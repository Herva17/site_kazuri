<?php
// Sécurité : on s'assure que les classes sont chargées
if (!class_exists('User')) {
    require_once dirname(__DIR__, 3) . '/Classes/UserClass.php';
}

// ==========================================
// RÉCUPÉRATION DES VIDÉOS DEPUIS LA BASE DE DONNÉES
// ==========================================
$videos = [];
$heroVideo = null;
$debugError = null;

try {
    $pdo = Conn::getInstance()->getPdo();

    // ✅ DÉTECTION : la colonne is_featured existe-t-elle ?
    $hasIsFeatured = false;
    try {
        $checkCol = $pdo->query("SHOW COLUMNS FROM videos LIKE 'is_featured'");
        $hasIsFeatured = ($checkCol->rowCount() > 0);
    } catch (Throwable $e) {
        $hasIsFeatured = false;
    }

    // ✅ Construction dynamique de la requête
    $featuredSelect = $hasIsFeatured ? ', v.is_featured' : ', 0 AS is_featured';

    $sql = "SELECT v.id, v.title, v.description, v.thumbnail_url, v.duration_seconds, 
                   v.views, v.created_at" . $featuredSelect . ",
                   u.first_name, u.last_name,
                   c.slug as category_slug, c.name as category_name
            FROM videos v
            JOIN users u ON v.user_id = u.id
            JOIN categories c ON v.category_id = c.id
            WHERE v.status = 'published'
            ORDER BY v.created_at DESC";
            
    $stmt = $pdo->query($sql);
    $allVideos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // ✅ On sépare la vidéo "À la une" (ou on prend la plus récente par défaut)
    foreach ($allVideos as $v) {
        if ($v['is_featured'] == 1) {
            $heroVideo = $v;
            break;
        }
    }
    if (!$heroVideo && count($allVideos) > 0) {
        $heroVideo = $allVideos[0];
    }

    $videos = $allVideos;

} catch (Throwable $e) {
    $debugError = $e->getMessage();
    $allVideos = [];
}

// ==========================================
// FONCTIONS UTILITAIRES
// ==========================================
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

function timeAgo(string $date): string {
    $timestamp = strtotime($date);
    $diff = time() - $timestamp;
    if ($diff < 60) return "À l'instant";
    if ($diff < 3600) return "Il y a " . floor($diff / 60) . " min";
    if ($diff < 86400) return "Il y a " . floor($diff / 3600) . " h";
    if ($diff < 604800) return "Il y a " . floor($diff / 86400) . " j";
    return "Il y a " . floor($diff / 2592000) . " mois";
}

function normalizeCategory(string $slug): string {
    $slug = strtolower($slug);
    if (in_array($slug, ['film', 'films', 'movie', 'movies'])) return 'film';
    if (in_array($slug, ['series', 'série', 'séries', 'tv shows'])) return 'series';
    if (in_array($slug, ['musique', 'music', 'audio'])) return 'musique';
    if (in_array($slug, ['reel', 'reels', 'short', 'shorts'])) return 'reel';
    return 'autre';
}

// ✅ NOUVELLE FONCTION : Génère les initiales d'un nom
function getInitials(string $firstName, string $lastName): string {
    $first = mb_substr(trim($firstName), 0, 1, 'UTF-8');
    $last  = mb_substr(trim($lastName), 0, 1, 'UTF-8');
    return mb_strtoupper($first . $last, 'UTF-8');
}

// ✅ Petite fonction pour varier légèrement la couleur de fond des initiales
function getInitialsColor(string $name): string {
    $colors = ['#ff2d3a', '#ff6b35', '#8b5cf6', '#3b82f6', '#10b981', '#ec4899', '#f59e0b'];
    $index = crc32($name) % count($colors);
    return $colors[$index];
}
?>

<!-- ✅ AFFICHAGE D'ERREUR SI LA REQUÊTE ÉCHOUE -->
<?php if ($debugError): ?>
    <div style="background: rgba(255,75,92,0.1); border: 1px solid #ff4b5c; color: #ff4b5c; padding: 16px; border-radius: 8px; margin-bottom: 20px;">
        <strong>⚠️ Erreur SQL :</strong> <?= htmlspecialchars($debugError) ?>
    </div>
<?php endif; ?>

<!-- BANNIÈRE HERO DYNAMIQUE -->
<?php if ($heroVideo): 
    $heroFullName = $heroVideo['first_name'] . ' ' . $heroVideo['last_name'];
    $heroInitials = getInitials($heroVideo['first_name'], $heroVideo['last_name']);
?>
<div class="hero-banner">
    <img src="<?= htmlspecialchars($heroVideo['thumbnail_url'] ?: 'https://picsum.photos/seed/hero/1200/600') ?>" alt="Hero">
    <div class="hero-content">
        <span style="background:var(--accent); color:#fff; padding:4px 8px; border-radius:4px; font-size:12px; font-weight:bold;">À LA UNE</span>
        <h1><?= htmlspecialchars($heroVideo['title']) ?></h1>
        <p><?= htmlspecialchars(substr($heroVideo['description'] ?? 'Découvrez ce nouveau contenu exceptionnel sur Kazuri.', 0, 150)) ?>...</p>
        
        <!-- Infos auteur -->
        <div style="display: flex; align-items: center; gap: 10px; margin: 16px 0 20px;">
            <div style="width: 36px; height: 36px; border-radius: 50%; background: var(--accent); color: #fff; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 14px;">
                <?= $heroInitials ?>
            </div>
            <span style="color: #ddd; font-size: 14px; font-weight: 500;"><?= htmlspecialchars($heroFullName) ?></span>
        </div>
        
        <a href="index.php?p=detail&id=<?= $heroVideo['id'] ?>" class="btn-watch"><i class="ph-fill ph-play"></i> Regarder</a>
    </div>
</div>
<?php else: ?>
<div class="hero-banner" style="display:flex; align-items:center; justify-content:center; background:#1e1e1e;">
    <div style="text-align:center;">
        <h2 style="color:#fff; margin-bottom:10px;">Aucune vidéo disponible</h2>
        <p style="color:#aaa;">Les créateurs n'ont pas encore publié de contenu.</p>
    </div>
</div>
<?php endif; ?>

<!-- FILTRES INTERACTIFS -->
<div class="filters">
    <button class="chip active" onclick="filterContent('all', this)">Tout</button>
    <button class="chip" onclick="filterContent('film', this)">Films</button>
    <button class="chip" onclick="filterContent('series', this)">Séries</button>
    <button class="chip" onclick="filterContent('musique', this)">Musique</button>
    <button class="chip" onclick="filterContent('reel', this)">Reels</button>
</div>

<!-- GRILLE DE VIDÉOS DYNAMIQUE -->
<div class="video-grid" id="videoGrid">
    <?php if (empty($videos)): ?>
        <div style="grid-column: 1 / -1; text-align: center; padding: 60px 20px; color: #aaa;">
            <i class="ph ph-film-strip" style="font-size: 64px; color: #444; margin-bottom: 16px; display: block;"></i>
            <h3 style="color: #fff; margin-bottom: 8px;">Le catalogue est vide</h3>
            <p>Revenez plus tard pour découvrir de nouveaux contenus.</p>
        </div>
    <?php else: ?>
        <?php foreach ($videos as $video): 
            $thumb = !empty($video['thumbnail_url']) ? $video['thumbnail_url'] : 'https://picsum.photos/seed/' . urlencode($video['title']) . '/600/338';
            $authorFullName = $video['first_name'] . ' ' . $video['last_name'];
            $authorInitials = getInitials($video['first_name'], $video['last_name']);
            $initialsBg = getInitialsColor($authorFullName);
            $jsCategory = normalizeCategory($video['category_slug']);
        ?>
        <a href="index.php?p=detail&id=<?= $video['id'] ?>" class="video-card" data-category="<?= $jsCategory ?>">
            <div class="thumbnail-container">
                <img src="<?= htmlspecialchars($thumb) ?>" 
                     alt="<?= htmlspecialchars($video['title']) ?>"
                     onerror="this.style.display='none'; this.parentElement.classList.add('no-image');">
                <span class="duration-badge"><?= formatDuration((int)$video['duration_seconds']) ?></span>
            </div>
            <div class="video-info">
                <!-- ✅ INITIALES DE L'AUTEUR AU LIEU DE L'AVATAR -->
                <div style="width: 36px; height: 36px; flex-shrink: 0; border-radius: 50%; background: <?= $initialsBg ?>; color: #fff; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 14px; letter-spacing: 0.5px;">
                    <?= $authorInitials ?>
                </div>
                <div class="video-text">
                    <h3 class="video-title"><?= htmlspecialchars($video['title']) ?></h3>
                    <p class="video-meta"><?= htmlspecialchars($authorFullName) ?></p>
                    <p class="video-meta"><?= formatViews((int)$video['views']) ?> vues · <?= timeAgo($video['created_at']) ?></p>
                </div>
            </div>
        </a>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- SCRIPT DE FILTRAGE -->
<script>
function filterContent(category, btnElement) {
    const buttons = document.querySelectorAll('.filters .chip');
    buttons.forEach(btn => btn.classList.remove('active'));
    btnElement.classList.add('active');

    const cards = document.querySelectorAll('.video-card');
    cards.forEach(card => {
        const cardCategory = card.getAttribute('data-category');
        if (category === 'all' || cardCategory === category) {
            card.style.display = 'flex';
            setTimeout(() => { card.style.opacity = '1'; }, 10);
        } else {
            card.style.display = 'none';
        }
    });
}
</script>