<?php
// ==========================================
// 1. RÉCUPÉRATION VIDÉO + ÉTATS
// ==========================================
if (!class_exists('User')) {
    require_once dirname(__DIR__, 3) . '/Classes/UserClass.php';
}

$videoId  = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$video    = null;
$errorMsg = null;

/* --- User connecté --- */
$currentUser     = User::current();
$currentUserId   = 0;
$isAuthenticated = false;
if ($currentUser) {
    $isAuthenticated = true;
    if (is_object($currentUser)) {
        if (method_exists($currentUser, 'getId')) $currentUserId = (int)$currentUser->getId();
        elseif (method_exists($currentUser, 'id')) $currentUserId = (int)$currentUser->id();
        elseif (isset($currentUser->id))          $currentUserId = (int)$currentUser->id;
    } elseif (is_array($currentUser)) {
        $currentUserId = (int)($currentUser['id'] ?? 0);
    }
}

/* --- CSRF token (protection API) --- */
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];

/* --- États --- */
$isLiked         = false;
$isSubscribed    = false;
$likeCount       = 0;
$subscriberCount = 0;
$comments        = [];

try {
    $pdo = Conn::getInstance()->getPdo();

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
        /* ✅ ANTI-FRAUDE VUES : 1 vue / 30 min / session / vidéo */
        $dedupKey = 'viewed_' . $videoId;
        if (time() - (int)($_SESSION[$dedupKey] ?? 0) > 1800) {
            $pdo->prepare("UPDATE videos SET views = views + 1 WHERE id = ?")->execute([$videoId]);
            $_SESSION[$dedupKey] = time();
            $video['views']++;
        }

        /* Compteurs */
        $st = $pdo->prepare("SELECT COUNT(*) FROM video_likes WHERE video_id = ?");
        $st->execute([$videoId]);
        $likeCount = (int)$st->fetchColumn();

        $st = $pdo->prepare("SELECT COUNT(*) FROM subscriptions WHERE channel_id = ?");
        $st->execute([(int)$video['author_id']]);
        $subscriberCount = (int)$st->fetchColumn();

        if ($isAuthenticated && $currentUserId > 0) {
            $st = $pdo->prepare("SELECT 1 FROM video_likes WHERE video_id = ? AND user_id = ?");
            $st->execute([$videoId, $currentUserId]);
            $isLiked = (bool)$st->fetchColumn();

            $st = $pdo->prepare("SELECT 1 FROM subscriptions WHERE channel_id = ? AND subscriber_id = ?");
            $st->execute([(int)$video['author_id'], $currentUserId]);
            $isSubscribed = (bool)$st->fetchColumn();
        }

        /* Commentaires (si la table existe) */
        try {
            $st = $pdo->prepare("
                SELECT c.*, u.first_name, u.last_name, u.avatar_url
                FROM comments c JOIN users u ON u.id = c.user_id
                WHERE c.video_id = ? AND c.status = 'approved'
                ORDER BY c.created_at DESC LIMIT 30
            ");
            $st->execute([$videoId]);
            $comments = $st->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) { /* table absente */ }
    }
} catch (Throwable $e) {
    $errorMsg = "Erreur de chargement : " . $e->getMessage();
}

// ==========================================
// 2. FONCTIONS
// ==========================================
function formatViews(int $views): string {
    if ($views >= 1000000) return round($views / 1000000, 1) . ' M';
    if ($views >= 1000)    return round($views / 1000, 0) . ' k';
    return (string)$views;
}

function timeAgo(string $date): string {
    $diff = time() - strtotime($date);
    if ($diff < 60)       return "À l'instant";
    if ($diff < 3600)     return "Il y a " . floor($diff / 60) . " min";
    if ($diff < 86400)    return "Il y a " . floor($diff / 3600) . " h";
    if ($diff < 604800)   return "Il y a " . floor($diff / 86400) . " j";
    if ($diff < 2592000)  return "Il y a " . floor($diff / 604800) . " sem.";
    return "Il y a " . floor($diff / 2592000) . " mois";
}
?>

<style>
    /* === Barre de recherche === */
    .search-bar {
        position: relative;
        max-width: 640px;
        margin: 0 auto 24px;
    }
    .search-bar input {
        width: 100%;
        padding: 14px 48px 14px 20px;
        background: #1e1e1e;
        border: 1px solid #333;
        border-radius: 999px;
        color: #fff;
        font-size: 15px;
        transition: border-color .2s;
    }
    .search-bar input:focus {
        outline: none;
        border-color: var(--accent, #ff2d3a);
    }
    .search-bar button {
        position: absolute;
        right: 6px;
        top: 6px;
        bottom: 6px;
        width: 40px;
        background: var(--accent, #ff2d3a);
        border: none;
        border-radius: 999px;
        color: #fff;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .search-results {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        margin-top: 8px;
        background: #1e1e1e;
        border: 1px solid #333;
        border-radius: 12px;
        max-height: 400px;
        overflow-y: auto;
        z-index: 50;
        display: none;
        box-shadow: 0 8px 32px rgba(0,0,0,.6);
    }
    .search-results.show { display: block; }
    .search-results a {
        display: flex;
        gap: 12px;
        padding: 10px 14px;
        text-decoration: none;
        color: inherit;
        border-bottom: 1px solid #2a2a2a;
    }
    .search-results a:hover { background: #272727; }
    .search-results a:last-child { border-bottom: none; }
    .search-results img {
        width: 100px;
        aspect-ratio: 16/9;
        border-radius: 6px;
        object-fit: cover;
        background: #111;
        flex-shrink: 0;
    }

    /* === Modal de partage === */
    .share-modal {
        position: fixed;
        inset: 0;
        background: rgba(0,0,0,.85);
        z-index: 9999;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }
    .share-modal.show { display: flex; }
    .share-modal-content {
        background: #1e1e1e;
        border-radius: 16px;
        padding: 28px;
        max-width: 480px;
        width: 100%;
        border: 1px solid #333;
    }
    .share-modal-content h3 {
        color: #fff;
        margin: 0 0 20px;
        font-size: 18px;
    }
    .share-options {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(90px, 1fr));
        gap: 12px;
    }
    .share-opt {
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 8px;
        padding: 16px 8px;
        background: #272727;
        border-radius: 12px;
        color: #fff;
        text-decoration: none;
        font-size: 12px;
        transition: transform .15s;
        cursor: pointer;
        border: none;
        font-family: inherit;
    }
    .share-opt:hover { transform: translateY(-2px); }
    .share-opt i { font-size: 26px; }

    /* === Bouton abonnement cloche === */
    .sub-group {
        display: inline-flex;
        border-radius: 20px;
        overflow: hidden;
        margin-left: 12px;
    }
    .sub-group button {
        border: none;
        cursor: pointer;
        font-weight: 600;
        font-size: 14px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: .2s;
    }
    .sub-main {
        padding: 8px 18px;
        color: #fff;
    }
    .sub-bell {
        padding: 8px 12px;
        color: #fff;
        border-left: 1px solid rgba(0,0,0,.2) !important;
    }

    /* === Commentaires === */
    .comment-item {
        display: flex;
        gap: 12px;
        margin-bottom: 20px;
    }
    .comment-avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: var(--accent, #ff2d3a);
        color: #fff;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 14px;
        flex-shrink: 0;
        overflow: hidden;
    }
    .comment-avatar img { width: 100%; height: 100%; object-fit: cover; }
    .comment-body { flex: 1; }
    .comment-author { color: #fff; font-weight: 600; font-size: 13px; margin-bottom: 4px; }
    .comment-text { color: #ddd; font-size: 14px; line-height: 1.5; white-space: pre-wrap; }
    .comment-meta { color: #888; font-size: 12px; margin-top: 6px; display: flex; gap: 16px; }
</style>

<?php if ($errorMsg): ?>
    <div style="text-align: center; padding: 80px 20px;">
        <i class="ph ph-warning-circle" style="font-size: 64px; color: var(--accent);"></i>
        <h2 style="color: #fff; margin: 16px 0 8px;">Oups...</h2>
        <p style="color: #aaa; margin-bottom: 24px;"><?= htmlspecialchars($errorMsg) ?></p>
        <a href="index.php?p=home" style="background: var(--accent); color: #fff; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: bold;">
            Retour à l'accueil
        </a>
    </div>

<?php else:
    $authorInitials = mb_strtoupper(
        mb_substr($video['first_name'], 0, 1, 'UTF-8') .
        mb_substr($video['last_name'],  0, 1, 'UTF-8'),
        'UTF-8'
    );
    $authorFullName = $video['first_name'] . ' ' . $video['last_name'];
    $isOwnVideo     = ($isAuthenticated && $currentUserId === (int)$video['author_id']);
    $videoTitle     = $video['title'];
?>

<!-- ============ BARRE DE RECHERCHE ============ -->
<div class="search-bar">
    <input type="text" id="searchInput" placeholder="Rechercher une vidéo, un créateur…" autocomplete="off">
    <button type="button" id="searchBtn">
        <i class="ph ph-magnifying-glass" style="font-size:18px"></i>
    </button>
    <div class="search-results" id="searchResults"></div>
</div>

<!-- ============ LAYOUT PRINCIPAL ============ -->
<div style="display: grid; grid-template-columns: 1fr 340px; gap: 24px; align-items: start;">
    <div>
        <!-- Lecteur vidéo -->
        <div id="playerWrap" style="background: #000; border-radius: 12px; overflow: hidden; margin-bottom: 16px; transition: .3s;">
            <video id="mainPlayer" controls playsinline preload="metadata"
                poster="<?= htmlspecialchars($video['thumbnail_url']) ?>"
                style="width: 100%; aspect-ratio: 16/9; display: block;">
                <source src="<?= htmlspecialchars($video['video_url']) ?>" type="video/mp4">
                Votre navigateur ne supporte pas la lecture vidéo.
            </video>
        </div>

        <h1 style="color: #fff; font-size: 22px; margin: 0 0 12px; line-height: 1.3;">
            <?= htmlspecialchars($videoTitle) ?>
        </h1>

        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 16px; padding-bottom: 16px; border-bottom: 1px solid #333; margin-bottom: 16px;">

            <!-- Auteur + abonnement -->
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 48px; height: 48px; border-radius: 50%; background: var(--accent); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 18px; font-weight: bold; overflow: hidden;">
                    <?php if (!empty($video['author_avatar'])): ?>
                        <img src="<?= htmlspecialchars($video['author_avatar']) ?>"
                             alt="<?= htmlspecialchars($authorFullName) ?>"
                             style="width:100%;height:100%;object-fit:cover;"
                             onerror="this.style.display='none'">
                    <?php else: ?>
                        <?= $authorInitials ?>
                    <?php endif; ?>
                </div>
                <div>
                    <a href="index.php?p=home" style="font-weight: 600; color: #fff; font-size: 15px; text-decoration: none;">
                        <?= htmlspecialchars($authorFullName) ?>
                    </a>
                    <div style="color: #aaa; font-size: 13px;">
                        <span id="subscriberCount"><?= formatViews($subscriberCount) ?> abonnés</span>
                    </div>
                </div>

                <?php if (!$isOwnVideo): ?>
                    <div class="sub-group">
                        <button id="btnSubscribe" class="sub-main"
                                data-channel="<?= (int)$video['author_id'] ?>"
                                data-subscribed="<?= $isSubscribed ? '1' : '0' ?>"
                                style="background: <?= $isSubscribed ? '#272727' : 'var(--accent)' ?>;">
                            <?= $isSubscribed
                                ? '<i class="ph ph-check"></i> <span>Abonné</span>'
                                : '<span>S\'abonner</span>' ?>
                        </button>
                        <button id="btnBell" class="sub-bell"
                                title="Notifications"
                                style="background: <?= $isSubscribed ? '#272727' : 'var(--accent)' ?>;">
                            <i class="ph ph-bell"></i>
                        </button>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Actions -->
            <div style="display: flex; gap: 8px; flex-wrap: wrap;">
                <button id="btnLike"
                        data-video="<?= (int)$video['id'] ?>"
                        data-liked="<?= $isLiked ? '1' : '0' ?>"
                        style="background: <?= $isLiked ? 'var(--accent)' : '#272727' ?>;
                               color: #fff; border: none; padding: 8px 16px; border-radius: 20px;
                               cursor: pointer; display: flex; align-items: center; gap: 6px; font-size: 14px; transition: .2s;">
                    <i class="ph <?= $isLiked ? 'ph-thumbs-up-fill' : 'ph-thumbs-up' ?>"></i>
                    <span id="likeCount"><?= formatViews($likeCount) ?></span>
                </button>

                <button id="btnShare"
                        style="background: #272727; color: #fff; border: none; padding: 8px 16px; border-radius: 20px; cursor: pointer; display: flex; align-items: center; gap: 6px; font-size: 14px;">
                    <i class="ph ph-share-network"></i> Partager
                </button>

                <button id="btnTheater"
                        title="Mode cinéma"
                        style="background: #272727; color: #fff; border: none; padding: 8px 16px; border-radius: 20px; cursor: pointer; font-size: 14px;">
                    <i class="ph ph-arrows-out-line-horizontal"></i>
                </button>

                <button id="btnDownload"
                        data-video="<?= (int)$video['id'] ?>"
                        title="Télécharger la vidéo"
                        style="background: #272727; color: #fff; border: none; padding: 8px 16px; border-radius: 20px; cursor: pointer; font-size: 14px;">
                    <i class="ph ph-download-simple"></i>
                </button>
            </div>
        </div>

        <!-- Description -->
        <div style="background: #1e1e1e; padding: 16px; border-radius: 12px; margin-bottom: 20px;">
            <div style="color: #fff; font-weight: 600; margin-bottom: 8px;">
                <?= formatViews((int)$video['views']) ?> vues · <?= timeAgo($video['published_at'] ?? $video['created_at']) ?>
                · <span style="color: var(--accent); text-transform: uppercase; font-size: 12px;">
                    <?= htmlspecialchars($video['category_name']) ?>
                </span>
            </div>
            <div style="color: #ddd; font-size: 14px; line-height: 1.6; white-space: pre-wrap;">
                <?= htmlspecialchars($video['description'] ?: 'Aucune description fournie.') ?>
            </div>
        </div>

        <!-- ============ COMMENTAIRES ============ -->
        <div style="background: #1e1e1e; padding: 20px; border-radius: 12px;">
            <h3 style="color: #fff; margin: 0 0 16px; font-size: 16px;">
                <?= count($comments) ?> commentaire<?= count($comments) > 1 ? 's' : '' ?>
            </h3>

            <?php if ($isAuthenticated): ?>
                <div style="display: flex; gap: 12px; margin-bottom: 24px;">
                    <div class="comment-avatar"><?= mb_strtoupper(mb_substr($currentUser->firstName() ?? 'U', 0, 1, 'UTF-8'), 'UTF-8') ?></div>
                    <div style="flex: 1;">
                        <textarea id="commentInput"
                                  placeholder="Ajouter un commentaire…"
                                  rows="2"
                                  style="width: 100%; padding: 10px 14px; background: #121212; border: 1px solid #333;
                                         border-radius: 8px; color: #fff; font-family: inherit; font-size: 14px; resize: vertical;"></textarea>
                        <div style="text-align: right; margin-top: 8px;">
                            <button id="btnComment"
                                    style="background: var(--accent); color: #fff; border: none;
                                           padding: 8px 20px; border-radius: 20px; cursor: pointer; font-weight: 600; font-size: 14px;">
                                Publier
                            </button>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (empty($comments)): ?>
                <p style="color: #888; text-align: center; padding: 20px;">
                    Aucun commentaire pour l'instant. Soyez le premier !
                </p>
            <?php else: ?>
                <?php foreach ($comments as $c):
                    $cInitials = mb_strtoupper(
                        mb_substr($c['first_name'] ?? '', 0, 1, 'UTF-8') .
                        mb_substr($c['last_name'] ?? '', 0, 1, 'UTF-8'),
                        'UTF-8'
                    );
                ?>
                    <div class="comment-item">
                        <div class="comment-avatar">
                            <?php if (!empty($c['avatar_url'])): ?>
                                <img src="<?= htmlspecialchars($c['avatar_url']) ?>" alt="">
                            <?php else: ?>
                                <?= $cInitials ?>
                            <?php endif; ?>
                        </div>
                        <div class="comment-body">
                            <div class="comment-author">
                                <?= htmlspecialchars(($c['first_name'] ?? '') . ' ' . ($c['last_name'] ?? '')) ?>
                            </div>
                            <div class="comment-text"><?= htmlspecialchars($c['content'] ?? '') ?></div>
                            <div class="comment-meta">
                                <span><?= timeAgo($c['created_at']) ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- ============ SUGGESTIONS ============ -->
    <aside>
        <h3 style="color: #fff; font-size: 16px; margin: 0 0 16px;">À découvrir aussi</h3>
        <div style="margin-bottom: 12px;">
            <input type="text" id="filterSuggestions"
                   placeholder="Filtrer les suggestions…"
                   style="width: 100%; padding: 8px 12px; background: #1e1e1e; border: 1px solid #333; border-radius: 8px; color: #fff; font-size: 13px;">
        </div>

        <div id="suggestionList">
            <?php
            try {
                $stmt2 = $pdo->prepare("SELECT v.id, v.title, v.thumbnail_url, v.duration_seconds,
                                               CONCAT(u.first_name, ' ', u.last_name) as author
                                        FROM videos v JOIN users u ON v.user_id = u.id
                                        WHERE v.status = 'published' AND v.id != ?
                                        ORDER BY v.created_at DESC LIMIT 10");
                $stmt2->execute([$videoId]);
                $suggestions = $stmt2->fetchAll(PDO::FETCH_ASSOC);
            } catch (Throwable $e) {
                $suggestions = [];
            }
            ?>

            <?php foreach ($suggestions as $sug): ?>
                <a href="index.php?p=detail&id=<?= $sug['id'] ?>"
                   class="suggestion-item"
                   data-title="<?= htmlspecialchars(strtolower($sug['title'] . ' ' . $sug['author'])) ?>"
                   style="display: flex; gap: 12px; margin-bottom: 12px; text-decoration: none; color: inherit; padding: 8px; border-radius: 8px; transition: .15s;"
                   onmouseover="this.style.background='#1e1e1e'"
                   onmouseout="this.style.background='transparent'">
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
        </div>
    </aside>
</div>

<!-- ============ MODAL PARTAGE ============ -->
<div class="share-modal" id="shareModal">
    <div class="share-modal-content">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h3 style="margin: 0;">Partager la vidéo</h3>
            <button onclick="closeShareModal()" style="background: none; border: none; color: #aaa; font-size: 24px; cursor: pointer;">
                <i class="ph ph-x"></i>
            </button>
        </div>

        <div class="share-options">
            <a href="#" class="share-opt" data-network="whatsapp" style="background: #25d366;">
                <i class="ph ph-whatsapp-logo"></i> WhatsApp
            </a>
            <a href="#" class="share-opt" data-network="facebook" style="background: #1877f2;">
                <i class="ph ph-facebook-logo"></i> Facebook
            </a>
            <a href="#" class="share-opt" data-network="twitter" style="background: #1da1f2;">
                <i class="ph ph-x-logo"></i> X / Twitter
            </a>
            <a href="#" class="share-opt" data-network="telegram" style="background: #0088cc;">
                <i class="ph ph-telegram-logo"></i> Telegram
            </a>
            <a href="#" class="share-opt" data-network="email" style="background: #ea4335;">
                <i class="ph ph-envelope-simple"></i> Email
            </a>
            <button class="share-opt" data-network="copy" style="background: #444;">
                <i class="ph ph-copy"></i> Copier le lien
            </button>
        </div>

        <div style="margin-top: 20px; padding-top: 16px; border-top: 1px solid #333;">
            <label style="color: #888; font-size: 12px; display: block; margin-bottom: 8px;">Lien direct</label>
            <div style="display: flex; gap: 8px;">
                <input type="text" id="shareLink" readonly
                       value="<?= htmlspecialchars('http://' . $_SERVER['HTTP_HOST'] . '/KazuriApp/public/index.php?p=detail&id=' . $videoId) ?>"
                       style="flex: 1; padding: 10px 12px; background: #121212; border: 1px solid #333; border-radius: 8px; color: #fff; font-size: 13px;">
                <button onclick="copyLink()" style="background: var(--accent); color: #fff; border: none; padding: 10px 16px; border-radius: 8px; cursor: pointer; font-weight: 600;">
                    Copier
                </button>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    'use strict';

    const API_URL   = 'index.php?api=video';
    const SEARCH_URL = 'index.php?api=video';
    const CSRF      = <?= json_encode($csrfToken) ?>;
    const IS_LOGGED = <?= $isAuthenticated ? 'true' : 'false' ?>;
    const SHARE_URL = window.location.href;
    const VIDEO_ID  = <?= (int)$videoId ?>;

    /* ============ TOAST ============ */
    function toast(msg, type = 'info') {
        const bg = type === 'success' ? '#4ade80' : type === 'error' ? '#ff4b5c' : '#1e1e1e';
        const fg = type === 'info' ? '#fff' : '#111';
        const el = document.createElement('div');
        el.textContent = msg;
        el.style.cssText = 'position:fixed;bottom:24px;left:50%;transform:translateX(-50%);background:' +
            bg + ';color:' + fg + ';padding:12px 24px;border-radius:8px;font-weight:600;font-size:14px;' +
            'z-index:99999;opacity:0;transition:opacity .25s;box-shadow:0 4px 20px rgba(0,0,0,.5);';
        document.body.appendChild(el);
        requestAnimationFrame(() => el.style.opacity = '1');
        setTimeout(() => { el.style.opacity = '0'; setTimeout(() => el.remove(), 250); }, 2400);
    }

    /* ============ API ============ */
    async function api(action, data = {}) {
        const fd = new FormData();
        fd.set('action', action);
        fd.set('csrf_token', CSRF);
        Object.entries(data).forEach(([k, v]) => fd.set(k, v));
        const res  = await fetch(API_URL, { method: 'POST', body: fd, credentials: 'same-origin' });
        const json = await res.json().catch(() => ({}));
        if (!res.ok || json.ok === false) {
            if (json.need_login) {
                toast('Connectez-vous pour continuer…', 'error');
                setTimeout(() => {
                    location.href = 'index.php?p=account&redirect=' +
                        encodeURIComponent(location.pathname + location.search);
                }, 1200);
                return null;
            }
            if (json.need_premium) {
                toast('Abonnement premium requis.', 'error');
                setTimeout(() => location.href = 'index.php?p=pricing', 1200);
                return null;
            }
            toast(json.error || 'Une erreur est survenue.', 'error');
            return null;
        }
        return json;
    }

    function fmtViews(n) {
        n = Number(n);
        if (n >= 1e6) return (n / 1e6).toFixed(1).replace('.0','') + ' M';
        if (n >= 1e3) return Math.round(n / 1e3) + ' k';
        return String(n);
    }

    /* ============ RECHERCHE ============ */
    const searchInput   = document.getElementById('searchInput');
    const searchResults = document.getElementById('searchResults');
    let searchTimer = null;

    function doSearch(query) {
        if (query.length < 2) { searchResults.classList.remove('show'); return; }
        clearTimeout(searchTimer);
        searchTimer = setTimeout(async () => {
            const fd = new FormData();
            fd.set('action', 'search');
            fd.set('q', query);
            try {
                const res  = await fetch(SEARCH_URL, { method: 'POST', body: fd, credentials: 'same-origin' });
                const data = await res.json();
                if (!data.ok || !data.videos || !data.videos.length) {
                    searchResults.innerHTML = '<div style="padding: 16px; color: #888; font-size: 14px;">Aucun résultat</div>';
                    searchResults.classList.add('show');
                    return;
                }
                searchResults.innerHTML = data.videos.map(v => `
                    <a href="index.php?p=detail&id=${v.id}">
                        <img src="${v.thumbnail_url || ''}" alt="" onerror="this.style.background='#333'">
                        <div style="flex: 1; min-width: 0;">
                            <div style="color: #fff; font-weight: 600; font-size: 13px; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">${escapeHtml(v.title)}</div>
                            <div style="color: #888; font-size: 12px; margin-top: 4px;">${escapeHtml(v.author)} · ${v.views} vues</div>
                        </div>
                    </a>
                `).join('');
                searchResults.classList.add('show');
            } catch (e) {
                console.error(e);
            }
        }, 250);
    }

    function escapeHtml(s) {
        return String(s ?? '').replace(/[&<>"']/g, c => ({
            '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
        })[c]);
    }

    if (searchInput) {
        searchInput.addEventListener('input', e => doSearch(e.target.value.trim()));
        searchInput.addEventListener('focus', () => {
            if (searchInput.value.trim().length >= 2) searchResults.classList.add('show');
        });
    }
    document.addEventListener('click', e => {
        if (!e.target.closest('.search-bar')) searchResults.classList.remove('show');
    });

    /* ============ FILTRE SUGGESTIONS ============ */
    const filterInput = document.getElementById('filterSuggestions');
    if (filterInput) {
        filterInput.addEventListener('input', () => {
            const q = filterInput.value.toLowerCase().trim();
            document.querySelectorAll('.suggestion-item').forEach(item => {
                const t = item.dataset.title || '';
                item.style.display = !q || t.includes(q) ? 'flex' : 'none';
            });
        });
    }

    /* ============ LIKE ============ */
    const btnLike = document.getElementById('btnLike');
    if (btnLike) {
        btnLike.addEventListener('click', async () => {
            btnLike.disabled = true;
            const data = await api('like', { video_id: btnLike.dataset.video });
            btnLike.disabled = false;
            if (!data) return;
            const liked = !!data.liked;
            btnLike.style.background = liked ? 'var(--accent)' : '#272727';
            btnLike.querySelector('i').className = 'ph ' + (liked ? 'ph-thumbs-up-fill' : 'ph-thumbs-up');
            document.getElementById('likeCount').textContent = fmtViews(data.count);
            toast(liked ? 'Vidéo aimée ❤️' : 'Like retiré', liked ? 'success' : 'info');
        });
    }

    /* ============ ABONNEMENT + CLOCHE ============ */
    const btnSub  = document.getElementById('btnSubscribe');
    const btnBell = document.getElementById('btnBell');
    if (btnSub) {
        btnSub.addEventListener('click', async () => {
            btnSub.disabled = true;
            const data = await api('subscribe', { channel_id: btnSub.dataset.channel });
            btnSub.disabled = false;
            if (!data) return;
            const subscribed = !!data.subscribed;
            const bg = subscribed ? '#272727' : 'var(--accent)';
            btnSub.style.background = bg;
            if (btnBell) btnBell.style.background = bg;
            btnSub.innerHTML = subscribed
                ? '<i class="ph ph-check"></i> <span>Abonné</span>'
                : '<span>S\'abonner</span>';

            const countEl = document.getElementById('subscriberCount');
            if (countEl && data.total !== undefined) {
                countEl.textContent = fmtViews(data.total) + ' abonnés';
            }
            toast(subscribed ? 'Abonnement enregistré ✅' : 'Abonnement annulé', subscribed ? 'success' : 'info');
        });
    }
    if (btnBell) {
        btnBell.addEventListener('click', () => {
            toast('Notifications activées 🔔', 'success');
        });
    }

    /* ============ PARTAGE (MODAL) ============ */
    const shareModal = document.getElementById('shareModal');
    function openShareModal() { shareModal.classList.add('show'); }
    function closeShareModal() { shareModal.classList.remove('show'); }
    window.closeShareModal = closeShareModal;

    document.getElementById('btnShare')?.addEventListener('click', openShareModal);
    shareModal?.addEventListener('click', e => {
        if (e.target === shareModal) closeShareModal();
    });

    const shareTitle = <?= json_encode($videoTitle) ?>;
    const shareText  = shareTitle + ' sur Kazuri';

    document.querySelectorAll('.share-opt[data-network]').forEach(el => {
        el.addEventListener('click', async (e) => {
            e.preventDefault();
            const net = el.dataset.network;
            const u   = encodeURIComponent(SHARE_URL);
            const t   = encodeURIComponent(shareText);

            const urls = {
                whatsapp: `https://wa.me/?text=${t}%20${u}`,
                facebook: `https://www.facebook.com/sharer/sharer.php?u=${u}`,
                twitter:  `https://twitter.com/intent/tweet?text=${t}&url=${u}`,
                telegram: `https://t.me/share/url?url=${u}&text=${t}`,
                email:    `mailto:?subject=${encodeURIComponent(shareTitle)}&body=${t}%20${u}`,
            };

            if (net === 'copy') {
                try { await navigator.clipboard.writeText(SHARE_URL); toast('Lien copié 📋', 'success'); }
                catch { prompt('Copiez ce lien :', SHARE_URL); }
                closeShareModal();
                return;
            }
            if (urls[net]) window.open(urls[net], '_blank', 'width=600,height=500');
        });
    });

    window.copyLink = async function () {
        const inp = document.getElementById('shareLink');
        try {
            await navigator.clipboard.writeText(inp.value);
            toast('Lien copié 📋', 'success');
        } catch {
            inp.select();
            document.execCommand('copy');
            toast('Lien copié 📋', 'success');
        }
    };

    /* ============ THÉÂTRE ============ */
    const btnTheater = document.getElementById('btnTheater');
    if (btnTheater) {
        btnTheater.addEventListener('click', () => {
            document.body.classList.toggle('theater-mode');
        });
    }
    const style = document.createElement('style');
    style.textContent = `
        .theater-mode .search-bar { display: none; }
        .theater-mode div[style*="grid-template-columns: 1fr 340px"] {
            grid-template-columns: 1fr !important;
            max-width: 1280px;
            margin: 0 auto;
        }
        .theater-mode aside { display: none; }
    `;
    document.head.appendChild(style);

    /* ============ TÉLÉCHARGEMENT ============ */
    const btnDl = document.getElementById('btnDownload');
    if (btnDl) {
        btnDl.addEventListener('click', async () => {
            btnDl.disabled = true;
            const old = btnDl.innerHTML;
            btnDl.innerHTML = '<i class="ph ph-spinner" style="animation:spin 1s linear infinite;display:inline-block"></i>';
            const data = await api('download', { video_id: btnDl.dataset.video });
            btnDl.disabled = false;
            btnDl.innerHTML = old;
            if (!data) return;
            const a = document.createElement('a');
            a.href = data.url;
            a.download = data.filename || 'video.mp4';
            a.style.display = 'none';
            document.body.appendChild(a);
            a.click();
            a.remove();
            toast('Téléchargement lancé ⬇️', 'success');
        });
    }

    /* ============ COMMENTAIRE ============ */
    const btnComment = document.getElementById('btnComment');
    if (btnComment) {
        btnComment.addEventListener('click', async () => {
            const input = document.getElementById('commentInput');
            const text  = input.value.trim();
            if (!text) { toast('Écrivez un commentaire d\'abord', 'error'); return; }
            btnComment.disabled = true;
            const data = await api('comment', { video_id: VIDEO_ID, content: text });
            btnComment.disabled = false;
            if (!data) return;
            input.value = '';
            toast('Commentaire publié ✅', 'success');
            setTimeout(() => location.reload(), 800);
        });
    }

    /* ============ RACCOURCIS CLAVIER ============ */
    const player = document.getElementById('mainPlayer');
    document.addEventListener('keydown', e => {
        if (['INPUT', 'TEXTAREA'].includes(e.target.tagName)) return;
        if (!player) return;
        switch (e.key.toLowerCase()) {
            case ' ':
            case 'k':
                e.preventDefault();
                player.paused ? player.play() : player.pause();
                break;
            case 'm':
                player.muted = !player.muted;
                break;
            case 'f':
                if (document.fullscreenElement) document.exitFullscreen();
                else player.requestFullscreen();
                break;
            case 't':
                document.body.classList.toggle('theater-mode');
                break;
            case 'arrowright':
                player.currentTime += 5;
                break;
            case 'arrowleft':
                player.currentTime -= 5;
                break;
        }
    });

    /* ============ ANIMATION SPINNER ============ */
    if (!document.getElementById('spinKeyframe')) {
        const s = document.createElement('style');
        s.id = 'spinKeyframe';
        s.textContent = '@keyframes spin { to { transform: rotate(360deg); } }';
        document.head.appendChild(s);
    }

})();
</script>

<?php endif; ?>