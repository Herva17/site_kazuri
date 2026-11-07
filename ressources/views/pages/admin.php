<?php
// On s'assure que la classe AdminDashboard est chargée
if (!class_exists('AdminDashboard')) {
    require_once dirname(__DIR__, 3) . '/Classes/AdminDashboardClass.php';
}

// Récupération des données
$stats = AdminDashboard::getStats();
$activities = AdminDashboard::getRecentActivities(5);

// Fonction utilitaire pour formater le temps écoulé
function timeAgo(int $timestamp): string {
    $diff = time() - $timestamp;
    if ($diff < 60) return "À l'instant";
    if ($diff < 3600) return "Il y a " . floor($diff / 60) . " min";
    if ($diff < 86400) return "Il y a " . floor($diff / 3600) . " h";
    if ($diff < 604800) return "Il y a " . floor($diff / 86400) . " j";
    return date('d/m/Y', $timestamp);
}

// Formatage des nombres
function formatMoney(float $amount): string {
    return number_format($amount, 2, ',', ' ') . ' $';
}
?>

<!-- CARTES STATISTIQUES (KPIs) -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 32px;">
    
    <!-- Utilisateurs totaux -->
    <div class="card" style="margin: 0;">
        <div style="color: #aaa; font-size: 14px; margin-bottom: 8px;">Utilisateurs totaux</div>
        <div style="font-size: 32px; font-weight: bold; color: #fff;"><?= number_format($stats['total_users'], 0, ',', ' ') ?></div>
        <div style="color: #4ade80; font-size: 13px; margin-top: 8px;">▲ Base de données</div>
    </div>

    <!-- Vidéos en attente -->
    <div class="card" style="margin: 0; border-color: rgba(250, 204, 21, 0.4);">
        <div style="color: #aaa; font-size: 14px; margin-bottom: 8px;">Vidéos en attente</div>
        <div style="font-size: 32px; font-weight: bold; color: #facc15;"><?= number_format($stats['pending_videos'], 0, ',', ' ') ?></div>
        <div style="color: #facc15; font-size: 13px; margin-top: 8px;">
            <?= $stats['pending_videos'] > 0 ? 'À modérer rapidement' : 'Aucune vidéo à modérer' ?>
        </div>
    </div>

    <!-- Abonnés Premium -->
    <div class="card" style="margin: 0;">
        <div style="color: #aaa; font-size: 14px; margin-bottom: 8px;">Abonnés Premium</div>
        <div style="font-size: 32px; font-weight: bold; color: #fff;"><?= number_format($stats['premium_subscribers'], 0, ',', ' ') ?></div>
        <div style="color: #4ade80; font-size: 13px; margin-top: 8px;">▲ Abonnements actifs</div>
    </div>

    <!-- Revenus estimés -->
    <div class="card" style="margin: 0;">
        <div style="color: #aaa; font-size: 14px; margin-bottom: 8px;">Revenus (Ce mois)</div>
        <div style="font-size: 32px; font-weight: bold; color: #fff;"><?= formatMoney($stats['month_revenue']) ?></div>
        <div style="color: #aaa; font-size: 13px; margin-top: 8px;">Total encaissé ce mois-ci</div>
    </div>
</div>

<!-- TABLEAU DES ACTIVITÉS RÉCENTES -->
<div class="card">
    <h2 class="card-title">Dernières activités sur la plateforme</h2>
    <table class="data-table">
        <thead>
            <tr>
                <th>Action</th>
                <th>Utilisateur</th>
                <th>Détails</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($activities)): ?>
                <tr>
                    <td colspan="4" style="text-align: center; color: #aaa; padding: 20px;">
                        Aucune activité récente sur la plateforme.
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($activities as $act): ?>
                <tr>
                    <td><span class="badge <?= $act['badge'] ?>"><?= $act['type'] ?></span></td>
                    <td><?= htmlspecialchars($act['user']) ?></td>
                    <td><?= $act['details'] ?></td>
                    <td style="color:#aaa;"><?= timeAgo($act['timestamp']) ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>