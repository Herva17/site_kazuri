<?php
    // ✅ RÉCUPÉRATION DES DONNÉES RÉELLES DEPUIS LA BDD
    $p = [];
    try {
        require_once dirname(__DIR__, 3) . '/Classes/Conn.php';
        $pdo = Conn::getInstance()->getPdo();
        $stmt = $pdo->query("
            SELECT v.id, v.title, v.description, v.video_url, v.published_at, 
                   v.duration_seconds, v.thumbnail_url, v.views,
                   c.slug as category_slug, c.name as category_name,
                   CONCAT(u.first_name, ' ', u.last_name) as author_name
            FROM videos v
            LEFT JOIN categories c ON v.category_id = c.id
            LEFT JOIN users u ON v.user_id = u.id
            WHERE v.status = 'published'
            ORDER BY v.published_at DESC, v.created_at DESC
        ");
        foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $p[] = [
                'id'       => (int)$r['id'],
                'title'    => $r['title'],
                'desc'     => $r['description'],
                'url'      => $r['video_url'],
                'date'     => $r['published_at'],
                'duration' => (int)($r['duration_seconds'] ?? 0),
                'thumb'    => $r['thumbnail_url'],
                'views'    => (int)($r['views'] ?? 0),
                'category' => $r['category_name'] ?? 'Kazuri',
                'author'   => $r['author_name'] ?? 'Kazuri Studio',
            ];
        }
    } catch (Throwable $e) {
        $p = [];
    }

    // ✅ FONCTIONS UTILITAIRES
    if (!function_exists('formatDuration')) {
        function formatDuration(int $seconds): string {
            if ($seconds <= 0) return '0:00';
            $h = floor($seconds / 3600);
            $m = floor(($seconds % 3600) / 60);
            $s = $seconds % 60;
            return $h > 0 ? sprintf('%d:%02d:%02d', $h, $m, $s) : sprintf('%d:%02d', $m, $s);
        }
    }
    if (!function_exists('formatViews')) {
        function formatViews(int $views): string {
            if ($views >= 1000000) return round($views / 1000000, 1) . ' M';
            if ($views >= 1000) return round($views / 1000, 0) . ' k';
            return (string)$views;
        }
    }
    if (!function_exists('timeAgo')) {
        function timeAgo(?string $date): string {
            if (!$date) return 'Nouveau';
            $diff = time() - strtotime($date);
            if ($diff < 60) return "À l\'instant";
            if ($diff < 3600) return "Il y a " . floor($diff / 60) . " min";
            if ($diff < 86400) return "Il y a " . floor($diff / 3600) . " h";
            if ($diff < 604800) return "Il y a " . floor($diff / 86400) . " j";
            return date('d/m/Y', strtotime($date));
        }
    }
    if (!function_exists('resolveMediaPath')) {
        function resolveMediaPath($path) {
            if (empty($path)) return '';
            if (str_starts_with($path, 'http')) return $path;
            $root = dirname(__DIR__, 3);
            if (file_exists($root . '/public/' . $path)) return $path;
            if (file_exists($root . '/' . $path)) return '../' . $path;
            return $path;
        }
    }

    // ✅ FILTRAGE PAR CATÉGORIE
    $films = []; $series = []; $autres = [];
    foreach ($p as $v) {
        $cat = strtolower($v['category']);
        if (strpos($cat, 'film') !== false) $films[] = $v;
        elseif (strpos($cat, 'séri') !== false || strpos($cat, 'serie') !== false) $series[] = $v;
        else $autres[] = $v;
    }

    // ✅ FALLBACKS STATIQUES
    $staticFilms = [
        ['file' => '13.png', 'title' => 'Impuissant', 'duration' => '120 min', 'genre' => 'Drame', 'quality' => '4K', 'age' => '16+'],
        ['file' => '4.png', 'title' => 'Roho Nguvu', 'duration' => '135 min', 'genre' => 'Spirituel', 'quality' => '1080', 'age' => '16+'],
        ['file' => '3.png', 'title' => 'Majaribu', 'duration' => '110 min', 'genre' => 'Thriller', 'quality' => '720P', 'age' => '18+'],
        ['file' => '5.png', 'title' => 'Douleur', 'duration' => '125 min', 'genre' => 'Policier', 'quality' => '1080', 'age' => '16+'],
        ['file' => '7.png', 'title' => 'Bhurende', 'duration' => '140 min', 'genre' => 'Drame', 'quality' => '1080', 'age' => '18+'],
        ['file' => '12.png', 'title' => 'Bhurende', 'duration' => '115 min', 'genre' => 'Action', 'quality' => '1080', 'age' => '16+'],
    ];
    $staticSeries = [
        ['file' => '17.png', 'title' => 'Majaribu - Saison 2'],
        ['file' => '15.png', 'title' => 'Majaribu'],
        ['file' => '16.png', 'title' => 'Majaribu'],
    ];
    $staticAutres = [
        ['file' => '14.png', 'title' => 'Mzigo Kwa Mama'],
        ['file' => '2.png', 'title' => 'Majaribu'],
        ['file' => '1.png', 'title' => 'Majaribu'],
        ['file' => '6.png', 'title' => 'Douleur'],
        ['file' => '8.png', 'title' => 'Impuissant'],
        ['file' => '9.png', 'title' => 'Scène de production'],
    ];
?>

<style>
/* ==========================================
   CARTES VIDÉO
   ========================================== */
.media-card-pro {
    background: #1a1a1a; border-radius: 10px; overflow: hidden;
    transition: transform 0.25s ease, box-shadow 0.25s ease;
    cursor: pointer; height: 100%; display: flex; flex-direction: column;
}
.media-card-pro:hover { transform: translateY(-6px); box-shadow: 0 12px 30px rgba(0,0,0,0.6); }
.media-card-pro .thumb-wrapper { position: relative; width: 100%; aspect-ratio: 16 / 9; overflow: hidden; background: #000; }
.media-card-pro .thumb-wrapper img { width: 100%; height: 100%; object-fit: cover; transition: transform 0.4s ease; }
.media-card-pro:hover .thumb-wrapper img { transform: scale(1.08); }
.media-card-pro .badge-cat {
    position: absolute; top: 10px; left: 10px; background: #e90101; color: #fff;
    padding: 3px 10px; border-radius: 4px; font-size: 11px; font-weight: 700; text-transform: uppercase;
}
.media-card-pro .badge-dur {
    position: absolute; bottom: 10px; right: 10px; background: rgba(0,0,0,0.85); color: #fff;
    padding: 2px 8px; border-radius: 4px; font-size: 12px; font-weight: 600;
}
.media-card-pro .play-overlay {
    position: absolute; inset: 0; display: flex; align-items: center; justify-content: center;
    background: rgba(0,0,0,0.35); opacity: 0; transition: opacity 0.25s;
}
.media-card-pro:hover .play-overlay { opacity: 1; }
.media-card-pro .play-overlay i { font-size: 48px; color: #fff; text-shadow: 0 2px 10px rgba(0,0,0,0.8); }
.media-card-pro .info-body { padding: 12px 14px; flex: 1; display: flex; flex-direction: column; }
.media-card-pro .info-title {
    color: #fff; font-size: 15px; font-weight: 600; margin: 0 0 6px; line-height: 1.35;
    display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
}
.media-card-pro .info-meta {
    color: #999; font-size: 12px; margin: 0; display: flex; align-items: center; gap: 6px; flex-wrap: wrap;
}
.media-card-pro .info-meta i { font-size: 11px; }
.media-card-pro .info-author { color: #ccc; font-weight: 500; }
.media-card-pro .dot-sep { color: #555; }

/* ==========================================
   LECTEUR EN OVERLAY
   ========================================== */
.cinema-player {
    display: none; position: fixed; inset: 0; z-index: 99999;
    justify-content: center; align-items: center; padding: 20px;
}
.cinema-player.active { display: flex; }

.cinema-overlay {
    position: absolute; inset: 0; background: rgba(0, 0, 0, 0.88);
    backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); cursor: pointer;
}

.cinema-player-inner {
    position: relative; z-index: 2; width: 100%;
    max-width: 900px; max-height: 90vh;
    display: flex; flex-direction: column;
    animation: modalPop 0.35s cubic-bezier(0.175, 0.885, 0.32, 1.275);
}
@keyframes modalPop {
    from { opacity: 0; transform: scale(0.92); }
    to   { opacity: 1; transform: scale(1); }
}

.cinema-video-wrapper {
    position: relative; width: 100%; aspect-ratio: 16 / 9; max-height: 70vh;
    background: #000; border-radius: 12px; overflow: hidden;
    box-shadow: 0 25px 70px rgba(0,0,0,0.9);
}
.cinema-video-wrapper video {
    width: 100%; height: 100%; display: block; outline: none; object-fit: contain;
}

.cinema-close {
    position: absolute; top: 15px; right: 15px; width: 42px; height: 42px;
    background: rgba(0, 0, 0, 0.6); border: 1px solid rgba(255, 255, 255, 0.3); border-radius: 50%;
    color: #fff; font-size: 20px; cursor: pointer; transition: all 0.3s ease;
    display: flex; align-items: center; justify-content: center; z-index: 20;
    backdrop-filter: blur(8px);
}
.cinema-close:hover { background: #e90101; border-color: #e90101; transform: rotate(90deg) scale(1.12); }

.cinema-info {
    margin-top: 14px; color: #fff; display: flex; justify-content: space-between; align-items: flex-start; padding: 0 4px;
}
.cinema-info h3 { font-size: 19px; font-weight: 700; margin: 0 0 4px; }
.cinema-info p { color: #aaa; font-size: 13px; margin: 0; }

/* ==========================================
   PAYWALL À 15 SECONDES
   ========================================== */
.cinema-paywall {
    position: absolute; inset: 0; background: rgba(10, 10, 10, 0.85);
    backdrop-filter: blur(15px); -webkit-backdrop-filter: blur(15px);
    display: none; flex-direction: column; align-items: center; justify-content: center;
    text-align: center; padding: 30px; z-index: 15; animation: fadeIn 0.4s ease-out;
}
.cinema-paywall.active { display: flex; }
.cinema-paywall i.lock-icon {
    font-size: 60px; color: #e90101; margin-bottom: 20px; text-shadow: 0 0 30px rgba(233, 1, 1, 0.5);
}
.cinema-paywall h2 { color: #fff; font-size: 26px; font-weight: 700; margin: 0 0 12px; }
.cinema-paywall p { color: #bbb; font-size: 15px; margin: 0 0 26px; max-width: 460px; line-height: 1.5; }
.cinema-paywall .btn-login {
    background: #e90101; color: #fff; padding: 14px 40px; border-radius: 30px;
    text-decoration: none; font-weight: 600; font-size: 16px; transition: all 0.25s;
    display: inline-flex; align-items: center; gap: 10px; box-shadow: 0 8px 25px rgba(233, 1, 1, 0.35);
}
.cinema-paywall .btn-login:hover {
    background: #ff2d3a; transform: translateY(-2px); box-shadow: 0 12px 35px rgba(233, 1, 1, 0.55);
}
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

body.no-scroll { overflow: hidden !important; }
</style>

<section class="catalog-intro">
    <div class="container">
        <p class="catalog-kicker">KAZURI · <?= t('CINÉMA AFRICAIN') ?></p>
        <h1><?= t('Des histoires d’ici, à découvrir.') ?></h1>
        <p><?= t('Films et productions locales réunis dans une seule collection.') ?></p>
    </div>
</section>

<!-- ============================================
     LECTEUR EN OVERLAY
     ============================================ -->
<div id="cinemaPlayer" class="cinema-player">
    <div class="cinema-overlay" onclick="closeCinemaPlayer()"></div>
    <div class="cinema-player-inner">
        <div class="cinema-video-wrapper">
            <button class="cinema-close" onclick="closeCinemaPlayer()" title="Fermer">
                <i class="lni lni-close"></i>
            </button>
            <video id="cinemaVideo" src="" controls playsinline></video>
            
            <div id="cinemaPaywall" class="cinema-paywall">
                <i class="lni lni-lock-alt lock-icon"></i>
                <h2>Connectez-vous pour découvrir plus</h2>
                <p>Créez un compte gratuit pour continuer à regarder cette vidéo en illimité.</p>
                <a href="index.php?p=account" class="btn-login">
                    <i class="lni lni-user"></i> Se connecter
                </a>
            </div>
        </div>
        <div class="cinema-info">
            <div>
                <h3 id="cinemaTitle"></h3>
                <p id="cinemaMeta"></p>
            </div>
        </div>
    </div>
</div>

<!-- ============================================
     SECTION 1 : DERNIÈRES VIDÉOS
     ============================================ -->
<section id="films" class="content-section section-featured">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="section-title d-flex justify-content-between align-items-center">
                    <div>
                        <h6><?= t('STREAMING AFRICAIN') ?></h6>
                        <h2><?= t('Regardez nos vidéos en ligne') ?></h2>
                    </div>
                    <a href="#productions-locales" class="see-all-link">
                        <?= t('Tout voir') ?> <i class="lni lni-chevron-right"></i>
                    </a>
                </div>
            </div>

            <?php if (!empty($p)): ?>
                <?php foreach (array_slice($p, 0, 4) as $v): ?>
                <div class="col-lg-3 col-md-4 col-sm-6 col-6 mb-4">
                    <div class="media-card-pro" onclick='playCinema(<?= json_encode([
                        'url' => resolveMediaPath($v['url']),
                        'title' => $v['title'],
                        'meta' => $v['category'] . ' · ' . $v['author'] . ' · ' . formatDuration($v['duration']) . ' · ' . formatViews($v['views']) . ' vues'
                    ]) ?>)'>
                        <div class="thumb-wrapper">
                            <img src="<?= htmlspecialchars(resolveMediaPath($v['thumb']) ?: '../assets/images/13.png') ?>" 
                                 onerror="this.src='../assets/images/13.png'" alt="<?= htmlspecialchars($v['title']) ?>" loading="lazy">
                            <span class="badge-cat"><?= htmlspecialchars($v['category']) ?></span>
                            <span class="badge-dur"><?= formatDuration($v['duration']) ?></span>
                            <div class="play-overlay"><i class="lni lni-play"></i></div>
                        </div>
                        <div class="info-body">
                            <h3 class="info-title"><?= htmlspecialchars($v['title']) ?></h3>
                            <p class="info-meta">
                                <span class="info-author"><?= htmlspecialchars($v['author']) ?></span>
                                <span class="dot-sep">•</span>
                                <i class="lni lni-eye"></i> <?= formatViews($v['views']) ?>
                                <span class="dot-sep">•</span>
                                <?= timeAgo($v['date']) ?>
                            </p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12"><div class="media-empty"><i class="lni lni-film"></i><h4><?= t('Aucun média disponible') ?></h4></div></div>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ============================================
     SECTION 2 : POURQUOI KAZURI
     ============================================ -->
<section class="content-section no-top-spacing">
    <div class="container">
        <div class="row align-items-center no-gutters">
            <div class="col-lg-6">
                <figure class="side-image full-left"><img src="../assets/images/side-image01.png" alt="Image"></figure>
            </div>
            <div class="col-lg-6">
                <div class="side-content right">
                    <h2><?= t('La meilleure expérience de streaming africain.') ?></h2>
                    <ul class="icon-list">
                        <li><figure><img src="../assets/images/icon01.png" alt="Image"></figure><div class="content"><h4><?= t('Accessible partout') ?></h4><p><?= t('Gardez accès à vos films et séries préférés où que vous soyez, même en déplacement.') ?></p></div></li>
                        <li><figure><img src="../assets/images/icon02.png" alt="Image"></figure><div class="content"><h4><?= t('Streaming sans interruption') ?></h4><p><?= t('Profitez d’une lecture fluide grâce à notre réseau ultra-rapide optimisé pour l’Afrique.') ?></p></div></li>
                        <li><figure><img src="../assets/images/icon03.png" alt="Image"></figure><div class="content"><h4><?= t('Sécurisé en permanence') ?></h4><p><?= t('Accédez à vos contenus en toute sécurité, même sur les réseaux publics.') ?></p></div></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================
     SECTION 3 : FILMS GOMATRACIENS
     ============================================ -->
<section id="productions-locales" class="content-section section-goma" data-background="#111111">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="section-title text-center light">
                    <h6><?= t('FILMS GOMATRACIENS') ?></h6>
                    <h2><?= t('Nos productions locales') ?></h2>
                </div>
            </div>

            <?php if (!empty($films)): ?>
                <?php foreach ($films as $v): ?>
                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 col-12 mb-4">
                    <div class="media-card-pro" onclick='playCinema(<?= json_encode([
                        'url' => resolveMediaPath($v['url']),
                        'title' => $v['title'],
                        'meta' => $v['category'] . ' · ' . $v['author'] . ' · ' . formatDuration($v['duration']) . ' · ' . formatViews($v['views']) . ' vues'
                    ]) ?>)'>
                        <div class="thumb-wrapper">
                            <img src="<?= htmlspecialchars(resolveMediaPath($v['thumb']) ?: '../assets/images/13.png') ?>" 
                                 onerror="this.src='../assets/images/13.png'" alt="<?= htmlspecialchars($v['title']) ?>" loading="lazy">
                            <span class="badge-cat"><?= htmlspecialchars($v['category']) ?></span>
                            <span class="badge-dur"><?= formatDuration($v['duration']) ?></span>
                            <div class="play-overlay"><i class="lni lni-play"></i></div>
                        </div>
                        <div class="info-body">
                            <h3 class="info-title"><?= htmlspecialchars($v['title']) ?></h3>
                            <p class="info-meta">
                                <span class="info-author"><?= htmlspecialchars($v['author']) ?></span>
                                <span class="dot-sep">•</span>
                                <i class="lni lni-eye"></i> <?= formatViews($v['views']) ?>
                                <span class="dot-sep">•</span>
                                <?= timeAgo($v['date']) ?>
                            </p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <?php foreach ($staticFilms as $sf): ?>
                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 col-12 mb-4">
                    <div class="media-card-pro">
                        <div class="thumb-wrapper">
                            <img src="../assets/images/<?= $sf['file'] ?>" alt="Affiche <?= htmlspecialchars($sf['title']) ?>" loading="lazy">
                            <span class="badge-cat"><?= t($sf['genre']) ?></span>
                            <span class="badge-dur"><?= $sf['duration'] ?></span>
                        </div>
                        <div class="info-body">
                            <h3 class="info-title"><?= htmlspecialchars($sf['title']) ?></h3>
                            <p class="info-meta">
                                <span class="info-author">Kazuri Studio</span>
                                <span class="dot-sep">•</span>
                                <?= $sf['quality'] ?> HD
                                <span class="dot-sep">•</span>
                                <?= $sf['age'] ?>
                            </p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>

            <div class="col-12 text-center"> <a href="#films" class="custom-button"><?= t('VOIR TOUS LES FILMS') ?></a> </div>
        </div>
    </div>
</section>

<!-- ============================================
     SECTION 4 : SÉRIES
     ============================================ -->
<section id="series" class="content-section section-featured">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="section-title">
                    <h6><?= t('SÉRIES') ?></h6>
                    <h2><?= t('Séries à découvrir') ?></h2>
                </div>
            </div>

            <?php if (!empty($series)): ?>
                <?php foreach ($series as $v): ?>
                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 col-12 mb-4">
                    <div class="media-card-pro" onclick='playCinema(<?= json_encode([
                        'url' => resolveMediaPath($v['url']),
                        'title' => $v['title'],
                        'meta' => $v['category'] . ' · ' . $v['author'] . ' · ' . formatDuration($v['duration']) . ' · ' . formatViews($v['views']) . ' vues'
                    ]) ?>)'>
                        <div class="thumb-wrapper">
                            <img src="<?= htmlspecialchars(resolveMediaPath($v['thumb']) ?: '../assets/images/4.png') ?>" 
                                 onerror="this.src='../assets/images/4.png'" alt="<?= htmlspecialchars($v['title']) ?>" loading="lazy">
                            <span class="badge-cat"><?= htmlspecialchars($v['category']) ?></span>
                            <span class="badge-dur"><?= formatDuration($v['duration']) ?></span>
                            <div class="play-overlay"><i class="lni lni-play"></i></div>
                        </div>
                        <div class="info-body">
                            <h3 class="info-title"><?= htmlspecialchars($v['title']) ?></h3>
                            <p class="info-meta">
                                <span class="info-author"><?= htmlspecialchars($v['author']) ?></span>
                                <span class="dot-sep">•</span>
                                <i class="lni lni-eye"></i> <?= formatViews($v['views']) ?>
                                <span class="dot-sep">•</span>
                                <?= timeAgo($v['date']) ?>
                            </p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <?php foreach ($staticSeries as $sf): ?>
                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 col-12 mb-4">
                    <div class="media-card-pro">
                        <div class="thumb-wrapper">
                            <img src="../assets/images/<?= $sf['file'] ?>" alt="<?= htmlspecialchars($sf['title']) ?>" loading="lazy">
                            <span class="badge-cat">Séries</span>
                        </div>
                        <div class="info-body">
                            <h3 class="info-title"><?= htmlspecialchars($sf['title']) ?></h3>
                            <p class="info-meta"><span class="info-author">Kazuri Studio</span></p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ============================================
     SECTION 5 : AUTRES
     ============================================ -->
<section id="autres" class="content-section section-featured">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="section-title">
                    <h6><?= t('À EXPLORER') ?></h6>
                    <h2><?= t('Autres vidéos') ?></h2>
                </div>
            </div>

            <?php if (!empty($autres)): ?>
                <?php foreach ($autres as $v): ?>
                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 col-12 mb-4">
                    <div class="media-card-pro" onclick='playCinema(<?= json_encode([
                        'url' => resolveMediaPath($v['url']),
                        'title' => $v['title'],
                        'meta' => $v['category'] . ' · ' . $v['author'] . ' · ' . formatDuration($v['duration']) . ' · ' . formatViews($v['views']) . ' vues'
                    ]) ?>)'>
                        <div class="thumb-wrapper">
                            <img src="<?= htmlspecialchars(resolveMediaPath($v['thumb']) ?: '../assets/images/3.png') ?>" 
                                 onerror="this.src='../assets/images/3.png'" alt="<?= htmlspecialchars($v['title']) ?>" loading="lazy">
                            <span class="badge-cat"><?= htmlspecialchars($v['category']) ?></span>
                            <span class="badge-dur"><?= formatDuration($v['duration']) ?></span>
                            <div class="play-overlay"><i class="lni lni-play"></i></div>
                        </div>
                        <div class="info-body">
                            <h3 class="info-title"><?= htmlspecialchars($v['title']) ?></h3>
                            <p class="info-meta">
                                <span class="info-author"><?= htmlspecialchars($v['author']) ?></span>
                                <span class="dot-sep">•</span>
                                <i class="lni lni-eye"></i> <?= formatViews($v['views']) ?>
                                <span class="dot-sep">•</span>
                                <?= timeAgo($v['date']) ?>
                            </p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <?php foreach ($staticAutres as $sa): ?>
                <div class="col-xl-3 col-lg-4 col-md-6 col-sm-6 col-12 mb-4">
                    <div class="media-card-pro">
                        <div class="thumb-wrapper">
                            <img src="../assets/images/<?= htmlspecialchars($sa['file']) ?>" alt="<?= htmlspecialchars($sa['title']) ?>" loading="lazy">
                            <span class="badge-cat">Autre</span>
                        </div>
                        <div class="info-body">
                            <h3 class="info-title"><?= htmlspecialchars($sa['title']) ?></h3>
                            <p class="info-meta"><span class="info-author">Kazuri Studio</span></p>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ============================================
     SECTION 6 : TÉMOIGNAGE
     ============================================ -->
<section class="content-section" data-background="../assets/images/section-pattern01.png">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <div class="testimonial">
                    <h2><?= t('Approuvé par les experts et les vrais utilisateurs') ?></h2>
                    <blockquote><?= t('« Kazuri est bien plus qu’une plateforme de streaming. C’est une fenêtre ouverte sur la richesse du cinéma africain, avec une attention particulière aux productions de Goma. »') ?></blockquote>
                    <figure> <img src="../assets/images/avatar01.jpg" alt="Image"> <img src="../assets/images/brand01.jpg" alt="Image"> </figure>
                    <h6>Kazuri Team <br><small><?= t('Streaming africain') ?></small></h6>
                </div>
            </div>
            <div class="col-lg-5">
                <figure class="side-image"> <img src="../assets/images/side-image02.png" alt="Image"> </figure>
            </div>
        </div>
    </div>
</section>

<!-- ============================================
     SECTION 7 : ABONNEMENTS
     ============================================ -->
<section class="content-section">
    <div class="video-bg"><video src="../assets/videos/video01.mp4" autoplay muted playsinline loop></video></div>
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="section-title text-center light">
                    <h6><?= t('CHOISISSEZ VOTRE ABONNEMENT') ?></h6>
                    <h2><?= t('Prêt à commencer ?') ?></h2>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-4">
                <div class="price-box">
                    <figure class="icon"><img src="../assets/images/icon04.png" alt="Image"></figure>
                    <div class="months"><?= t('Plan 1 mois') ?></div>
                    <div class="price"><span>$</span> <b>4.99</b> <small><?= t('par mois') ?></small> </div>
                    <a href="#"><?= t('S’ABONNER') ?></a>
                    <div class="guarantee"><i class="lni lni-protection"></i> <?= t('Garantie satisfait ou remboursé 30 jours') ?></div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="price-box">
                    <figure class="icon"><img src="../assets/images/icon05.png" alt="Image"></figure>
                    <div class="months"><?= t('Plan 12 mois') ?></div>
                    <div class="price"><span>$</span> <b>2.99</b> <small><?= t('par mois') ?></small> </div>
                    <div class="save"><?= t('Économisez 40%') ?></div>
                    <div class="note"><u>$59.88</u> $35.88 <?= t('pour la première année') ?></div>
                    <a href="#"><?= t('S’ABONNER') ?></a>
                    <div class="guarantee"><i class="lni lni-protection"></i> <?= t('Garantie satisfait ou remboursé 30 jours') ?></div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="price-box">
                    <figure class="icon"><img src="../assets/images/icon06.png" alt="Image"></figure>
                    <div class="months"><?= t('Plan 6 mois') ?></div>
                    <div class="price"><span>$</span> <b>3.99</b> <small><?= t('par mois') ?></small> </div>
                    <a href="#"><?= t('S’ABONNER') ?></a>
                    <div class="guarantee"><i class="lni lni-protection"></i> <?= t('Garantie satisfait ou remboursé 30 jours') ?></div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================
     SECTION 8 : GET STARTED
     ============================================ -->
<section class="content-section no-spacing" data-background="#111111">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="get-started-box">
                    <h2><?= t('Commencez vos 30 premiers jours.') ?></h2>
                    <p><?= t('Prêt à regarder ? Entrez votre email pour créer ou redémarrer votre abonnement.') ?></p>
                    <form>
                        <input type="email" placeholder="<?= t('Tapez votre adresse e-mail') ?>">
                        <input type="submit" value="<?= t('COMMENCER') ?>">
                    </form>
                </div>
            </div>
        </div>
    </div>
</section>

<script>
// ✅ VARIABLES
let paywallTimer = null;
let paywallShown = false;
const PAYWALL_TIME = 15; // 15 secondes

function playCinema(data) {
    if (!data.url || data.url === '') {
        alert("Cette vidéo est une démonstration statique (pas de fichier vidéo disponible).");
        return;
    }
    
    var modal = document.getElementById('cinemaPlayer');
    var video = document.getElementById('cinemaVideo');
    var title = document.getElementById('cinemaTitle');
    var meta = document.getElementById('cinemaMeta');
    var paywall = document.getElementById('cinemaPaywall');
    
    // Reset
    clearTimeout(paywallTimer);
    paywallShown = false;
    paywall.classList.remove('active');
    video.controls = true;
    
    video.src = data.url;
    title.textContent = data.title;
    meta.textContent = data.meta;
    modal.classList.add('active');
    
    // Bloquer le scroll
    document.body.classList.add('no-scroll');
    
    // Lancer
    video.play().catch(function(err) {
        console.log("Lecture auto bloquée :", err);
    });

    // Surveillance 15s
    video.ontimeupdate = function() {
        if (!paywallShown && video.currentTime >= PAYWALL_TIME) {
            paywallShown = true;
            video.pause();
            video.controls = false;
            paywall.classList.add('active');
        }
    };
}

function closeCinemaPlayer() {
    var modal = document.getElementById('cinemaPlayer');
    var video = document.getElementById('cinemaVideo');
    var paywall = document.getElementById('cinemaPaywall');
    
    clearTimeout(paywallTimer);
    paywallShown = false;
    
    video.pause();
    video.src = '';
    video.ontimeupdate = null;
    modal.classList.remove('active');
    paywall.classList.remove('active');
    
    // Libérer le scroll
    document.body.classList.remove('no-scroll');
}

// Fermer avec Échap
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        var modal = document.getElementById('cinemaPlayer');
        if (modal.classList.contains('active')) {
            closeCinemaPlayer();
        }
    }
});
</script>