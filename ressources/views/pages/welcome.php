<?php
    $p = \app\App::getInstance()->getTable('media')->allMedias();
    $allLocalPhotos = [
        ['file' => '13.png', 'title' => 'Impuissant'],
        ['file' => '4.png', 'title' => 'Roho Nguvu'],
        ['file' => '3.png', 'title' => 'Majaribu'],
        ['file' => '5.png', 'title' => 'Douleur'],
        ['file' => '7.png', 'title' => 'Bhurende'],
        ['file' => '12.png', 'title' => 'Bhurende'],
        ['file' => '14.png', 'title' => 'Mzigo Kwa Mama'],
        ['file' => '15.png', 'title' => 'Majaribu'],
        ['file' => '16.png', 'title' => 'Majaribu'],
        ['file' => '2.png', 'title' => 'Majaribu'],
        ['file' => '1.png', 'title' => 'Majaribu'],
        ['file' => '6.png', 'title' => 'Douleur'],
        ['file' => '8.png', 'title' => 'Impuissant', 'mediaId' => 10],
        ['file' => '9.png', 'title' => 'Scène de production'],
        ['file' => '10.png', 'title' => 'Production locale'],
        ['file' => '11.jpeg', 'title' => 'Production locale'],
        ['file' => '17.png', 'title' => 'Majaribu - Saison 2', 'mediaId' => 12],
        ['file' => 'release_img/kopmago1.PNG', 'title' => 'Kopmago', 'wide' => true, 'mediaId' => 6],
        ['file' => 'release_img/kopmago2.PNG', 'title' => 'Kopmago', 'wide' => true, 'mediaId' => 6],
    ];
    $extraLocalPhotos = array_slice($allLocalPhotos, 12);
    $seriesPosterFallbacks = [
        ['file' => '17.png', 'title' => 'Majaribu - Saison 2', 'mediaId' => 12],
        ['file' => '15.png', 'title' => 'Majaribu', 'mediaId' => 12],
        ['file' => '16.png', 'title' => 'Majaribu', 'mediaId' => 12],
    ];
?>

<section class="catalog-intro">
    <div class="container">
        <p class="catalog-kicker">KAZURI · <?= t('CINÉMA AFRICAIN') ?></p>
        <h1><?= t('Des histoires d’ici, à découvrir.') ?></h1>
        <p><?= t('Films et productions locales réunis dans une seule collection.') ?></p>
    </div>
</section>

<?php ob_start(); ?>
<!-- ============================================
     SECTION 1 : MÉDIAS DYNAMIQUES (BDD)
     ============================================ -->
<section id="films" class="content-section section-featured">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="section-title d-flex justify-content-between align-items-center">
                    <div>
                        <h6><?= t('STREAMING AFRICAIN') ?></h6>
                        <h2><?= t('Regardez nos films en ligne') ?></h2>
                    </div>
                    <a href="#productions-locales" class="see-all-link">
                        <?= t('Tout voir') ?> <i class="lni lni-chevron-right"></i>
                    </a>
                </div>
                <!-- end section-title -->
            </div>
            <!-- end col-12 -->

            <?php if (!empty($p)): ?>
                <?php foreach ($p as $v): ?>
                <div class="col-lg-3 col-md-4 col-sm-6 col-6">
                    <div class="media-card">
                        <a href="index.php?p=detail&id_media=<?=$v[0]?>" class="media-card-link">

                            <!-- IMAGE POSTER -->
                            <div class="media-card-image">
                                <img src="../admin/endpoint/medias/<?=$v[7]?>" alt="<?=$v[1]?>" loading="lazy" onerror="this.onerror=null;this.src='../assets/images/movie-poster.jpg';">

                                <!-- BADGE CATÉGORIE -->
                                <div class="media-card-badge">
                                    <?= htmlspecialchars(t((string)$v[8]), ENT_QUOTES, 'UTF-8') ?>
                                </div>

                                <!-- QUALITÉ / DURÉE -->
                                <div class="media-card-quality">
                                    <?= !empty($v[5]) ? $v[5] : 'HD' ?>
                                </div>

                                <!-- OVERLAY AU SURVOL -->
                                <div class="media-card-overlay">
                                    <div class="media-card-play">
                                        <i class="lni lni-play"></i>
                                    </div>
                                </div>
                            </div>

                            <!-- INFOS -->
                            <div class="media-card-info">
                                <h3 class="media-card-title"><?=$v[1]?></h3>
                                <div class="media-card-meta">
                                    <span class="media-card-cat"><?=$v[8]?></span>
                                    <span class="media-card-dot">•</span>
                                    <span class="media-card-date">
                                        <?= !empty($v[4]) ? htmlspecialchars((string)$v[4], ENT_QUOTES, 'UTF-8') : t('Nouveau') ?>
                                    </span>
                                </div>
                            </div>

                        </a>
                    </div>
                    <!-- end media-card -->
                </div>
                <!-- end col -->
                <?php endforeach ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="media-empty">
                        <i class="lni lni-film"></i>
                                        <h4><?= t('Aucun média disponible') ?></h4>
                                        <p><?= t('Revenez bientôt pour découvrir nos nouveaux contenus.') ?></p>
                    </div>
                </div>
            <?php endif; ?>

        </div>
        <!-- end row -->
    </div>
    <!-- end container -->
</section>
<!-- end section-featured -->
<!-- end content-section -->
<?php $streamingSection = ob_get_clean(); ?>

<?php
    $categoryOf = function ($media) {
        return strtolower(trim((string)($media[8] ?? '')));
    };
    $byCategories = function ($categories) use ($p, $categoryOf) {
        return array_values(array_filter($p, function ($media) use ($categories, $categoryOf) {
            return in_array($categoryOf($media), $categories, true);
        }));
    };

    $latestMedia = $p;
    usort($latestMedia, function ($left, $right) {
        $leftDate = strtotime((string)($left[4] ?? '')) ?: 0;
        $rightDate = strtotime((string)($right[4] ?? '')) ?: 0;
        return $rightDate <=> $leftDate;
    });

    $seriesMedia = $byCategories(['tv shows', 'series', 'séries']);
    $filmMedia = $byCategories(['movies', 'movie', 'films', 'film']);
    $youthMedia = $byCategories(['cartoon', 'jeunesse', 'youth', 'kids']);
    $shopMedia = $byCategories(['boutiques', 'boutique', 'stores', 'store']);
    $shortMedia = $byCategories(['shorts', 'short']);
    $knownCategories = ['tv shows', 'series', 'séries', 'movies', 'movie', 'films', 'film', 'cartoon', 'jeunesse', 'youth', 'kids', 'boutiques', 'boutique', 'stores', 'store', 'shorts', 'short'];
    $otherMedia = array_values(array_filter($p, function ($media) use ($categoryOf, $knownCategories) {
        return !in_array($categoryOf($media), $knownCategories, true);
    }));

    $mediaSections = [
        ['id' => 'series', 'label' => 'SÉRIES', 'title' => 'Séries à découvrir', 'items' => $seriesMedia, 'fallbackPosters' => $seriesPosterFallbacks],
        ['id' => 'films', 'label' => 'FILMS', 'title' => 'Films en streaming', 'items' => $filmMedia],
        ['id' => 'jeunesse', 'label' => 'JEUNESSE', 'title' => 'À regarder en famille', 'items' => $youthMedia, 'showPlaceholder' => true],
        ['id' => 'nouveautes', 'label' => 'RÉCEMMENT AJOUTÉS', 'title' => 'Nouveautés', 'items' => $latestMedia],
        ['id' => 'boutiques', 'label' => 'BOUTIQUES', 'title' => 'Boutiques', 'items' => $shopMedia, 'showPlaceholder' => true],
        ['id' => 'shorts', 'label' => 'FORMATS COURTS', 'title' => 'Shorts', 'items' => $shortMedia, 'showPlaceholder' => true],
        ['id' => 'autres', 'label' => 'À EXPLORER', 'title' => 'Autres vidéos', 'items' => $otherMedia],
    ];

    $renderMediaSection = function ($section) {
        ob_start();
        ?>
        <section id="<?= htmlspecialchars($section['id'], ENT_QUOTES, 'UTF-8') ?>" class="content-section section-featured">
            <div class="container">
                <div class="row">
                    <div class="col-12">
                        <div class="section-title">
                            <h6><?= htmlspecialchars(t($section['label']), ENT_QUOTES, 'UTF-8') ?></h6>
                            <h2><?= htmlspecialchars(t($section['title']), ENT_QUOTES, 'UTF-8') ?></h2>
                        </div>
                    </div>
                    <?php if (!empty($section['items'])): ?>
                        <?php foreach ($section['items'] as $media): ?>
                        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-6">
                            <div class="media-card">
                                <a href="index.php?p=detail&id_media=<?= rawurlencode((string)$media[0]) ?>" class="media-card-link">
                                    <div class="media-card-image">
                                        <img src="../admin/endpoint/medias/<?= htmlspecialchars((string)($media[7] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
                                            alt="<?= htmlspecialchars((string)($media[1] ?? 'Vidéo'), ENT_QUOTES, 'UTF-8') ?>"
                                            loading="lazy"
                                            onerror="this.onerror=null;this.src='../assets/images/movie-poster.jpg';">
                                        <div class="media-card-badge"><?= htmlspecialchars(t((string)($media[8] ?? 'Kazuri')), ENT_QUOTES, 'UTF-8') ?></div>
                                        <div class="media-card-quality"><?= htmlspecialchars((string)($media[5] ?? 'HD'), ENT_QUOTES, 'UTF-8') ?></div>
                                        <div class="media-card-overlay"><div class="media-card-play"><i class="lni lni-play"></i></div></div>
                                    </div>
                                    <div class="media-card-info">
                                        <h3 class="media-card-title"><?= htmlspecialchars((string)($media[1] ?? 'Vidéo'), ENT_QUOTES, 'UTF-8') ?></h3>
                                        <div class="media-card-meta">
                                            <span class="media-card-cat"><?= htmlspecialchars(t((string)($media[8] ?? 'Kazuri')), ENT_QUOTES, 'UTF-8') ?></span>
                                            <span class="media-card-dot">•</span>
                                            <span class="media-card-date"><?= htmlspecialchars((string)($media[4] ?? t('Nouveau')), ENT_QUOTES, 'UTF-8') ?></span>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php elseif (!empty($section['fallbackPosters'])): ?>
                        <?php foreach ($section['fallbackPosters'] as $poster): ?>
                        <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-6">
                            <div class="media-card">
                                <a href="index.php?p=detail&id_media=<?= rawurlencode((string)$poster['mediaId']) ?>" class="media-card-link">
                                    <div class="media-card-image">
                                        <img src="../assets/images/<?= htmlspecialchars($poster['file'], ENT_QUOTES, 'UTF-8') ?>"
                                            alt="<?= htmlspecialchars($poster['title'], ENT_QUOTES, 'UTF-8') ?>" loading="lazy">
                                        <div class="media-card-badge"><?= t('SÉRIES') ?></div>
                                        <div class="media-card-overlay"><div class="media-card-play"><i class="lni lni-play"></i></div></div>
                                    </div>
                                    <div class="media-card-info">
                                        <h3 class="media-card-title"><?= htmlspecialchars($poster['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                                        <div class="media-card-meta"><span class="media-card-cat"><?= t('Séries') ?></span></div>
                                    </div>
                                </a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php elseif (!empty($section['showPlaceholder'])): ?>
                    <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-6">
                        <div class="media-card media-card-placeholder">
                            <div class="media-card-image placeholder-image"><i class="lni lni-film" aria-hidden="true"></i></div>
                            <div class="media-card-info">
                                <h3 class="media-card-title"><?= t('Bientôt disponible') ?></h3>
                                <p><?= t('Ajoutez des vidéos à cette catégorie depuis l’administration pour les afficher ici.') ?></p>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                    <div class="col-12">
                        <div class="media-empty">
                            <i class="lni lni-film"></i>
                            <h4><?= t('Aucune vidéo dans cette rubrique') ?></h4>
                            <p><?= t('Les vidéos ajoutées à cette catégorie apparaîtront ici.') ?></p>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </section>
        <?php
        return ob_get_clean();
    };

    $streamingSection = '';
    foreach ($mediaSections as $mediaSection) {
        $streamingSection .= $renderMediaSection($mediaSection);
    }
?>

<!-- ============================================
     SECTION 2 : POURQUOI KAZURI (image + icônes)
     ============================================ -->
<section class="content-section no-top-spacing">
    <div class="container">
        <div class="row align-items-center no-gutters">
            <div class="col-lg-6">
                <figure class="side-image full-left"> <img src="../assets/images/side-image01.png" alt="Image">
                </figure>
                <!-- end side-image -->
            </div>
            <!-- end col-6 -->
            <div class="col-lg-6">
                <div class="side-content right">
                    <h2><?= t('La meilleure expérience de streaming africain.') ?></h2>
                    <ul class="icon-list">
                        <li>
                            <figure> <img src="../assets/images/icon01.png" alt="Image"> </figure>
                            <div class="content">
                                <h4><?= t('Accessible partout') ?></h4>
                                <p><?= t('Gardez accès à vos films et séries préférés où que vous soyez, même en déplacement.') ?></p>
                            </div>
                        </li>
                        <li>
                            <figure> <img src="../assets/images/icon02.png" alt="Image"> </figure>
                            <div class="content">
                                <h4><?= t('Streaming sans interruption') ?></h4>
                                <p><?= t('Profitez d’une lecture fluide grâce à notre réseau ultra-rapide optimisé pour l’Afrique.') ?></p>
                            </div>
                        </li>
                        <li>
                            <figure> <img src="../assets/images/icon03.png" alt="Image"> </figure>
                            <div class="content">
                                <h4><?= t('Sécurisé en permanence') ?></h4>
                                <p><?= t('Accédez à vos contenus en toute sécurité, même sur les réseaux publics.') ?></p>
                            </div>
                        </li>
                    </ul>
                </div>
                <!-- end side-content -->
            </div>
            <!-- end col-6 -->
        </div>
        <!-- end row -->
    </div>
    <!-- end container -->
</section>
<!-- end content-section -->

<!-- ============================================
     SECTION 3 : FILMS GOMATRACIENS (grille 12 films)
     ============================================ -->
<section id="productions-locales" class="content-section section-goma" data-background="#111111">
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="section-title text-center light">
                    <h6><?= t('FILMS GOMATRACIENS') ?></h6>
                    <h2><?= t('Nos productions locales') ?></h2>
                </div>
                <!-- end section-title -->
            </div>
            <!-- end col-12 -->

            <!-- Film 1 : Impuissant -->
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-6">
                <div class="video-thumb light">
                    <figure class="video-image"> <a href="index.php?p=detail&amp;id_media=10" aria-label="Lire Impuissant"><img src="../assets/images/13.png" alt="Affiche du film Impuissant" loading="lazy"></a>
                        <div class="circle-rate">
                            <svg class="circle-chart" viewBox="0 0 30 30" width="100" height="100"
                                xmlns="http://www.w3.org/2000/svg">
                                <circle class="circle-chart__background" stroke="#2f3439" stroke-width="2" fill="none"
                                    cx="15" cy="15" r="14"></circle>
                                <circle class="circle-chart__circle" stroke="#4eb04b" stroke-width="2"
                                    stroke-dasharray="50,100" cx="15" cy="15" r="14"></circle>
                            </svg>
                            <b>5.4</b>
                        </div>
                        <!-- end circle-rate -->
                        <div class="hd">4K <b>UHD</b></div>
                        <!-- end hd -->
                    </figure>
                    <div class="video-content"> <small class="range">120 min,</small>
                        <ul class="tags">
                            <li><?= t('Drame') ?></li>
                        </ul>
                        <div class="age">16+</div>
                        <!-- end age -->
                        <h3 class="name"><a href="movie-single.html">Impuissant</a></h3>
                    </div>
                    <!-- end video-content -->
                </div>
                <!-- end video-thumb -->
            </div>
            <!-- end col-2 -->

            <!-- Film 2 : Roho Nguvu 2 -->
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-6">
                <div class="video-thumb light">
                    <figure class="video-image"> <a href="#"><img src="../assets/images/4.png" alt="Affiche du film Roho Nguvu" loading="lazy"></a>
                        <div class="circle-rate">
                            <svg class="circle-chart" viewBox="0 0 30 30" width="100" height="100"
                                xmlns="http://www.w3.org/2000/svg">
                                <circle class="circle-chart__background" stroke="#2f3439" stroke-width="2" fill="none"
                                    cx="15" cy="15" r="14"></circle>
                                <circle class="circle-chart__circle" stroke="#4eb04b" stroke-width="2"
                                    stroke-dasharray="50,100" cx="15" cy="15" r="14"></circle>
                            </svg>
                            <b>5.4</b>
                        </div>
                        <!-- end circle-rate -->
                        <div class="hd">1080 <b>HD</b></div>
                        <!-- end hd -->
                    </figure>
                    <div class="video-content"> <small class="range">135 min,</small>
                        <ul class="tags">
                            <li><?= t('Spirituel') ?></li>
                        </ul>
                        <div class="age">16+</div>
                        <!-- end age -->
                        <h3 class="name"><a href="movie-single.html">Roho Nguvu</a></h3>
                    </div>
                    <!-- end video-content -->
                </div>
                <!-- end video-thumb -->
            </div>
            <!-- end col-2 -->

            <!-- Film 3 : Majaribu -->
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-6">
                <div class="video-thumb light">
                    <figure class="video-image"> <a href="index.php?p=detail&amp;id_media=12" aria-label="Lire Majaribu"><img src="../assets/images/3.png" alt="Affiche du film Majaribu" loading="lazy"></a>
                        <div class="circle-rate">
                            <svg class="circle-chart" viewBox="0 0 30 30" width="100" height="100"
                                xmlns="http://www.w3.org/2000/svg">
                                <circle class="circle-chart__background" stroke="#2f3439" stroke-width="2" fill="none"
                                    cx="15" cy="15" r="14"></circle>
                                <circle class="circle-chart__circle" stroke="#4eb04b" stroke-width="2"
                                    stroke-dasharray="50,100" cx="15" cy="15" r="14"></circle>
                            </svg>
                            <b>5.4</b>
                        </div>
                        <!-- end circle-rate -->
                        <div class="hd">720P <b>HD</b></div>
                        <!-- end hd -->
                    </figure>
                    <div class="video-content"> <small class="range">110 min,</small>
                        <ul class="tags">
                            <li><?= t('Thriller') ?></li>
                        </ul>
                        <div class="age">18+</div>
                        <!-- end age -->
                        <h3 class="name"><a href="movie-single.html">Majaribu</a></h3>
                    </div>
                    <!-- end video-content -->
                </div>
                <!-- end video-thumb -->
            </div>
            <!-- end col-2 -->

            <!-- Film 4 : Douleur -->
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-6">
                <div class="video-thumb light">
                    <figure class="video-image"> <a href="index.php?p=detail&amp;id_media=17" aria-label="Lire Douleur"><img src="../assets/images/5.png" alt="Affiche de la série Douleur" loading="lazy"></a>
                        <div class="circle-rate">
                            <svg class="circle-chart" viewBox="0 0 30 30" width="100" height="100"
                                xmlns="http://www.w3.org/2000/svg">
                                <circle class="circle-chart__background" stroke="#2f3439" stroke-width="2" fill="none"
                                    cx="15" cy="15" r="14"></circle>
                                <circle class="circle-chart__circle" stroke="#4eb04b" stroke-width="2"
                                    stroke-dasharray="50,100" cx="15" cy="15" r="14"></circle>
                            </svg>
                            <b>5.4</b>
                        </div>
                        <!-- end circle-rate -->
                        <div class="hd">1080 <b>HD</b></div>
                        <!-- end hd -->
                    </figure>
                    <div class="video-content"> <small class="range">125 min,</small>
                        <ul class="tags">
                            <li><?= t('Policier') ?></li>
                        </ul>
                        <div class="age">16+</div>
                        <!-- end age -->
                        <h3 class="name"><a href="movie-single.html">Douleur</a></h3>
                    </div>
                    <!-- end video-content -->
                </div>
                <!-- end video-thumb -->
            </div>
            <!-- end col-2 -->

            <!-- Film 5 : Le Prix du Sang -->
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-6">
                <div class="video-thumb light">
                    <figure class="video-image"> <a href="#"><img src="../assets/images/7.png" alt="Affiche du film Bhurende" loading="lazy"></a>
                        <div class="circle-rate">
                            <svg class="circle-chart" viewBox="0 0 30 30" width="100" height="100"
                                xmlns="http://www.w3.org/2000/svg">
                                <circle class="circle-chart__background" stroke="#2f3439" stroke-width="2" fill="none"
                                    cx="15" cy="15" r="14"></circle>
                                <circle class="circle-chart__circle" stroke="#4eb04b" stroke-width="2"
                                    stroke-dasharray="50,100" cx="15" cy="15" r="14"></circle>
                            </svg>
                            <b>5.4</b>
                        </div>
                        <!-- end circle-rate -->
                        <div class="hd">1080 <b>HD</b></div>
                        <!-- end hd -->
                    </figure>
                    <div class="video-content"> <small class="range">140 min,</small>
                        <ul class="tags">
                            <li><?= t('Drame') ?></li>
                        </ul>
                        <div class="age">18+</div>
                        <!-- end age -->
                        <h3 class="name"><a href="movie-single.html">Bhurende</a></h3>
                    </div>
                    <!-- end video-content -->
                </div>
                <!-- end video-thumb -->
            </div>
            <!-- end col-2 -->

            <!-- Film 6 : Kinshasa Connexion -->
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-6">
                <div class="video-thumb light">
                    <figure class="video-image"> <a href="#"><img src="../assets/images/12.png" alt="Affiche du film Bhurende" loading="lazy"></a>
                        <div class="circle-rate">
                            <svg class="circle-chart" viewBox="0 0 30 30" width="100" height="100"
                                xmlns="http://www.w3.org/2000/svg">
                                <circle class="circle-chart__background" stroke="#2f3439" stroke-width="2" fill="none"
                                    cx="15" cy="15" r="14"></circle>
                                <circle class="circle-chart__circle" stroke="#4eb04b" stroke-width="2"
                                    stroke-dasharray="50,100" cx="15" cy="15" r="14"></circle>
                            </svg>
                            <b>5.4</b>
                        </div>
                        <!-- end circle-rate -->
                        <div class="hd">1080 <b>HD</b></div>
                        <!-- end hd -->
                    </figure>
                    <div class="video-content"> <small class="range">115 min,</small>
                        <ul class="tags">
                            <li><?= t('Action') ?></li>
                        </ul>
                        <div class="age">16+</div>
                        <!-- end age -->
                        <h3 class="name"><a href="movie-single.html">Bhurende</a></h3>
                    </div>
                    <!-- end video-content -->
                </div>
                <!-- end video-thumb -->
            </div>
            <!-- end col-2 -->

            <!-- Film 7 : Prix de la Nuit -->
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-6">
                <div class="video-thumb light">
                    <figure class="video-image"> <a href="#"><img src="../assets/images/14.png" alt="Affiche du film Mzigo Kwa Mama" loading="lazy"></a>
                        <div class="circle-rate">
                            <svg class="circle-chart" viewBox="0 0 30 30" width="100" height="100"
                                xmlns="http://www.w3.org/2000/svg">
                                <circle class="circle-chart__background" stroke="#2f3439" stroke-width="2" fill="none"
                                    cx="15" cy="15" r="14"></circle>
                                <circle class="circle-chart__circle" stroke="#4eb04b" stroke-width="2"
                                    stroke-dasharray="50,100" cx="15" cy="15" r="14"></circle>
                            </svg>
                            <b>5.4</b>
                        </div>
                        <!-- end circle-rate -->
                        <div class="hd">1080 <b>HD</b></div>
                        <!-- end hd -->
                    </figure>
                    <div class="video-content"> <small class="range">130 min,</small>
                        <ul class="tags">
                            <li><?= t('Drame social') ?></li>
                        </ul>
                        <div class="age">16+</div>
                        <!-- end age -->
                        <h3 class="name"><a href="movie-single.html">Mzigo Kwa Mama</a></h3>
                    </div>
                    <!-- end video-content -->
                </div>
                <!-- end video-thumb -->
            </div>
            <!-- end col-2 -->

            <!-- Film 8 : Roho Nguvu -->
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-6">
                <div class="video-thumb light">
                    <figure class="video-image"> <a href="#"><img src="../assets/images/15.png" alt="Affiche de la saison 2 de Majaribu" loading="lazy"></a>
                        <div class="circle-rate">
                            <svg class="circle-chart" viewBox="0 0 30 30" width="100" height="100"
                                xmlns="http://www.w3.org/2000/svg">
                                <circle class="circle-chart__background" stroke="#2f3439" stroke-width="2" fill="none"
                                    cx="15" cy="15" r="14"></circle>
                                <circle class="circle-chart__circle" stroke="#4eb04b" stroke-width="2"
                                    stroke-dasharray="50,100" cx="15" cy="15" r="14"></circle>
                            </svg>
                            <b>5.4</b>
                        </div>
                        <!-- end circle-rate -->
                        <div class="hd">1080 <b>HD</b></div>
                        <!-- end hd -->
                    </figure>
                    <div class="video-content"> <small class="range">128 min,</small>
                        <ul class="tags">
                            <li><?= t('Spirituel') ?></li>
                        </ul>
                        <div class="age">16+</div>
                        <!-- end age -->
                        <h3 class="name"><a href="movie-single.html">Majaribu</a></h3>
                    </div>
                    <!-- end video-content -->
                </div>
                <!-- end video-thumb -->
            </div>
            <!-- end col-2 -->

            <!-- Film 9 : Goma mon Amour -->
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-6">
                <div class="video-thumb light">
                    <figure class="video-image"> <a href="#"><img src="../assets/images/16.png" alt="Affiche de l'épisode 12 de Majaribu" loading="lazy"></a>
                        <div class="circle-rate">
                            <svg class="circle-chart" viewBox="0 0 30 30" width="100" height="100"
                                xmlns="http://www.w3.org/2000/svg">
                                <circle class="circle-chart__background" stroke="#2f3439" stroke-width="2" fill="none"
                                    cx="15" cy="15" r="14"></circle>
                                <circle class="circle-chart__circle" stroke="#4eb04b" stroke-width="2"
                                    stroke-dasharray="50,100" cx="15" cy="15" r="14"></circle>
                            </svg>
                            <b>5.4</b>
                        </div>
                        <!-- end circle-rate -->
                        <div class="hd">1080 <b>HD</b></div>
                        <!-- end hd -->
                    </figure>
                    <div class="video-content"> <small class="range">105 min,</small>
                        <ul class="tags">
                            <li><?= t('Romance') ?></li>
                        </ul>
                        <div class="age">12+</div>
                        <!-- end age -->
                        <h3 class="name"><a href="movie-single.html">Majaribu</a></h3>
                    </div>
                    <!-- end video-content -->
                </div>
                <!-- end video-thumb -->
            </div>
            <!-- end col-2 -->

            <!-- Film 10 : Nyiragongo -->
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-6">
                <div class="video-thumb light">
                    <figure class="video-image"> <a href="#"><img src="../assets/images/2.png" alt="Affiche des saisons 2 et 3 de Majaribu" loading="lazy"></a>
                        <div class="circle-rate">
                            <svg class="circle-chart" viewBox="0 0 30 30" width="100" height="100"
                                xmlns="http://www.w3.org/2000/svg">
                                <circle class="circle-chart__background" stroke="#2f3439" stroke-width="2" fill="none"
                                    cx="15" cy="15" r="14"></circle>
                                <circle class="circle-chart__circle" stroke="#4eb04b" stroke-width="2"
                                    stroke-dasharray="50,100" cx="15" cy="15" r="14"></circle>
                            </svg>
                            <b>5.4</b>
                        </div>
                        <!-- end circle-rate -->
                        <div class="hd">1080 <b>HD</b></div>
                        <!-- end hd -->
                    </figure>
                    <div class="video-content"> <small class="range">145 min,</small>
                        <ul class="tags">
                            <li><?= t('Drame') ?></li>
                        </ul>
                        <div class="age">16+</div>
                        <!-- end age -->
                        <h3 class="name"><a href="movie-single.html">Majaribu</a></h3>
                    </div>
                    <!-- end video-content -->
                </div>
                <!-- end video-thumb -->
            </div>
            <!-- end col-2 -->

            <!-- Film 11 : Brazzaville Nights -->
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-6">
                <div class="video-thumb light">
                    <figure class="video-image"> <a href="#"><img src="../assets/images/1.png" alt="Affiche de la saison 2 de Majaribu" loading="lazy"></a>
                        <div class="circle-rate">
                            <svg class="circle-chart" viewBox="0 0 30 30" width="100" height="100"
                                xmlns="http://www.w3.org/2000/svg">
                                <circle class="circle-chart__background" stroke="#2f3439" stroke-width="2" fill="none"
                                    cx="15" cy="15" r="14"></circle>
                                <circle class="circle-chart__circle" stroke="#4eb04b" stroke-width="2"
                                    stroke-dasharray="50,100" cx="15" cy="15" r="14"></circle>
                            </svg>
                            <b>5.4</b>
                        </div>
                        <!-- end circle-rate -->
                        <div class="hd">1080 <b>HD</b></div>
                        <!-- end hd -->
                    </figure>
                    <div class="video-content"> <small class="range">118 min,</small>
                        <ul class="tags">
                            <li><?= t('Thriller') ?></li>
                        </ul>
                        <div class="age">18+</div>
                        <!-- end age -->
                        <h3 class="name"><a href="movie-single.html">Majaribu</a></h3>
                    </div>
                    <!-- end video-content -->
                </div>
                <!-- end video-thumb -->
            </div>
            <!-- end col-2 -->

            <!-- Film 12 : La Promesse -->
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-6">
                <div class="video-thumb light">
                    <figure class="video-image"> <a href="#"><img src="../assets/images/6.png" alt="Affiche de la série Douleur" loading="lazy"></a>
                        <div class="circle-rate">
                            <svg class="circle-chart" viewBox="0 0 30 30" width="100" height="100"
                                xmlns="http://www.w3.org/2000/svg">
                                <circle class="circle-chart__background" stroke="#2f3439" stroke-width="2" fill="none"
                                    cx="15" cy="15" r="14"></circle>
                                <circle class="circle-chart__circle" stroke="#4eb04b" stroke-width="2"
                                    stroke-dasharray="50,100" cx="15" cy="15" r="14"></circle>
                            </svg>
                            <b>5.4</b>
                        </div>
                        <!-- end circle-rate -->
                        <div class="hd">1080 <b>HD</b></div>
                        <!-- end hd -->
                    </figure>
                    <div class="video-content"> <small class="range">122 min,</small>
                        <ul class="tags">
                            <li><?= t('Drame') ?></li>
                        </ul>
                        <div class="age">16+</div>
                        <!-- end age -->
                        <h3 class="name"><a href="movie-single.html">Douleur</a></h3>
                    </div>
                    <!-- end video-content -->
                </div>
                <!-- end video-thumb -->
            </div>
            <!-- end col-2 -->

            <?php foreach ($extraLocalPhotos as $photo): ?>
            <div class="col-xl-2 col-lg-3 col-md-4 col-sm-6 col-6">
                <?php if (!empty($photo['mediaId'])): ?>
                <a class="video-thumb light catalog-photo-card catalog-photo-link" href="index.php?p=detail&id_media=<?= rawurlencode((string)$photo['mediaId']) ?>">
                <?php else: ?>
                <article class="video-thumb light catalog-photo-card">
                <?php endif; ?>
                    <figure class="video-image<?= !empty($photo['wide']) ? ' catalog-photo-wide' : '' ?>">
                        <img src="../assets/images/<?= htmlspecialchars($photo['file'], ENT_QUOTES, 'UTF-8') ?>"
                            alt="<?= htmlspecialchars($photo['title'], ENT_QUOTES, 'UTF-8') ?>"
                            loading="lazy">
                    </figure>
                    <div class="video-content">
                        <h3 class="name"><?= htmlspecialchars($photo['title'], ENT_QUOTES, 'UTF-8') ?></h3>
                    </div>
                <?php if (!empty($photo['mediaId'])): ?>
                </a>
                <?php else: ?>
                </article>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>

            <div class="col-12 text-center"> <a href="#films" class="custom-button"><?= t('VOIR TOUS LES FILMS') ?></a> </div>
            <!-- end col-12 -->
        </div>
        <!-- end row -->
    </div>
    <!-- end container -->
</section>
<!-- end content-section -->
<?= $streamingSection ?>

<!-- ============================================
     SECTION 4 : TÉMOIGNAGE
     ============================================ -->
<section class="content-section" data-background="../assets/images/section-pattern01.png">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-7">
                <div class="testimonial">
                    <h2><?= t('Approuvé par les experts et les vrais utilisateurs') ?></h2>
                    <blockquote><?= t('« Kazuri est bien plus qu’une plateforme de streaming. C’est une fenêtre ouverte sur la richesse du cinéma africain, avec une attention particulière aux productions de Goma. »') ?></blockquote>
                    <figure> <img src="../assets/images/avatar01.jpg" alt="Image"> <img
                            src="../assets/images/brand01.jpg" alt="Image">
                    </figure>
                    <h6>Kazuri Team <br>
                        <small><?= t('Streaming africain') ?></small>
                    </h6>
                </div>
                <!-- end testimonial -->
            </div>
            <!-- end col-7 -->
            <div class="col-lg-5">
                <figure class="side-image"> <img src="../assets/images/side-image02.png" alt="Image"> </figure>
                <!-- end side-image -->
            </div>
            <!-- end col-5 -->
        </div>
        <!-- end row -->
    </div>
    <!-- end container -->
</section>
<!-- end content-section -->

<!-- ============================================
     SECTION 5 : ABONNEMENTS
     ============================================ -->
<section class="content-section">
    <div class="video-bg">
        <video src="../assets/videos/video01.mp4" autoplay muted playsinline loop></video>
    </div>
    <!-- end video-bg -->
    <div class="container">
        <div class="row">
            <div class="col-12">
                <div class="section-title text-center light">
                    <h6><?= t('CHOISISSEZ VOTRE ABONNEMENT') ?></h6>
                    <h2><?= t('Prêt à commencer ?') ?></h2>
                </div>
                <!-- end section-title -->
            </div>
            <!-- end col-12 -->
        </div>
        <!-- end row -->
        <div class="row">
            <div class="col-lg-4">
                <div class="price-box">
                    <figure class="icon"><img src="../assets/images/icon04.png" alt="Image"></figure>
                    <div class="months"><?= t('Plan 1 mois') ?></div>
                    <div class="price"><span>$</span> <b>4.99</b> <small><?= t('par mois') ?></small> </div>
                    <a href="#"><?= t('S’ABONNER') ?></a>
                    <div class="guarantee"><i class="lni lni-protection"></i> <?= t('Garantie satisfait ou remboursé 30 jours') ?></div>
                </div>
                <!-- end price-box -->
            </div>
            <!-- end col-4 -->
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
                <!-- end price-box -->
            </div>
            <!-- end col-4 -->
            <div class="col-lg-4">
                <div class="price-box">
                    <figure class="icon"><img src="../assets/images/icon06.png" alt="Image"></figure>
                    <div class="months"><?= t('Plan 6 mois') ?></div>
                    <div class="price"><span>$</span> <b>3.99</b> <small><?= t('par mois') ?></small> </div>
                    <a href="#"><?= t('S’ABONNER') ?></a>
                    <div class="guarantee"><i class="lni lni-protection"></i> <?= t('Garantie satisfait ou remboursé 30 jours') ?></div>
                </div>
                <!-- end price-box -->
            </div>
            <!-- end col-4 -->
        </div>
        <!-- end row -->
    </div>
    <!-- end container -->
</section>
<!-- end content-section -->

<!-- ============================================
     SECTION 6 : GET STARTED
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
                <!-- end get-started-box -->
            </div>
            <!-- end col-4 -->
        </div>
        <!-- end row -->
    </div>
    <!-- end container -->
</section>