<?php
if (!class_exists('User')) {
    require_once dirname(__DIR__, 3) . '/Classes/UserClass.php';
}

$currentUser = User::current();
if (!$currentUser) {
    header('Location: index.php?p=account');
    exit;
}

/* --- ID utilisateur --- */
$userId = 0;
if (is_object($currentUser)) {
    if (method_exists($currentUser, 'getId')) $userId = (int)$currentUser->getId();
    elseif (isset($currentUser->id))          $userId = (int)$currentUser->id;
} elseif (is_array($currentUser)) {
    $userId = (int)($currentUser['id'] ?? 0);
}
if ($userId === 0) $userId = (int)($_SESSION['user_id'] ?? 0);

/* --- Périodes --- */
$now             = new DateTimeImmutable('now');
$periodStart     = $now->modify('-28 days')->format('Y-m-d H:i:s');
$prevPeriodStart = $now->modify('-56 days')->format('Y-m-d H:i:s');

/* --- Valeurs par défaut --- */
$totalViews      = 0;
$watchHours      = 0.0;
$subsCur         = 0;
$subsPrev        = 0;
$likesCur        = 0;
$likesPrev       = 0;

if (isset($pdo) && $userId > 0) {
    try {
        /* 1) Vues totales depuis videos.views */
        $st = $pdo->prepare("
            SELECT COALESCE(SUM(views), 0)
            FROM videos
            WHERE user_id = ? AND status = 'published'
        ");
        $st->execute([$userId]);
        $totalViews = (int)$st->fetchColumn();

        /* 2) Temps de visionnage estimé (vues × durée) */
        $st = $pdo->prepare("
            SELECT COALESCE(SUM(views * COALESCE(duration_seconds, 0)), 0)
            FROM videos
            WHERE user_id = ? AND status = 'published'
        ");
        $st->execute([$userId]);
        $watchHours = round((float)$st->fetchColumn() / 3600);

        /* 3) Abonnés (28 derniers jours) */
        $st = $pdo->prepare("
            SELECT COUNT(*) FROM subscriptions
            WHERE channel_id = ? AND created_at >= ?
        ");
        $st->execute([$userId, $periodStart]);
        $subsCur = (int)$st->fetchColumn();

        $st = $pdo->prepare("
            SELECT COUNT(*) FROM subscriptions
            WHERE channel_id = ? AND created_at >= ? AND created_at < ?
        ");
        $st->execute([$userId, $prevPeriodStart, $periodStart]);
        $subsPrev = (int)$st->fetchColumn();

        /* 4) Likes (28 derniers jours) */
        $st = $pdo->prepare("
            SELECT COUNT(*) FROM video_likes vl
            JOIN videos v ON v.id = vl.video_id
            WHERE v.user_id = ? AND vl.created_at >= ?
        ");
        $st->execute([$userId, $periodStart]);
        $likesCur = (int)$st->fetchColumn();

        $st = $pdo->prepare("
            SELECT COUNT(*) FROM video_likes vl
            JOIN videos v ON v.id = vl.video_id
            WHERE v.user_id = ? AND vl.created_at >= ? AND vl.created_at < ?
        ");
        $st->execute([$userId, $prevPeriodStart, $periodStart]);
        $likesPrev = (int)$st->fetchColumn();

    } catch (Throwable $e) {
        error_log('[studio_stats] ' . $e->getMessage());
    }
}

/* --- Formatage --- */
if (!function_exists('kpi_fmt')) {
    function kpi_fmt(float|int $n): string { return number_format($n, 0, ',', ' '); }
}
if (!function_exists('kpi_delta')) {
    function kpi_delta(float $cur, float $prev): array {
        if ($prev <= 0 && $cur <= 0) return ['up' => true, 'html' => '—'];
        $pct = $prev > 0 ? (($cur - $prev) / $prev) * 100 : 100.0;
        $up  = $pct >= 0;
        return ['up' => $up, 'html' => ($up ? '▲ +' : '▼ ') . number_format(abs($pct), 0, ',', ' ') . '%'];
    }
}

$subsDelta  = kpi_delta($subsCur,  $subsPrev);
$likesDelta = kpi_delta($likesCur, $likesPrev);

/* --- Graphique : abonnés + likes gagnés par mois (12 mois) --- */
$chartSubs  = [];
$chartLikes = [];
$monthLabels = [];
for ($i = 11; $i >= 0; $i--) {
    $d = $now->modify("first day of -{$i} month");
    $key = $d->format('Y-m');
    $chartSubs[$key]  = 0;
    $chartLikes[$key] = 0;
    $monthLabels[$key] = $d->format('m/Y');
}

if (isset($pdo) && $userId > 0) {
    try {
        /* Abonnés par mois */
        $st = $pdo->prepare("
            SELECT DATE_FORMAT(created_at, '%Y-%m') AS ym, COUNT(*) AS c
            FROM subscriptions
            WHERE channel_id = ? AND created_at >= DATE_FORMAT(NOW() - INTERVAL 11 MONTH, '%Y-%m-01')
            GROUP BY ym
        ");
        $st->execute([$userId]);
        foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $r) {
            if (isset($chartSubs[$r['ym']])) $chartSubs[$r['ym']] = (int)$r['c'];
        }

        /* Likes par mois */
        $st = $pdo->prepare("
            SELECT DATE_FORMAT(vl.created_at, '%Y-%m') AS ym, COUNT(*) AS c
            FROM video_likes vl
            JOIN videos v ON v.id = vl.video_id
            WHERE v.user_id = ? AND vl.created_at >= DATE_FORMAT(NOW() - INTERVAL 11 MONTH, '%Y-%m-01')
            GROUP BY ym
        ");
        $st->execute([$userId]);
        foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $r) {
            if (isset($chartLikes[$r['ym']])) $chartLikes[$r['ym']] = (int)$r['c'];
        }
    } catch (Throwable $e) {
        error_log('[studio_stats][chart] ' . $e->getMessage());
    }
}

$maxChart = max(array_merge([1], array_values($chartSubs), array_values($chartLikes)));
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<title>Statistiques — Kazuri</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>

<section class="content-section account-section" style="padding: 40px 0;">
    <div class="container">

        <!-- Cartes KPI -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 24px;">
            <div class="dashboard-card" style="margin: 0;">
                <div style="color: #aaa; font-size: 13px; text-transform: uppercase;">Vues totales</div>
                <div style="font-size: 32px; font-weight: bold; color: #fff; margin: 8px 0;">
                    <?= kpi_fmt($totalViews) ?>
                </div>
                <div style="color: #aaa; font-size: 13px;">Toutes vos vidéos publiées</div>
            </div>
            <div class="dashboard-card" style="margin: 0;">
                <div style="color: #aaa; font-size: 13px; text-transform: uppercase;">Visionnage estimé</div>
                <div style="font-size: 32px; font-weight: bold; color: #fff; margin: 8px 0;">
                    <?= kpi_fmt($watchHours) ?> h
                </div>
                <div style="color: #aaa; font-size: 13px;">Vues × durée moyenne</div>
            </div>
            <div class="dashboard-card" style="margin: 0;">
                <div style="color: #aaa; font-size: 13px; text-transform: uppercase;">Nouveaux abonnés (28 j)</div>
                <div style="font-size: 32px; font-weight: bold; color: #fff; margin: 8px 0;">
                    <?= ($subsCur > 0 ? '+' : '') . kpi_fmt($subsCur) ?>
                </div>
                <div style="color: <?= $subsDelta['up'] ? '#4ade80' : '#ff4b5c' ?>; font-size: 13px;">
                    <?= $subsDelta['html'] ?> vs période précédente
                </div>
            </div>
            <div class="dashboard-card" style="margin: 0;">
                <div style="color: #aaa; font-size: 13px; text-transform: uppercase;">Nouveaux likes (28 j)</div>
                <div style="font-size: 32px; font-weight: bold; color: #fff; margin: 8px 0;">
                    <?= ($likesCur > 0 ? '+' : '') . kpi_fmt($likesCur) ?>
                </div>
                <div style="color: <?= $likesDelta['up'] ? '#4ade80' : '#ff4b5c' ?>; font-size: 13px;">
                    <?= $likesDelta['html'] ?> vs période précédente
                </div>
            </div>
        </div>

        <!-- Graphique -->
        <div class="dashboard-card">
            <h3 class="dashboard-title">Croissance de la chaîne</h3>
            <p style="color: #aaa; font-size: 13px; margin: 4px 0 16px;">Abonnés et likes gagnés par mois (12 derniers mois)</p>

            <div style="display: flex; align-items: flex-end; gap: 12px; height: 200px; padding-bottom: 20px; border-bottom: 1px solid #333;">
                <?php foreach ($monthLabels as $ym => $lbl):
                    $hSub = (int)round(($chartSubs[$ym]  / $maxChart) * 100);
                    $hLik = (int)round(($chartLikes[$ym] / $maxChart) * 100);
                ?>
                    <div style="flex: 1; display: flex; flex-direction: column; align-items: center; gap: 2px;">
                        <div title="<?= $lbl ?> : <?= kpi_fmt($chartLikes[$ym]) ?> likes"
                             style="width: 45%; background: #3ea6ff; border-radius: 2px 2px 0 0; height: <?= max(3, $hLik) ?>%; min-height: 3px;"></div>
                        <div title="<?= $lbl ?> : <?= kpi_fmt($chartSubs[$ym]) ?> abonnés"
                             style="width: 45%; background: var(--accent, #ff2d3a); border-radius: 2px 2px 0 0; height: <?= max(3, $hSub) ?>%; min-height: 3px;"></div>
                    </div>
                <?php endforeach; ?>
            </div>
            <div style="display: flex; justify-content: space-between; color: #aaa; font-size: 11px; margin-top: 10px;">
                <?php foreach ($monthLabels as $lbl): ?>
                    <span style="flex: 1; text-align: center;"><?= $lbl ?></span>
                <?php endforeach; ?>
            </div>
            <div style="display: flex; gap: 20px; margin-top: 16px; font-size: 13px;">
                <div style="display: flex; align-items: center; gap: 6px;">
                    <span style="width: 12px; height: 12px; background: var(--accent, #ff2d3a); border-radius: 2px;"></span>
                    <span style="color: #aaa;">Abonnés</span>
                </div>
                <div style="display: flex; align-items: center; gap: 6px;">
                    <span style="width: 12px; height: 12px; background: #3ea6ff; border-radius: 2px;"></span>
                    <span style="color: #aaa;">Likes</span>
                </div>
            </div>
        </div>

    </div>
</section>

</body>
</html>