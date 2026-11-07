<?php
// Sécurité : on s'assure que la classe User est chargée
if (!class_exists('User')) {
    require_once dirname(__DIR__, 3) . '/Classes/UserClass.php';
}

// ✅ GESTION DU PLAN GRATUIT : Si on arrive via le bouton "Commencer gratuitement"
if (isset($_GET['plan']) && $_GET['plan'] === 'free') {
    $_SESSION['plan'] = 'free';
}

$currentUser = User::current();
if (!$currentUser) {
    header('Location: index.php?p=account');
    exit;
}

$firstName = htmlspecialchars($currentUser->firstName());
$userId = $currentUser->id();
$plan = $_SESSION['plan'] ?? 'free';
$isPremium = ($plan === 'premium');

// ==========================================
// RÉCUPÉRATION DES STATISTIQUES ET VIDÉOS
// ==========================================
$totalViews = 0;
$totalDuration = 0;
$recentVideos = [];
$subscribersCount = 0;

try {
    $pdo = Conn::getInstance()->getPdo();

    // 1. Récupération des statistiques globales (Vues et Durée)
    // On suppose que vous avez ajouté une colonne "views" à la table videos
    $stmtStats = $pdo->prepare("SELECT SUM(views) as total_views, SUM(duration_seconds) as total_duration, COUNT(id) as total_videos FROM videos WHERE user_id = ? AND status = 'published'");
    $stmtStats->execute([$userId]);
    $stats = $stmtStats->fetch(PDO::FETCH_ASSOC);
    
    $totalViews = (int)($stats['total_views'] ?? 0);
    $totalDurationSeconds = (int)($stats['total_duration'] ?? 0);
    $totalVideos = (int)($stats['total_videos'] ?? 0);

    // 2. Récupération des 3 dernières vidéos
    $stmtVideos = $pdo->prepare("SELECT * FROM videos WHERE user_id = ? ORDER BY created_at DESC LIMIT 3");
    $stmtVideos->execute([$userId]);
    $recentVideos = $stmtVideos->fetchAll(PDO::FETCH_ASSOC);

    // 3. Simulation du nombre d'abonnés (à relier à une vraie table "follows" plus tard)
    $subscribersCount = 245; 

} catch (Throwable $e) {
    // Valeurs par défaut en cas d'erreur
    $recentVideos = [];
}

// Fonctions utilitaires
function formatDuration(?int $seconds): string {
    if (!$seconds) return '0:00';
    $h = floor($seconds / 3600);
    $m = floor(($seconds % 3600) / 60);
    $s = $seconds % 60;
    return $h > 0 ? sprintf('%d:%02d:%02d', $h, $m, $s) : sprintf('%d:%02d', $m, $s);
}

function timeAgo(string $date): string {
    $timestamp = strtotime($date);
    $diff = time() - $timestamp;
    if ($diff < 60) return "À l'instant";
    if ($diff < 3600) return "Il y a " . floor($diff / 60) . " min";
    if ($diff < 86400) return "Il y a " . floor($diff / 3600) . " h";
    if ($diff < 604800) return "Il y a " . floor($diff / 86400) . " j";
    return date('d/m/Y', $timestamp);
}
?>

<!-- BANDEAU DE BIENVENUE -->
<div style="margin-bottom: 32px; display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 16px;">
    <div>
        <h1 style="font-size: 28px; margin: 0 0 8px; color: #fff;">Bonjour, <?= $firstName ?> 👋</h1>
        <p style="color: #aaa; margin: 0; font-size: 15px;">
            Voici ce qui se passe sur votre chaîne aujourd'hui.
        </p>
    </div>
    <div>
        <span style="background: <?= $isPremium ? 'var(--accent)' : '#333' ?>; color: #fff; padding: 6px 12px; border-radius: 6px; font-weight: bold; text-transform: uppercase; font-size: 12px; border: 1px solid <?= $isPremium ? 'var(--accent)' : '#444' ?>;">
            Plan <?= $plan ?>
        </span>
        <?php if (!$isPremium): ?>
            <a href="index.php?p=subscription" style="color: #3ea6ff; text-decoration: none; font-size: 13px; margin-left: 12px; font-weight: 500;">Passer Premium →</a>
        <?php endif; ?>
    </div>
</div>

<!-- CARTES STATISTIQUES (KPIs Dynamiques) -->
<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin-bottom: 32px;">
    
    <!-- Vues -->
    <div class="dashboard-card" style="margin: 0; padding: 20px;">
        <div style="color: #aaa; font-size: 13px; font-weight: 500; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px;">Vues Totales</div>
        <div style="font-size: 36px; font-weight: bold; color: #fff; line-height: 1;"><?= number_format($totalViews, 0, ',', ' ') ?></div>
        <div style="color: #4ade80; font-size: 13px; margin-top: 12px; display: flex; align-items: center; gap: 4px;">
            <i class="ph-bold ph-video-camera"></i> <?= $totalVideos ?> vidéos publiées
        </div>
    </div>
    
    <!-- Temps de visionnage -->
    <div class="dashboard-card" style="margin: 0; padding: 20px;">
        <div style="color: #aaa; font-size: 13px; font-weight: 500; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px;">Contenu disponible</div>
        <div style="font-size: 36px; font-weight: bold; color: #fff; line-height: 1;">
            <?= round($totalDurationSeconds / 3600, 1) ?> <span style="font-size: 16px; color: #aaa;">h</span>
        </div>
        <div style="color: #aaa; font-size: 13px; margin-top: 12px; display: flex; align-items: center; gap: 4px;">
            Temps de visionnage cumulé
        </div>
    </div>

    <!-- Abonnés -->
    <div class="dashboard-card" style="margin: 0; padding: 20px;">
        <div style="color: #aaa; font-size: 13px; font-weight: 500; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px;">Abonnés</div>
        <div style="font-size: 36px; font-weight: bold; color: #fff; line-height: 1;">+<?= number_format($subscribersCount, 0, ',', ' ') ?></div>
        <div style="color: #4ade80; font-size: 13px; margin-top: 12px; display: flex; align-items: center; gap: 4px;">
            <i class="ph-bold ph-trend-up"></i> Communauté grandissante
        </div>
    </div>

    <!-- Revenus -->
    <div class="dashboard-card" style="margin: 0; padding: 20px; <?= $isPremium ? 'border-color: rgba(74, 222, 128, 0.3);' : 'opacity: 0.5;' ?>">
        <div style="color: #aaa; font-size: 13px; font-weight: 500; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px;">Revenus estimés</div>
        <div style="font-size: 36px; font-weight: bold; color: #fff; line-height: 1;">
            <?= $isPremium ? '148,50 $' : '— $' ?>
        </div>
        <div style="color: <?= $isPremium ? '#4ade80' : '#ff4b5c' ?>; font-size: 13px; margin-top: 12px;">
            <?= $isPremium ? '▲ Monétisation active' : 'Monétisation désactivée' ?>
        </div>
    </div>
</div>

<!-- DERNIÈRES VIDÉOS (TABLEAU DYNAMIQUE) -->
<div class="dashboard-card">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h2 class="dashboard-title" style="margin: 0;">Contenus récents</h2>
        <a href="index.php?p=studio_contents" style="color: #3ea6ff; text-decoration: none; font-size: 14px; font-weight: 500;">Voir tout</a>
    </div>
    
    <div style="overflow-x: auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="min-width: 250px;">Vidéo</th>
                    <th>Visibilité</th>
                    <th>Date</th>
                    <th>Vues</th>
                    <th>Statut</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recentVideos)): ?>
                    <tr>
                        <td colspan="5" style="text-align: center; padding: 30px; color: #aaa;">
                            Vous n'avez pas encore publié de vidéo.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($recentVideos as $v): 
                        $thumb = !empty($v['thumbnail_url']) ? $v['thumbnail_url'] : 'https://picsum.photos/seed/' . urlencode($v['title']) . '/200/112';
                        
                        $badgeClass = 'badge-warning';
                        $statusLabel = 'En attente';
                        if ($v['status'] === 'published') {
                            $badgeClass = 'badge-success';
                            $statusLabel = 'Publié';
                        } elseif ($v['status'] === 'rejected') {
                            $badgeClass = 'badge-danger';
                            $statusLabel = 'Rejeté';
                        }
                    ?>
                    <tr>
                        <td>
                            <div style="display: flex; gap: 16px; align-items: center;">
                                <img src="<?= htmlspecialchars($thumb) ?>" class="video-thumb-sm" alt="Miniature">
                                <div>
                                    <div style="font-weight: 600; color: #fff; margin-bottom: 4px;"><?= htmlspecialchars($v['title']) ?></div>
                                    <div style="font-size: 12px; color: #aaa;"><?= formatDuration((int)$v['duration_seconds']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <?php if ($v['status'] === 'published'): ?>
                                <span style="color: #4ade80; font-size: 13px; display: flex; align-items: center; gap: 6px;"><i class="ph-fill ph-globe"></i> Public</span>
                            <?php else: ?>
                                <span style="color: #aaa; font-size: 13px; display: flex; align-items: center; gap: 6px;"><i class="ph ph-lock-key"></i> Privé</span>
                            <?php endif; ?>
                        </td>
                        <td style="color: #aaa;"><?= timeAgo($v['created_at']) ?></td>
                        <td><?= number_format((int)($v['views'] ?? 0), 0, ',', ' ') ?></td>
                        <td><span class="badge <?= $badgeClass ?>"><?= $statusLabel ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- BANNIÈRE D'ACTION -->
<div class="dashboard-card" style="background: linear-gradient(45deg, #1e1e1e, #2a2a2a); border-color: #444; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px;">
    <div>
        <h3 style="color: #fff; margin: 0 0 4px;">Prêt à publier une nouvelle vidéo ?</h3>
        <p style="color: #aaa; margin: 0; font-size: 14px;">Partagez votre talent avec votre audience dès maintenant.</p>
    </div>
    <a href="index.php?p=studio_upload" style="background: var(--accent); color: #fff; padding: 10px 24px; border-radius: 8px; text-decoration: none; font-weight: bold; display: inline-flex; align-items: center; gap: 8px;">
        <i class="ph ph-upload-simple"></i> Mettre en ligne
    </a>
</div>