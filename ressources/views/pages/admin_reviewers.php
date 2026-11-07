<?php
// ressources/views/pages/admin_reviewers.php
if (!class_exists('User')) {
    require_once dirname(__DIR__, 3) . '/Classes/UserClass.php';
}

$currentUser = User::current();
if (!$currentUser || !$currentUser->isAdmin()) {
    header('Location: index.php?p=home');
    exit;
}

// --- Récupération des reviewers ---
$reviewers = [];
if (isset($pdo)) {
    try {
        $st = $pdo->query("
            SELECT r.*, u.email AS user_email
            FROM reviewers r
            JOIN users u ON u.id = r.user_id
            ORDER BY r.role DESC, r.display_name ASC
        ");
        $reviewers = $st->fetchAll(PDO::FETCH_ASSOC);
    } catch (Throwable $e) {
        error_log('[admin_reviewers] ' . $e->getMessage());
    }
}

// --- Statistiques rapides ---
$stats = ['total' => 0, 'actifs' => 0, 'moderateurs' => 0, 'admins' => 0];
foreach ($reviewers as $r) {
    $stats['total']++;
    if ($r['status'] === 'active')   $stats['actifs']++;
    if ($r['role'] === 'moderator')  $stats['moderateurs']++;
    if (in_array($r['role'], ['admin', 'super_admin'], true)) $stats['admins']++;
}
?>

<!-- Cartes stats -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px; margin-bottom: 24px;">
    <div class="card" style="margin: 0;">
        <div style="color: #aaa; font-size: 12px; text-transform: uppercase;">Total reviewers</div>
        <div style="font-size: 28px; font-weight: bold; color: #fff;"><?= $stats['total'] ?></div>
    </div>
    <div class="card" style="margin: 0;">
        <div style="color: #aaa; font-size: 12px; text-transform: uppercase;">Actifs</div>
        <div style="font-size: 28px; font-weight: bold; color: #4ade80;"><?= $stats['actifs'] ?></div>
    </div>
    <div class="card" style="margin: 0;">
        <div style="color: #aaa; font-size: 12px; text-transform: uppercase;">Modérateurs</div>
        <div style="font-size: 28px; font-weight: bold; color: #60a5fa;"><?= $stats['moderateurs'] ?></div>
    </div>
    <div class="card" style="margin: 0;">
        <div style="color: #aaa; font-size: 12px; text-transform: uppercase;">Admins</div>
        <div style="font-size: 28px; font-weight: bold; color: #ff2d3a;"><?= $stats['admins'] ?></div>
    </div>
</div>

<!-- Table reviewers -->
<div class="card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
        <h3 class="card-title" style="margin: 0;">Équipe de modération</h3>
        <a href="index.php?p=admin_reviewer_edit" class="btn btn-primary">
            <i class="ph ph-plus"></i> Nouveau reviewer
        </a>
    </div>

    <?php if (empty($reviewers)): ?>
        <p style="color: #aaa; text-align: center; padding: 40px 0;">
            Aucun reviewer pour l'instant. Cliquez sur « Nouveau reviewer » pour commencer.
        </p>
    <?php else: ?>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Rôle</th>
                    <th>Spécialité</th>
                    <th>Quota/jour</th>
                    <th>Statut</th>
                    <th>Dernière activité</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reviewers as $r): ?>
                    <tr>
                        <td><strong><?= htmlspecialchars($r['display_name']) ?></strong></td>
                        <td style="color: #aaa;"><?= htmlspecialchars($r['email']) ?></td>
                        <td>
                            <?php
                            $roleBadge = match($r['role']) {
                                'super_admin' => 'badge-danger',
                                'admin'       => 'badge-warning',
                                'editor'      => 'badge-info',
                                default       => 'badge-success',
                            };
                            ?>
                            <span class="badge <?= $roleBadge ?>"><?= strtoupper($r['role']) ?></span>
                        </td>
                        <td style="color: #aaa;"><?= htmlspecialchars($r['specialty'] ?? '—') ?></td>
                        <td><?= (int)$r['max_daily'] ?></td>
                        <td>
                            <?php if ($r['status'] === 'active'): ?>
                                <span class="badge badge-success">Actif</span>
                            <?php elseif ($r['status'] === 'suspended'): ?>
                                <span class="badge badge-danger">Suspendu</span>
                            <?php else: ?>
                                <span class="badge badge-warning">Inactif</span>
                            <?php endif; ?>
                        </td>
                        <td style="color: #aaa; font-size: 13px;">
                            <?= $r['last_seen_at'] ? date('d/m/Y H:i', strtotime($r['last_seen_at'])) : 'Jamais' ?>
                        </td>
                        <td>
                            <a href="index.php?p=admin_reviewer_edit&id=<?= (int)$r['id'] ?>" class="btn btn-outline" style="padding: 6px 10px; font-size: 12px;">
                                <i class="ph ph-pencil"></i>
                            </a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>