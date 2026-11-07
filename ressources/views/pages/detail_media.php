<?php
// ==========================================
// 1. RÉCUPÉRATION DE LA VIDÉO
// ==========================================
if (!class_exists('User')) {
    require_once dirname(__DIR__, 3) . '/Classes/UserClass.php';
}

$videoId = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$video = null;
$errorMsg = null;

try {
    $pdo = Conn::getInstance()->getPdo();

    // Requête pour récupérer la vidéo + auteur + catégorie
    $sql = "SELECT v.*, 
                   u.first_name, u.last_name, u.avatar_url AS author_avatar, u.id AS author_id,
                   c.name AS category_name, c.slug AS category_slug
            FROM videos v
            JOIN users u ON v.user_id = u.id
            JOIN categories c ON v.category_id = c.id
            WHERE v.id = ? AND v.status = 'published'
            LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$videoId]);
    $video = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$video) {
        $errorMsg = "Cette vidéo est introuvable ou n'est pas encore publiée.";
    } else {
        // ✅ Incrémenter le compteur de vues
        $pdo->prepare("UPDATE videos SET views = views + 1 WHERE id = ?")->execute([$videoId]);
        $video['views']++; // On l'incrémente aussi localement pour l'affichage
    }

} catch (Throwable $e) {
    $errorMsg = "Erreur de chargement : " . $e->getMessage();
}

// ==========================================
// 2. FONCTIONS UTILITAIRES
// ==========================================
function formatViews(int $views): string {
    if ($views >= 1000000) return round($views / 1000000, 1) . ' M';
    if ($views >= 1000) return round($views / 1000, 0) . ' k';
    return (string)$views;
}

function timeAgo(string $date): string {
    $diff = time() - strtotime($date);
    if ($diff < 60) return "À l'instant";
    if ($diff < 3600) return "Il y a " . floor($diff / 60) . " min";
    if ($diff < 86400) return "Il y a " . floor($diff / 3600) . " h";
    if ($diff < 604800) return "Il y a " . floor($diff / 86400) . " j";
    if ($diff < 2592000) return "Il y a " . floor($diff / 604800) . " sem.";
    return "Il y a " . floor($diff / 2592000) . " mois";
}
?>

<?php if ($errorMsg): ?>
    <!-- ===== ERREUR ===== -->
    <div style="text-align: center; padding: 80px 20px;">
        <i class="ph ph-warning-circle" style="font-size: 64px; color: var(--accent);"></i>
        <h2 style="color: #fff; margin: 16px 0 8px;">Oups...</h2>
        <p style="color: #aaa; margin-bottom: 24px;"><?= htmlspecialchars($errorMsg) ?></p>
        <a href="index.php?p=home" style="background: var(--accent); color: #fff; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: bold;">
            Retour à l'accueil
        </a>
    </div>

<?php else: 
    // Initiales de l'auteur (ex: "HI" pour Hervé IRAGI)
    $authorInitials = mb_strtoupper(
        mb_substr($video['first_name'], 0, 1, 'UTF-8') . 
        mb_substr($video['last_name'], 0, 1, 'UTF-8'), 
        'UTF-8'
    );
    $authorFullName = $video['first_name'] . ' ' . $video['last_name'];
?>

<div style="display: grid; grid-template-columns: 1fr 340px; gap: 24px; align-items: start;">
    
    <!-- ===== COLONNE GAUCHE : LECTEUR + INFOS ===== -->
    <div>
        <!-- LECTEUR VIDÉO -->
        <div style="background: #000; border-radius: 12px; overflow: hidden; margin-bottom: 20px;">
            <video 
                id="mainPlayer"
                controls 
                playsinline
                poster="<?= htmlspecialchars($video['thumbnail_url']) ?>"
                style="width: 100%; aspect-ratio: 16/9; display: block;">
                <source src="<?= htmlspecialchars($video['video_url']) ?>" type="video/mp4">
                Votre navigateur ne supporte pas la lecture vidéo.
            </video>
        </div>

        <!-- TITRE ET INFOS -->
        <h1 style="color: #fff; font-size: 22px; margin: 0 0 12px; line-height: 1.3;">
            <?= htmlspecialchars($video['title']) ?>
        </h1>

        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; padding-bottom: 16px; border-bottom: 1px solid #333; margin-bottom: 16px;">
            
            <!-- AUTEUR AVEC INITIALES -->
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 48px; height: 48px; border-radius: 50%; background: var(--accent); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 18px; font-weight: bold;">
                    <?= $authorInitials ?>
                </div>
                <div>
                    <div style="font-weight: 600; color: #fff; font-size: 15px;"><?= htmlspecialchars($authorFullName) ?></div>
                    <div style="color: #aaa; font-size: 13px;">Créateur · <span style="color: #3ea6ff;">Voir la chaîne</span></div>
                </div>
                <button style="background: var(--accent); color: #fff; border: none; padding: 8px 20px; border-radius: 20px; font-weight: 600; cursor: pointer; font-size: 14px; margin-left: 12px;">
                    S'abonner
                </button>
            </div>

            <!-- ACTIONS -->
            <div style="display: flex; gap: 8px;">
                <button style="background: #272727; color: #fff; border: none; padding: 8px 16px; border-radius: 20px; cursor: pointer; display: flex; align-items: center; gap: 6px; font-size: 14px;">
                    <i class="ph ph-thumbs-up"></i> <?= formatViews((int)($video['likes'] ?? 0)) ?>
                </button>
                <button style="background: #272727; color: #fff; border: none; padding: 8px 16px; border-radius: 20px; cursor: pointer; display: flex; align-items: center; gap: 6px; font-size: 14px;">
                    <i class="ph ph-share-network"></i> Partager
                </button>
                <button style="background: #272727; color: #fff; border: none; padding: 8px 16px; border-radius: 20px; cursor: pointer; font-size: 14px;">
                    <i class="ph ph-download-simple"></i>
                </button>
            </div>
        </div>

        <!-- MÉTADONNÉES -->
        <div style="background: #1e1e1e; padding: 16px; border-radius: 12px; margin-bottom: 20px;">
            <div style="color: #fff; font-weight: 600; margin-bottom: 8px;">
                <?= formatViews((int)$video['views']) ?> vues · <?= timeAgo($video['published_at'] ?? $video['created_at']) ?>
            </div>
            <div style="color: #ddd; font-size: 14px; line-height: 1.6; white-space: pre-wrap;"><?= htmlspecialchars($video['description'] ?: 'Aucune description fournie.') ?></div>
        </div>

        <!-- CATÉGORIE -->
        <div>
            <span style="background: var(--accent); color: #fff; padding: 4px 12px; border-radius: 6px; font-size: 12px; font-weight: bold; text-transform: uppercase;">
                <?= htmlspecialchars($video['category_name']) ?>
            </span>
        </div>
    </div>

    <!-- ===== COLONNE DROITE : SUGGESTIONS ===== -->
    <aside>
        <h3 style="color: #fff; font-size: 16px; margin: 0 0 16px;">À découvrir aussi</h3>
        
        <?php
        // Récupérer 5 autres vidéos publiées
        try {
            $stmt2 = $pdo->prepare("SELECT v.id, v.title, v.thumbnail_url, v.duration_seconds, 
                                           CONCAT(u.first_name, ' ', u.last_name) as author
                                    FROM videos v JOIN users u ON v.user_id = u.id
                                    WHERE v.status = 'published' AND v.id != ?
                                    ORDER BY v.created_at DESC LIMIT 5");
            $stmt2->execute([$videoId]);
            $suggestions = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            $suggestions = [];
        }
        ?>

        <?php foreach ($suggestions as $sug): ?>
            <a href="index.php?p=detail&id=<?= $sug['id'] ?>" style="display: flex; gap: 12px; margin-bottom: 12px; text-decoration: none; color: inherit; padding: 8px; border-radius: 8px;">
                <div style="position: relative; flex-shrink: 0;">
                    <img src="<?= htmlspecialchars($sug['thumbnail_url']) ?>" 
                         style="width: 140px; aspect-ratio: 16/9; border-radius: 8px; object-fit: cover; background: #222;">
                    <span style="position: absolute; bottom: 4px; right: 4px; background: rgba(0,0,0,0.8); color: #fff; font-size: 11px; padding: 2px 4px; border-radius: 3px;">
                        <?= gmdate('i:s', $sug['duration_seconds'] ?? 0) ?>
                    </span>
                </div>
                <div>
                    <div style="font-weight: 600; font-size: 14px; color: #fff; line-height: 1.3; margin-bottom: 4px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;">
                        <?= htmlspecialchars($sug['title']) ?>
                    </div>
                    <div style="font-size: 12px; color: #aaa;"><?= htmlspecialchars($sug['author']) ?></div>
                </div>
            </a>
        <?php endforeach; ?>

        <?php if (empty($suggestions)): ?>
            <p style="color: #aaa; font-size: 14px;">Aucune autre vidéo disponible.</p>
        <?php endif; ?>
    </aside>
</div>

<?php endif; ?>