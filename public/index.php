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

ob_start();

// --- CHARGEMENT DES VUES (CONTENU) ---
if ($page === '/' || $page === 'welcome') {
    require ROOT . '/ressources/views/pages/welcome.php';
} elseif ($page === 'logout') {
    session_destroy();
    header('Location: index.php?p=account');
    exit;
} elseif ($page === 'account') {
    require ROOT . '/ressources/views/pages/account.php';
} elseif ($page === 'detail') {
    require ROOT . '/ressources/views/pages/detail_media.php';
} elseif ($page === 'home') { 
    require ROOT . '/ressources/views/pages/home.php';
} elseif ($page === 'subscription') {
    require ROOT . '/ressources/views/pages/subscription.php';
} elseif ($page === 'studio') {
    require ROOT . '/ressources/views/pages/studio.php';
} else {
    require ROOT . '/ressources/views/pages/404.php';
}

$content = ob_get_clean();

// --- CHARGEMENT DES LAYOUTS (GABARITS) ---
if ($page === 'account') {
    require ROOT . '/ressources/views/layout/default_account.php';
} elseif ($page === 'detail') {
    require ROOT . '/ressources/views/layout/default_detailMedia.php';
} elseif ($page === 'home') {
    require ROOT . '/ressources/views/layout/default_home.php';
} elseif ($page === 'subscription') {
    require ROOT . '/ressources/views/layout/default_dashboard.php';  // ✅ Changé ici
} elseif ($page === 'studio') {
    require ROOT . '/ressources/views/layout/default_dashboard.php';
} else {
    require ROOT . '/ressources/views/layout/default.php';
}