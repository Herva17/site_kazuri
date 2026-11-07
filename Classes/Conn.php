<?php
declare(strict_types=1);

/**
 * Conn — Connexion PDO unique (Singleton).
 * C'est LE seul endroit où les identifiants MySQL sont définis.
 * Toute la classe métier (User, Models, pages…) passe par ici.
 */
final class Conn
{
    private static ?self $instance = null;
    private PDO $pdo;

    private function __construct()
    {
        // ⚠️ Uniquement ici on configure la BDD
        $host   = 'localhost';
        $dbname = 'kazur2501119';
        $user   = 'root';
        $pass   = '';

        $dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

        $this->pdo = new PDO($dsn, $user, $pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }

    public static function getInstance(): self
    {
        return self::$instance ??= new self();
    }

    /** Récupère le PDO brut. */
    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    // Anti-clonage et anti-désérialisation (pattern singleton strict)
    private function __clone() {}
    public function __wakeup(): void
    {
        throw new RuntimeException('Singleton : désérialisation interdite.');
    }
}