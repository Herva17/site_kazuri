<!doctype html>
<html lang="<?= htmlspecialchars($currentLanguage, ENT_QUOTES, 'UTF-8') ?>">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="theme-color" content="#e90101" />
    <title>Kazuri | Streaming</title>
    <meta name="author" content="Themezinho">
    <meta name="description" content="<?= t('Kazuri est une plateforme de streaming africain') ?>">
    <meta name="keywords"
        content="digiflex, video, movie, show, tv, watch, online, streaming, stream, ads, trailer, imdb, dram, action, hollywood, cinema, rate">

    <!-- SOCIAL MEDIA META -->
    <meta property="og:description" content="digiflex | Online Movie Streaming">
    <meta property="og:image" content="http://www.themezinho.net/digiflex/preview.png">
    <meta property="og:site_name" content="digiflex">
    <meta property="og:title" content="digiflex">
    <meta property="og:type" content="website">
    <meta property="og:url" content="http://www.themezinho.net/digiflex">

    <!-- TWITTER META -->
    <meta name="twitter:card" content="summary">
    <meta name="twitter:site" content="@digiflex">
    <meta name="twitter:creator" content="@digiflex">
    <meta name="twitter:title" content="digiflex">
    <meta name="twitter:description" content="Kazuri | Movie Streaming">
    <meta name="twitter:image" content="http://www.themezinho.net/digiflex/preview.png">

    <!-- FAVICON FILES -->
    <link href="../assets/ico/Icon2_Black114.png" rel="apple-touch-icon" sizes="144x144">
    <link href="../assets/ico/Icon2_Black114.png" rel="apple-touch-icon" sizes="114x114">
    <link href="../assets/ico/Icon2_Black7272.png" rel="apple-touch-icon" sizes="72x72">
    <link href="../assets/ico/Icon2_Black7272.png" rel="apple-touch-icon">
    <link href="../assets/ico/favicon32.png" rel="shortcut icon">

    <!-- CSS FILES -->
    <link rel="stylesheet" href="../assets/css/lineicons.css">
    <link rel="stylesheet" href="../assets/css/fancybox.min.css">
    <link rel="stylesheet" href="../assets/css/swiper.min.css">
    <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/account.css?v=<?= filemtime(ROOT . '/assets/css/account.css') ?>">
    <link rel="stylesheet" href="../assets/css/mobile-menu.css?v=<?= filemtime(ROOT . '/assets/css/mobile-menu.css') ?>">
</head>

<body class="account-page">
    <nav class="mobile-menu" aria-label="<?= t('Navigation mobile') ?>">
        <div class="inner">
            <div class="mobile-menu-header">
                <a class="mobile-menu-brand" href="index.php?p=welcome" aria-label="Kazuri, accueil">
                    <span>KAZURI</span>
                    <small><?= t('CINÉMA AFRICAIN') ?></small>
                </a>
                <button class="mobile-menu-close" type="button" aria-label="<?= t('Fermer le menu') ?>">
                    <i class="lni lni-close" aria-hidden="true"></i>
                </button>
            </div>
            <a href="index.php?p=account" class="button-account" aria-current="page"><i class="lni lni-user" aria-hidden="true"></i> <?= t('MON COMPTE') ?></a>
            <!-- end button-account -->
            <div class="site-menu">
                <p class="mobile-menu-label"><?= t('Explorer le catalogue') ?></p>
                <ul>
                    <li><a href="index.php?p=welcome#series"><?= t('Séries') ?></a></li>
                    <li><a href="index.php?p=welcome#films"><?= t('Films') ?></a></li>
                    <li><a href="index.php?p=welcome#jeunesse"><?= t('Jeunesse') ?></a></li>
                    <li><a href="index.php?p=welcome#nouveautes"><?= t('Nouveautés') ?></a></li>
                    <li><a href="index.php?p=welcome#boutiques"><?= t('Boutiques') ?></a></li>
                    <li><a href="index.php?p=welcome#shorts"><?= t('Shorts') ?></a></li>
                    <li><a href="index.php?p=welcome#autres"><?= t('Autres') ?></a></li>
                </ul>
            </div>
            <?php require ROOT . '/ressources/views/partials/language_switcher.php'; ?>
            <!-- end site-menu -->
        </div>
        <!-- end inner -->
    </nav>
    <!-- end mobile-menu -->
    <nav class="navbar">
        <div class="logo"> <a href="index.php?p=welcome"> <img src="../assets/ico/Icon1_White.png" alt="Image"> </a>
        </div>
        <!-- end logo -->
        <div class="site-menu">
            <ul>
                <li><a href="index.php?p=welcome#series"><?= t('Séries') ?></a></li>
                <li><a href="index.php?p=welcome#films"><?= t('Films') ?></a></li>
                <li><a href="index.php?p=welcome#jeunesse"><?= t('Jeunesse') ?></a></li>
                <li><a href="index.php?p=welcome#nouveautes"><?= t('Nouveautés') ?></a></li>
                <li><a href="index.php?p=welcome#boutiques"><?= t('Boutiques') ?></a></li>
                <li><a href="index.php?p=welcome#shorts"><?= t('Shorts') ?></a></li>
                <li><a href="index.php?p=welcome#autres"><?= t('Autres') ?></a></li>
            </ul>
        </div>
        <!-- end site-menu -->
        <div class="user-menu">
            <div class="navbar-search"><i class="lni lni-search-alt"></i></div>
            <div class="navbar-notify">
                <i class="lni lni-alarm"></i><b>2</b>
                <div class="notify-dropdown">
                    <div class="total-notify">
                        <h6><?= t('Notifications') ?></h6>
                        <span>2</span>
                    </div>
                    <ul>
                        <li><p><?= t('Une nouvelle vidéo est disponible sur Kazuri.') ?></p><span><?= t('Il y a 2 jours') ?></span></li>
                        <li><p><?= t('Découvrez les dernières sorties de films et de séries.') ?></p><span><?= t('Il y a 3 jours') ?></span></li>
                    </ul>
                </div>
            </div>
            <div class="navbar-account"><a href="index.php?p=account"><?= t('ACCOUNT') ?> <i class="lni lni-user"></i></a></div>
            <?php require ROOT . '/ressources/views/partials/language_switcher.php'; ?>
        </div>
        <!-- end user-menu -->
        <div class="hamburger-menu">
            <button class="hamburger" type="button" aria-label="Ouvrir le menu" aria-expanded="false">
                <svg width="45" height="45" viewBox="0 0 100 100">
                    <path class="line line1"
                        d="M 20,29.000046 H 80.000231 C 80.000231,29.000046 94.498839,28.817352 94.532987,66.711331 94.543142,77.980673 90.966081,81.670246 85.259173,81.668997 79.552261,81.667751 75.000211,74.999942 75.000211,74.999942 L 25.000021,25.000058" />
                    <path class="line line2" d="M 20,50 H 80" />
                    <path class="line line3"
                        d="M 20,70.999954 H 80.000231 C 80.000231,70.999954 94.498839,71.182648 94.532987,33.288669 94.543142,22.019327 90.966081,18.329754 85.259173,18.331003 79.552261,18.332249 75.000211,25.000058 75.000211,25.000058 L 25.000021,74.999942" />
                </svg>
            </button>
        </div>
        <!-- end hamburger-menu -->
    </nav>
    <!-- end navbar -->
    <section class="search-box">
        <div class="container">
                        <h6><?= t('Type movie or tv show name to find it') ?></h6>
            <form>
                <input type="search" placeholder="<?= t('Search here') ?>">
                <input type="submit" value="<?= t('FIND') ?>">
            </form>
        </div>
        <!-- end container -->
    </section>
    <header class="page-header">
        <div class="container">
            <h1><?= t('Account') ?></h1>
        </div>
    </header>
    <!-- end page-header -->
    <main>
        <?=$content?>
        <!-- end content-section -->
        <footer class="footer">
            <div class="container">
                <div class="row">
                    <div class="col-lg-4">
                        <h5 class="call-us"><?= t('Questions?') ?> <strong><?= t('Appelez-nous') ?> +33 753-864-021</strong></h5>
                        <p><?= t('Découvrez les films et séries sélectionnés pour vous.') ?></p>
                        <div class="language"><i class="lni lni-world"></i><?php require ROOT . '/ressources/views/partials/language_switcher.php'; ?></div>
                        <!-- end language -->
                    </div>
                    <!-- end col-4 -->
                    <div class="col-lg-2 offset-lg-1 col-md-4">
                        <h6 class="widget-title">KazuriTv</h6>
                        <ul class="footer-menu">
                            <li><a href="index.php?p=welcome"><?= t('Accueil') ?></a></li>
                            <li><a href="#"><?= t('À propos') ?> Kazuri</a></li>

                            <li><a href="#"><?= t('Contact') ?></a></li>
                        </ul>
                    </div>
                    <!-- end col-4 -->
                    <div class="col-lg-2 col-md-4">
                        <h6 class="widget-title"><?= t('SUPPORT') ?></h6>
                        <ul class="footer-menu">
                            <li><a href="#"><?= t('FAQ') ?></a></li>
                            <li><a href="#"><?= t('Assistance') ?></a></li>
                            <li><a href="index.php?p=account"><?= t('Compte') ?></a></li>
                            <li><a href="#"><?= t('SUPPORT') ?> <i class="lni lni-question-circle"></i></a></li>
                            <li><a href="#"><?= t('Centre média') ?></a></li>
                        </ul>
                    </div>
                    <!-- end col-4 -->
                    <div class="col-lg-2 col-md-4">
                        <h6 class="widget-title"><?= t('Politiques') ?></h6>
                        <ul class="footer-menu">
                            <li><a href="#"><?= t('Confidentialité') ?></a></li>
                            <li><a href="#"><?= t('Conditions d’utilisation') ?></a></li>
                            <li><a href="#"><?= t('Mentions légales') ?></a></li>
                        </ul>
                    </div>
                    <!-- end col-4 -->
                </div>
                <!-- end row -->
            </div>
            <!-- end container -->
            <div class="bottom-bar">
                <div class="container"> <span>© 2025 Kazuri | Streaming</span> <span><?= t('Site créé par') ?> <a
                            href="#">ZakuriTeam</a></span> </div>
                <!-- end container -->
            </div>
            <!-- end bottom-bar -->
        </footer>
        <!-- end footer -->
    </main>

    <!-- JS FILES -->
    <script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit">
    </script>
    <script src="../assets/js/jquery.min.js"></script>
    <script src="../assets/js/bootstrap.min.js"></script>
    <script src="../assets/js/swiper.min.js"></script>
    <script src="../assets/js/fancybox.min.js"></script>
    <script src="../assets/js/scripts.js"></script>
</body>

</html>