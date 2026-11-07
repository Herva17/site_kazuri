<?php
// ressources/views/pages/admin_reviewer_edit.php
if (!class_exists('User')) {
    require_once dirname(__DIR__, 3) . '/Classes/UserClass.php';
}

$currentUser = User::current();
if (!$currentUser || !$currentUser->isAdmin()) {
    header('Location: index.php?p=home');
    exit;
}

$id       = (int)($_GET['id'] ?? 0);
$isEdit   = $id > 0;
$reviewer = [
    'user_id'      => 0,
    'display_name' => '',
    'email'        => '',
    'role'         => 'moderator',
    'status'       => 'active',
    'specialty'    => '',
    'max_daily'    => 50,
];
$error = $success = '';

// --- Traitement du formulaire ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($pdo)) {
    try {
        $data = [
            ':display_name' => trim($_POST['display_name'] ?? ''),
            ':email'        => trim($_POST['email'] ?? ''),
            ':role'         => $_POST['role'] ?? 'moderator',
            ':status'       => $_POST['status'] ?? 'active',
            ':specialty'    => trim($_POST['specialty'] ?? '') ?: null,
            ':max_daily'    => (int)($_POST['max_daily'] ?? 50),
        ];

        if ($data[':display_name'] === '' || $data[':email'] === '') {
            throw new Exception('Nom et email sont obligatoires.');
        }

        if ($isEdit) {
            $data[':id'] = $id;
            $st = $pdo->prepare("
                UPDATE reviewers SET
                    display_name = :display_name,
                    email        = :email,
                    role         = :role,
                    status       = :status,
                    specialty    = :specialty,
                    max_daily    = :max_daily
                WHERE id = :id
            ");
            $st->execute($data);
            $success = 'Reviewer mis à jour avec succès.';
        } else {
            // Création : le user_id doit être fourni (promotion d'un user existant)
            $data[':user_id'] = (int)($_POST['user_id'] ?? 0);
            if ($data[':user_id'] <= 0) throw new Exception('Sélectionnez un utilisateur.');

            $st = $pdo->prepare("
                INSERT INTO reviewers
                    (user_id, display_name, email, role, status, specialty, max_daily)
                VALUES
                    (:user_id, :display_name, :email, :role, :status, :specialty, :max_daily)
            ");
            $st->execute($data);
            $success = 'Reviewer créé avec succès.';
            $id = (int)$pdo->lastInsertId();
            $isEdit = true;
        }
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

// --- Chargement en mode édition ---
if ($isEdit && isset($pdo)) {
    $st = $pdo->prepare("SELECT * FROM reviewers WHERE id = ?");
    $st->execute([$id]);
    $row = $st->fetch(PDO::FETCH_ASSOC);
    if ($row) $reviewer = $row;
}
?>

<div class="card" style="max-width: 720px; margin: 0 auto;">
    <h3 class="card-title"><?= $isEdit ? 'Modifier le reviewer' : 'Nouveau reviewer' ?></h3>

    <?php if ($error): ?>
        <div class="badge badge-danger" style="display: block; padding: 12px; margin-bottom: 16px;">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="badge badge-success" style="display: block; padding: 12px; margin-bottom: 16px;">
            <?= htmlspecialchars($success) ?>
        </div>
    <?php endif; ?>

    <form method="POST" style="display: grid; gap: 16px;">
        <?php if (!$isEdit): ?>
            <div>
                <label style="display: block; font-size: 13px; color: #aaa; margin-bottom: 6px;">
                    Utilisateur à promouvoir (user_id)
                </label>
                <input type="number" name="user_id" required
                       style="width: 100%; padding: 12px; background: #111; border: 1px solid #333; border-radius: 8px; color: #fff;">
            </div>
        <?php endif; ?>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div>
                <label style="display: block; font-size: 13px; color: #aaa; margin-bottom: 6px;">Nom affiché</label>
                <input type="text" name="display_name" required
                       value="<?= htmlspecialchars($reviewer['display_name']) ?>"
                       style="width: 100%; padding: 12px; background: #111; border: 1px solid #333; border-radius: 8px; color: #fff;">
            </div>
            <div>
                <label style="display: block; font-size: 13px; color: #aaa; margin-bottom: 6px;">Email</label>
                <input type="email" name="email" required
                       value="<?= htmlspecialchars($reviewer['email']) ?>"
                       style="width: 100%; padding: 12px; background: #111; border: 1px solid #333; border-radius: 8px; color: #fff;">
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div>
                <label style="display: block; font-size: 13px; color: #aaa; margin-bottom: 6px;">Rôle</label>
                <select name="role" style="width: 100%; padding: 12px; background: #111; border: 1px solid #333; border-radius: 8px; color: #fff;">
                    <?php foreach (['moderator', 'editor', 'admin', 'super_admin'] as $r): ?>
                        <option value="<?= $r ?>" <?= $reviewer['role'] === $r ? 'selected' : '' ?>>
                            <?= ucfirst($r) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label style="display: block; font-size: 13px; color: #aaa; margin-bottom: 6px;">Statut</label>
                <select name="status" style="width: 100%; padding: 12px; background: #111; border: 1px solid #333; border-radius: 8px; color: #fff;">
                    <?php foreach (['active', 'inactive', 'suspended'] as $s): ?>
                        <option value="<?= $s ?>" <?= $reviewer['status'] === $s ? 'selected' : '' ?>>
                            <?= ucfirst($s) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px;">
            <div>
                <label style="display: block; font-size: 13px; color: #aaa; margin-bottom: 6px;">Spécialité</label>
                <input type="text" name="specialty"
                       value="<?= htmlspecialchars($reviewer['specialty'] ?? '') ?>"
                       placeholder="documentaire, musique…"
                       style="width: 100%; padding: 12px; background: #111; border: 1px solid #333; border-radius: 8px; color: #fff;">
            </div>
            <div>
                <label style="display: block; font-size: 13px; color: #aaa; margin-bottom: 6px;">Quota / jour</label>
                <input type="number" name="max_daily" min="0" max="999"
                       value="<?= (int)$reviewer['max_daily'] ?>"
                       style="width: 100%; padding: 12px; background: #111; border: 1px solid #333; border-radius: 8px; color: #fff;">
            </div>
        </div>

        <div style="display: flex; gap: 12px; margin-top: 8px;">
            <button type="submit" class="btn btn-primary">
                <i class="ph ph-check"></i> <?= $isEdit ? 'Enregistrer' : 'Créer' ?>
            </button>
            <a href="index.php?p=admin_reviewers" class="btn btn-outline">Annuler</a>
        </div>
    </form>
</div>