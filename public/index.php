<?php
use app\App;
use core\Auth\DBAuth;

define('ROOT', dirname(__DIR__)); 
require ROOT. "/app/App.php";
App::load();

// ✅ On s'assure que la classe User est chargée pour tout le site
require_once ROOT . '/Classes/UserClass.php';

$supportedLanguages = ['fr', 'en', 'sw'];
if (isset($_GET['lang']) && in_array($_GET['lang'], $supportedLanguages, true)) {
    $_SESSION['kazuri_language'] = $_GET['lang'];
}
$currentLanguage = $_SESSION['kazuri_language'] ?? 'fr';
if (!in_array($currentLanguage, $supportedLanguages, true)) {
    $currentLanguage = 'fr';
}
$_SESSION['kazuri_language'] = $currentLanguage;
$translations = require ROOT . '/config/translations.php';

function t($text)
{
    global $currentLanguage, $translations;
    return $translations[$currentLanguage][$text] ?? $text;
}

$languageOptions = [
    'fr' => 'Français',
    'en' => 'English',
    'sw' => 'Kiswahili',
];

if (isset($_GET['p'])) {
    $page = $_GET['p'];
} else {
    $page = '/';
}

// ✅ Petite fonction utilitaire : charge une vue si elle existe, sinon affiche une page "En construction"
function loadView($page) {
    $path = ROOT . '/ressources/views/pages/' . $page . '.php';
    if (file_exists($path)) {
        require $path;
    } else {
        echo '<div style="text-align: center; padding: 80px 20px; color: #aaa;">';
        echo '<i class="ph ph-construction" style="font-size: 64px; color: #444; display: block; margin-bottom: 16px;"></i>';
        echo '<h2 style="color: #fff; margin: 0 0 12px;">Page en construction</h2>';
        echo '<p style="margin: 0 0 20px;">La page <strong>« ' . htmlspecialchars($page) . ' »</strong> sera bientôt disponible.</p>';
        echo '<a href="index.php?p=home" style="display: inline-block; background: var(--accent); color: #fff; padding: 12px 24px; border-radius: 8px; text-decoration: none; font-weight: bold;">Retour à l\'accueil</a>';
        echo '</div>';
    }
}

ob_start();

// ============================================================
// --- CHARGEMENT DES VUES (CONTENU) ---
// ============================================================
if ($page === '/' || $page === 'welcome') {
    loadView('welcome');
} elseif ($page === 'logout') {
    session_destroy();
    header('Location: index.php?p=account');
    exit;
} elseif ($page === 'account') {
    loadView('account');
} elseif ($page === 'detail') {
    loadView('detail_media');
} elseif ($page === 'home') { 
    loadView('home');
} 
// ✅ PAGES PUBLIQUES (Films, Séries, Musique, Reels, Bibliothèque, Likes)
elseif (in_array($page, ['films', 'series', 'musique', 'reels', 'library', 'likes'])) {
    loadView($page);
} 
// ✅ ROUTES ABONNEMENT & CHECKOUT
elseif ($page === 'subscription') {
    loadView('subscription');
} elseif ($page === 'pricing') {
    loadView('pricing');
} elseif ($page === 'checkout') {
    loadView('checkout');
} 
// ✅ ROUTES STUDIO (Tableau de bord + sous-pages)
elseif (in_array($page, [
    'studio', 'studio_contents', 'studio_stats', 'studio_comments', 
    'studio_monetization', 'studio_settings', 'studio_upload'
])) {
    loadView($page);
} 
// ✅ ROUTES ADMIN
elseif (in_array($page, ['admin', 'admin_users', 'admin_videos'])) {
    loadView($page);
} 
// ✅ PAGE 404
else {
    loadView('404');
}

$content = ob_get_clean();

// ============================================================
// --- CHARGEMENT DES LAYOUTS (GABARITS) ---
// ============================================================
if ($page === 'account') {
    $layoutPath = ROOT . '/ressources/views/layout/default_account.php';
} elseif ($page === 'detail') {
    $layoutPath = ROOT . '/ressources/views/layout/default_detailMedia.php';
} 
// ✅ VITRINE (Page d'accueil non connecté) -> Layout Classique
elseif (in_array($page, ['/', 'welcome'])) {
    $layoutPath = ROOT . '/ressources/views/layout/default.php';
} 
// ✅ DASHBOARD + PAGES PUBLIQUES (Connecté) -> Layout YouTube
elseif (in_array($page, ['home', 'films', 'series', 'musique', 'reels', 'library', 'likes'])) {
    $layoutPath = ROOT . '/ressources/views/layout/default_home.php';
} 
// ✅ LAYOUT STUDIO
elseif (in_array($page, [
    'subscription', 'pricing', 'checkout', 
    'studio', 'studio_contents', 'studio_stats', 'studio_comments', 
    'studio_monetization', 'studio_settings', 'studio_upload'
])) {
    $layoutPath = ROOT . '/ressources/views/layout/default_dashboard.php';
} 
// ✅ LAYOUT ADMIN
elseif (in_array($page, ['admin', 'admin_users', 'admin_videos'])) {
    $layoutPath = ROOT . '/ressources/views/layout/default_admin.php';
} 
// LAYOUT PAR DÉFAUT
else {
    $layoutPath = ROOT . '/ressources/views/layout/default.php';
}

// ✅ Vérification finale : si le layout existe, on le charge
if (file_exists($layoutPath)) {
    require $layoutPath;
} else {
    echo "Erreur : Layout introuvable (" . htmlspecialchars($layoutPath) . ")";
}