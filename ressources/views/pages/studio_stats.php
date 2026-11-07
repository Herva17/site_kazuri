<?php
declare(strict_types=1);
require_once dirname(__DIR__, 3) . '/Classes/UserClass.php';

// Utilisateur courant
$current = User::current();

if (!function_exists('t')) {
    function t(string $s): string { return $s; }
}

/* =====================================================================
   DONNÉES RÉELLES — KPI + Graphique
   ===================================================================== */

// --- ID utilisateur (robuste : objet User OU tableau OU session) ---
$userId = 0;
if (is_object($current)) {
    if (method_exists($current, 'getId'))  $userId = (int)$current->getId();
    elseif (isset($current->id))           $userId = (int)$current->id;
} elseif (is_array($current)) {
    $userId = (int)($current['id'] ?? 0);
}
if ($userId === 0) $userId = (int)($_SESSION['user_id'] ?? 0);

// --- Période analysée (28 jours + 28 jours précédents) ---
$days        = 28;
$now         = new DateTimeImmutable('now');
$periodStart = $now->modify("-{$days} days")->format('Y-m-d H:i:s');
$prevStart   = $now->modify('-' . ($days * 2) . ' days')->format('Y-m-d H:i:s');

// --- Valeurs par défaut ---
$kpiViews = ['cur' => 0,  'prev' => 0];
$kpiHours = ['cur' => 0.0, 'prev' => 0.0];
$kpiSubs  = ['cur' => 0,  'prev' => 0];

// --- Requêtes SQL ---
if (isset($pdo) && $userId > 0) {
    try {
        // Vues — 28 derniers jours
        $st = $pdo->prepare("
            SELECT COUNT(*) FROM video_views vv
            JOIN videos v ON v.id = vv.video_id
            WHERE v.user_id = ? AND vv.created_at >= ?
        ");
        $st->execute([$userId, $periodStart]);
        $kpiViews['cur'] = (int)$st->fetchColumn();

        // Vues — 28 jours précédents
        $st = $pdo->prepare("
            SELECT COUNT(*) FROM video_views vv
            JOIN videos v ON v.id = vv.video_id
            WHERE v.user_id = ? AND vv.created_at >= ? AND vv.created_at < ?
        ");
        $st->execute([$userId, $prevStart, $periodStart]);
        $kpiViews['prev'] = (int)$st->fetchColumn();

        // Temps de visionnage (secondes → heures)
        $st = $pdo->prepare("
            SELECT COALESCE(SUM(vv.watch_seconds),0) FROM video_views vv
            JOIN videos v ON v.id = vv.video_id
            WHERE v.user_id = ? AND vv.created_at >= ?
        ");
        $st->execute([$userId, $periodStart]);
        $kpiHours['cur'] = (float)$st->fetchColumn() / 3600;

        $st = $pdo->prepare("
            SELECT COALESCE(SUM(vv.watch_seconds),0) FROM video_views vv
            JOIN videos v ON v.id = vv.video_id
            WHERE v.user_id = ? AND vv.created_at >= ? AND vv.created_at < ?
        ");
        $st->execute([$userId, $prevStart, $periodStart]);
        $kpiHours['prev'] = (float)$st->fetchColumn() / 3600;

        // Abonnés gagnés
        $st = $pdo->prepare("
            SELECT COUNT(*) FROM subscriptions
            WHERE channel_id = ? AND created_at >= ?
        ");
        $st->execute([$userId, $periodStart]);
        $kpiSubs['cur'] = (int)$st->fetchColumn();

        $st = $pdo->prepare("
            SELECT COUNT(*) FROM subscriptions
            WHERE channel_id = ? AND created_at >= ? AND created_at < ?
        ");
        $st->execute([$userId, $prevStart, $periodStart]);
        $kpiSubs['prev'] = (int)$st->fetchColumn();
    } catch (Throwable $e) {
        error_log('[studio_stats] ' . $e->getMessage());
    }
}

// --- Helpers d'affichage ---
if (!function_exists('kpi_fmt')) {
    function kpi_fmt(float|int $n): string {
        return number_format($n, 0, ',', ' ');
    }
}
if (!function_exists('kpi_delta')) {
    function kpi_delta(float $cur, float $prev): array {
        if ($prev <= 0 && $cur <= 0) return ['up' => true, 'html' => '—'];
        $pct = $prev > 0 ? (($cur - $prev) / $prev) * 100 : 100.0;
        $up  = $pct >= 0;
        return [
            'up'   => $up,
            'html' => ($up ? '▲ +' : '▼ ') . number_format(abs($pct), 0, ',', ' ') . '%',
        ];
    }
}

$viewsDelta = kpi_delta($kpiViews['cur'], $kpiViews['prev']);
$hoursDelta = kpi_delta($kpiHours['cur'], $kpiHours['prev']);
$subsDelta  = kpi_delta($kpiSubs['cur'],  $kpiSubs['prev']);

// --- Graphique : vues par mois sur 12 mois glissants ---
$monthlyViews = [];
for ($i = 11; $i >= 0; $i--) {
    $d = $now->modify("first day of -{$i} month");
    $monthlyViews[$d->format('Y-m')] = 0;
}
if (isset($pdo) && $userId > 0) {
    try {
        $st = $pdo->prepare("
            SELECT DATE_FORMAT(vv.created_at, '%Y-%m') AS ym, COUNT(*) AS c
            FROM video_views vv
            JOIN videos v ON v.id = vv.video_id
            WHERE v.user_id = ?
              AND vv.created_at >= DATE_FORMAT(NOW() - INTERVAL 11 MONTH, '%Y-%m-01')
            GROUP BY ym
        ");
        $st->execute([$userId]);
        foreach ($st->fetchAll(PDO::FETCH_ASSOC) as $r) {
            if (isset($monthlyViews[$r['ym']])) {
                $monthlyViews[$r['ym']] = (int)$r['c'];
            }
        }
    } catch (Throwable $e) {
        error_log('[studio_stats][chart] ' . $e->getMessage());
    }
}
$maxMonth = max($monthlyViews) ?: 1;
?>
<!doctype html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Studio — Statistiques · Kazuri</title>
<link rel="stylesheet" href="assets/style.css">
</head>
<body>

<section class="content-section">
    <div class="container">

        <!-- Cartes KPI (dynamiques) -->
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 20px; margin-bottom: 24px;">
            <div class="dashboard-card" style="margin: 0;">
                <div style="color: #aaa; font-size: 13px; text-transform: uppercase;">Vues (<?= $days ?> jours)</div>
                <div style="font-size: 32px; font-weight: bold; color: #fff; margin: 8px 0;">
                    <?= kpi_fmt($kpiViews['cur']) ?>
                </div>
                <div style="color: <?= $viewsDelta['up'] ? '#4ade80' : '#ff4b5c' ?>; font-size: 13px;">
                    <?= $viewsDelta['html'] ?> vs période précédente
                </div>
            </div>
            <div class="dashboard-card" style="margin: 0;">
                <div style="color: #aaa; font-size: 13px; text-transform: uppercase;">Temps de visionnage</div>
                <div style="font-size: 32px; font-weight: bold; color: #fff; margin: 8px 0;">
                    <?= kpi_fmt(round($kpiHours['cur'])) ?> h
                </div>
                <div style="color: <?= $hoursDelta['up'] ? '#4ade80' : '#ff4b5c' ?>; font-size: 13px;">
                    <?= $hoursDelta['html'] ?> vs période précédente
                </div>
            </div>
            <div class="dashboard-card" style="margin: 0;">
                <div style="color: #aaa; font-size: 13px; text-transform: uppercase;">Abonnés</div>
                <div style="font-size: 32px; font-weight: bold; color: #fff; margin: 8px 0;">
                    <?= ($kpiSubs['cur'] > 0 ? '+' : '') . kpi_fmt($kpiSubs['cur']) ?>
                </div>
                <div style="color: <?= $subsDelta['up'] ? '#4ade80' : '#ff4b5c' ?>; font-size: 13px;">
                    <?= $subsDelta['html'] ?> vs période précédente
                </div>
            </div>
        </div>

        <!-- Graphique (dynamique) -->
        <div class="dashboard-card">
            <h3 class="dashboard-title">Performance de la chaîne</h3>
            <div style="display: flex; align-items: flex-end; gap: 10px; height: 200px; margin-top: 20px; padding-bottom: 20px; border-bottom: 1px solid #333;">
                <?php foreach ($monthlyViews as $ym => $val):
                    $h = $maxMonth > 0 ? (int)round(($val / $maxMonth) * 100) : 0;
                    if ($h < 3) $h = 3;
                    $op = max(0.15, round($val / $maxMonth, 2));
                ?>
                    <div
                        title="<?= htmlspecialchars($ym) ?> : <?= kpi_fmt($val) ?> vues"
                        style="flex: 1; background: var(--accent, #ff2d3a); border-radius: 4px 4px 0 0; height: <?= $h ?>%; opacity: <?= $op ?>;">
                    </div>
                <?php endforeach; ?>
            </div>
            <div style="display: flex; justify-content: space-between; color: #aaa; font-size: 12px; margin-top: 10px;">
                <?php
                $frMonths = ['Jan','Fév','Mar','Avr','Mai','Juin','Juil','Août','Sep','Oct','Nov','Déc'];
                foreach (array_keys($monthlyViews) as $ym):
                    $m = (int)substr($ym, 5, 2) - 1;
                ?>
                    <span><?= $frMonths[$m] ?></span>
                <?php endforeach; ?>
            </div>
        </div>

    </div>
</section>

</body>
</html>