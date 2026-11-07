<?php
declare(strict_types=1);

// On s'assure que Conn est chargée avant la classe User
require_once __DIR__ . '/Conn.php';

/**
 * Classe User — gère users, roles et user_roles.
 * ⚠️ Ne gère plus la connexion : elle est déléguée à Conn.
 */
final class User
{
    private const ROLE_PRIORITY = [
        'superadmin', 'admin', 'contributor', 'pending', 'subscriber', 'user', 'visitor'
    ];

    private static ?self $current = null;
    private static bool $currentLoaded = false;

    // ---------- Propriétés d'instance ----------
    private int     $id;
    private string  $email;
    private string  $firstName;
    private string  $lastName;
    private ?string $avatarUrl;
    private bool    $isActive;
    private string  $createdAt;
    private array   $roles = [];

    private function __construct(array $row)
    {
        $this->id         = (int)$row['id'];
        $this->email      = $row['email'];
        $this->firstName  = $row['first_name'];
        $this->lastName   = $row['last_name'];
        $this->avatarUrl  = $row['avatar_url'] ?? null;
        $this->isActive   = (bool)$row['is_active'];
        $this->createdAt  = $row['created_at'];
    }

    // =====================================================================
    // AUTHENTIFICATION
    // =====================================================================

    public static function register(
        string $email,
        string $password,
        string $firstName,
        string $lastName,
        ?string $avatarUrl = null,
        string $initialRole = 'user'
    ): self {
        $email = strtolower(trim($email));

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Email invalide.");
        }
        if (strlen($password) < 6) {
            throw new InvalidArgumentException("Mot de passe : 6 caractères minimum.");
        }
        if (trim($firstName) === '') {
            throw new InvalidArgumentException("Prénom requis.");
        }

        $pdo = Conn::getInstance()->getPdo();
        $pdo->beginTransaction();

        try {
            $st = $pdo->prepare("SELECT id FROM users WHERE email = ?");
            $st->execute([$email]);
            if ($st->fetch()) {
                throw new RuntimeException("Cet email est déjà utilisé.");
            }

            $st = $pdo->prepare(
                "INSERT INTO users (email, password_hash, first_name, last_name, avatar_url, is_active)
                 VALUES (?, ?, ?, ?, ?, 1)"
            );
            $st->execute([
                $email,
                password_hash($password, PASSWORD_DEFAULT),
                trim($firstName),
                trim($lastName),
                $avatarUrl ?: 'assets/img/av_user.jpg',
            ]);
            $userId = (int)$pdo->lastInsertId();

            self::attachRole($userId, $initialRole, $pdo);

            $st = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $st->execute([$userId]);
            $row = $st->fetch();

            $pdo->commit();
            return new self($row);
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    public static function attempt(string $email, string $password): ?self
    {
        $st = Conn::getInstance()->getPdo()->prepare(
            "SELECT * FROM users WHERE email = ? AND is_active = 1 LIMIT 1"
        );
        $st->execute([strtolower(trim($email))]);
        $row = $st->fetch();

        if (!$row || !password_verify($password, $row['password_hash'])) {
            return null;
        }

        $user = new self($row);
        $user->loadRoles();
        return $user;
    }

    public static function current(): ?self
    {
        if (self::$currentLoaded) {
            return self::$current;
        }
        self::$currentLoaded = true;

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (empty($_SESSION['user_id'])) {
            return self::$current = null;
        }

        $st = Conn::getInstance()->getPdo()->prepare(
            "SELECT * FROM users WHERE id = ? AND is_active = 1 LIMIT 1"
        );
        $st->execute([(int)$_SESSION['user_id']]);
        $row = $st->fetch();

        if (!$row) {
            unset($_SESSION['user_id']);
            return self::$current = null;
        }

        $user = new self($row);
        $user->loadRoles();
        return self::$current = $user;
    }

    public function login(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        session_regenerate_id(true);
        $_SESSION['user_id'] = $this->id;
        $_SESSION['user_email'] = $this->email;

        self::$current = $this;
        self::$currentLoaded = true;
    }

    public static function logout(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $p = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
        }
        session_destroy();

        self::$current = null;
        self::$currentLoaded = true;
    }

    public static function isLoggedIn(): bool
    {
        return self::current() !== null;
    }

    // =====================================================================
    // RÔLES
    // =====================================================================

    private function loadRoles(): void
    {
        $st = Conn::getInstance()->getPdo()->prepare(
            "SELECT r.name
             FROM roles r
             JOIN user_roles ur ON ur.role_id = r.id
             WHERE ur.user_id = ?
             ORDER BY r.name"
        );
        $st->execute([$this->id]);
        $this->roles = array_column($st->fetchAll(), 'name');
    }

    public function roles(): array { return $this->roles; }

    public function primaryRole(): string
    {
        foreach (self::ROLE_PRIORITY as $role) {
            if (in_array($role, $this->roles, true)) return $role;
        }
        return 'visitor';
    }

    public function hasRole(string $role): bool { return in_array($role, $this->roles, true); }
    public function hasAnyRole(string ...$roles): bool { return !empty(array_intersect($this->roles, $roles)); }
    public function isAdmin(): bool { return $this->hasAnyRole('admin', 'superadmin'); }
    public function isSuperAdmin(): bool { return $this->hasRole('superadmin'); }
    public function canPublish(): bool { return $this->hasAnyRole('contributor', 'admin', 'superadmin'); }

    public function assignRole(string $role): void
    {
        self::attachRole($this->id, $role, Conn::getInstance()->getPdo());
        $this->loadRoles();
    }

    public function removeRole(string $role): void
    {
        $st = Conn::getInstance()->getPdo()->prepare(
            "DELETE ur FROM user_roles ur
             JOIN roles r ON r.id = ur.role_id
             WHERE ur.user_id = ? AND r.name = ?"
        );
        $st->execute([$this->id, $role]);
        $this->loadRoles();
    }

    private static function attachRole(int $userId, string $roleName, PDO $pdo): void
    {
        $st = $pdo->prepare("SELECT id FROM roles WHERE name = ?");
        $st->execute([$roleName]);
        $roleId = $st->fetchColumn();

        if (!$roleId) {
            $pdo->prepare("INSERT INTO roles (name) VALUES (?)")->execute([$roleName]);
            $roleId = (int)$pdo->lastInsertId();
        }

        $pdo->prepare("INSERT IGNORE INTO user_roles (user_id, role_id) VALUES (?, ?)")->execute([$userId, $roleId]);
    }

    // =====================================================================
    // GESTION DES UTILISATEURS (POUR L'ADMINISTRATION)
    // =====================================================================

    /**
     * Récupère tous les utilisateurs avec leurs rôles.
     */
    public static function getAllUsers(): array
    {
        $pdo = Conn::getInstance()->getPdo();
        $sql = "SELECT u.id, u.first_name, u.last_name, u.email, u.is_active, u.created_at, 
                       GROUP_CONCAT(r.name) as roles
                FROM users u
                LEFT JOIN user_roles ur ON u.id = ur.user_id
                LEFT JOIN roles r ON ur.role_id = r.id
                GROUP BY u.id
                ORDER BY u.created_at DESC";
        return $pdo->query($sql)->fetchAll();
    }

    /**
     * Supprime un utilisateur par son ID.
     */
    public static function deleteUser(int $userId): bool
    {
        $pdo = Conn::getInstance()->getPdo();
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$userId]);
    }

    /**
     * Modifie le rôle d'un utilisateur.
     */
    public static function updateUserRole(int $userId, string $newRole): bool
    {
        $pdo = Conn::getInstance()->getPdo();
        
        // 1. On supprime les anciens rôles de gestion pour cet utilisateur
        $stmt = $pdo->prepare("DELETE ur FROM user_roles ur JOIN roles r ON ur.role_id = r.id WHERE ur.user_id = ? AND r.name IN ('admin', 'superadmin', 'contributor', 'user')");
        $stmt->execute([$userId]);
        
        // 2. On ajoute le nouveau rôle
        self::attachRole($userId, $newRole, $pdo);
        return true;
    }

    // =====================================================================
    // GETTERS (inchangés)
    // =====================================================================

    public function id(): int             { return $this->id; }
    public function email(): string       { return $this->email; }
    public function firstName(): string   { return $this->firstName; }
    public function lastName(): string    { return $this->lastName; }
    public function fullName(): string    { return trim($this->firstName . ' ' . $this->lastName); }
    public function avatarUrl(): string   { return $this->avatarUrl ?: 'assets/img/av_user.jpg'; }
    public function isActive(): bool      { return $this->isActive; }
    public function createdAt(): string   { return $this->createdAt; }

    public function handle(): string
    {
        $base = strtolower($this->firstName . $this->lastName);
        $base = preg_replace('/[^a-z0-9]+/', '', $base);
        return '@' . ($base ?: 'user' . $this->id);
    }

    public function toArray(): array
    {
        return [
            'id'         => $this->id,
            'name'       => $this->fullName(),
            'email'      => $this->email,
            'handle'     => $this->handle(),
            'avatar'     => $this->avatarUrl(),
            'role'       => $this->primaryRole(),
            'roles'      => $this->roles,
            'joined'     => substr($this->createdAt, 0, 10),
            'isAdmin'    => $this->isAdmin(),
            'isSuper'    => $this->isSuperAdmin(),
            'canPublish' => $this->canPublish(),
        ];
    }
}