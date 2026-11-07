<?php
if (!class_exists('User')) {
    require_once dirname(__DIR__, 3) . '/Classes/UserClass.php';
}

$currentUser = User::current();
if (!$currentUser) {
    header('Location: index.php?p=account');
    exit;
}

// ==========================================
// RÉCUPÉRATION DES VIDÉOS AIMÉES
// ==========================================
$likedVideos = [];
$debugError = null;

try {
    $pdo = Conn::getInstance()->getPdo();

    // Vérifier si la table likes existe
    $hasLikesTable = false;
    try {
        $check = $pdo->query("SHOW TABLES LIKE 'video_likes'");
        $hasLikesTable = ($check->rowCount() > 0);
    } catch (Throwable $e) {
        $hasLikesTable = false;
    }

    if ($hasLikesTable) {
        // Si la table existe, on récupère les vidéos aimées
        $sql = "SELECT v.id, v.title, v.thumbnail_url, v.duration_seconds, v.views, v.created_at,
                       u.first_name, u.last_name
                FROM video_likes vl
                JOIN videos v ON vl.video_id = v.id
                JOIN users u ON v.user_id = u.id
                WHERE vl.user_id = ? AND v.status = 'published'
                ORDER BY vl.created_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$currentUser->id()]);
        $likedVideos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    // Si la table n'existe pas, le tableau reste vide (on affichera le message)

} catch (Throwable $e) {
    $debugError = $e->getMessage();
}

// Fonctions utilitaires
function formatViews(int $views): string {
    if ($views >= 1000000) return round($views / 1000000, 1) . ' M';
    if ($views >= 1000) return round($views / 1000, 0) . ' k';
    return (string)$views;
}

function formatDuration(?int $seconds): string {
    if (!$seconds) return '0:00';
    $h = floor($seconds / 3600);
    $m = floor(($seconds % 3600) / 60);
    $s = $seconds % 60;
    return $h > 0 ? sprintf('%d:%02d:%02d', $h, $m, $s) : sprintf('%d:%02d', $m, $s);
}

function getInitials(string $firstName, string $lastName): string {
    $first = mb_substr(trim($firstName), 0, 1, 'UTF-8');
    $last  = mb_substr(trim($lastName), 0, 1, 'UTF-8');
    return mb_strtoupper($first . $last, 'UTF-8');
}
?>

<div style="margin-bottom: 32px;">
    <h1 style="color: #fff; font-size: 28px; margin: 0 0 8px;">
        <i class="ph ph-heart" style="color: var(--accent);"></i> Vidéos aimées
    </h1>
    <p style="color: #aaa; margin: 0; font-size: 15px;">
        Retrouvez toutes les vidéos que vous avez aimées sur Kazuri.
    </p>
</div>

<?php if ($debugError): ?>
    <div style="background: rgba(255,75,92,0.1); border: 1px solid #ff4b5c; color: #ff4b5c; padding: 16px; border-radius: 8px; margin-bottom: 20px;">
        <strong>⚠️ Erreur :</strong> <?= htmlspecialchars($debugError) ?>
    </div>
<?php endif; ?>

<?php if (empty($likedVideos)): ?>
    <!-- Message vide -->
    <div style="text-align: center; padding: 80px 20px; color: #aaa;">
        <i class="ph ph-heart" style="font-size: 80px; color: #444; margin-bottom: 20px; display: block;"></i>
        <h2 style="color: #fff; margin-bottom: 12px;">Vous n'avez encore aimé aucune vidéo</h2>
        <p style="margin-bottom: 24px;">Explorez le catalogue et cliquez sur ❤️ pour ajouter des vidéos à vos favoris.</p>
        <a href="index.php?p=home" style="background: var(--accent); color: #fff; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: bold;">
            <i class="ph ph-compass"></i> Découvrir le catalogue
        </a>
    </div>
<?php else: ?>
    <!-- Grille des vidéos aimées -->
    <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 24px 16px;">
        <?php foreach ($likedVideos as $video): 
            $thumb = !empty($video['thumbnail_url']) ? $video['thumbnail_url'] : 'https://picsum.photos/seed/' . urlencode($video['title']) . '/600/338';
            $authorFullName = $video['first_name'] . ' ' . $video['last_name'];
            $authorInitials = getInitials($video['first_name'], $video['last_name']);
        ?>
        <a href="index.php?p=detail&id=<?= $video['id'] ?>" style="cursor: pointer; text-decoration: none; color: inherit; display: flex; flex-direction: column; gap: 12px;">
            <div style="position: relative; width: 100%; aspect-ratio: 16/9; border-radius: 12px; overflow: hidden; background: #222;">
                <img src="<?= htmlspecialchars($thumb) ?>" alt="<?= htmlspecialchars($video['title']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                <span style="position: absolute; bottom: 8px; right: 8px; background: rgba(0,0,0,0.8); color: #fff; font-size: 12px; padding: 2px 6px; border-radius: 4px;">
                    <?= formatDuration((int)$video['duration_seconds']) ?>
                </span>
            </div>
            <div style="display: flex; gap: 12px;">
                <div style="width: 36px; height: 36px; flex-shrink: 0; border-radius: 50%; background: var(--accent); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 14px;">
                    <?= $authorInitials ?>
                </div>
                <div>
                    <h3 style="font-size: 15px; font-weight: 600; margin: 0 0 4px; color: #fff; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                        <?= htmlspecialchars($video['title']) ?>
                    </h3>
                    <p style="font-size: 13px; color: #aaa; margin: 0;"><?= htmlspecialchars($authorFullName) ?></p>
                    <p style="font-size: 13px; color: #aaa; margin: 0;"><?= formatViews((int)$video['views']) ?> vues</p>
                </div>
            </div>
        </a>
        <?php endforeach; ?>
    </div>
<?php endif; ?>