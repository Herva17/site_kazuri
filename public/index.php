<?php
use app\App;
use core\Auth\DBAuth;
define('ROOT', dirname(__DIR__)); 
require ROOT. "/app/App.php";
 App::load();

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

// $app = new DBAuth(App::getInstance()->getDB('user'));
// //var_dump($app);die();
//  if(!$app->isLogged() && $page != 'login' && $page != 'logout')
//  {
//     $page = 'login';

//  }

ob_start();

if ($page === '/' || $page === 'welcome') {

    require ROOT . '/ressources/views/pages/welcome.php';
   
}elseif($page === 'logout'){

    session_destroy();

    require ROOT . '/ressources/views/pages/Login/login.php';
  
}elseif($page === 'account'){
     require ROOT . '/ressources/views/pages/account.php';
}elseif($page === 'detail'){
    require ROOT . '/ressources/views/pages/detail_media.php';
}

$content = ob_get_clean();

if($page === 'account'){

   require ROOT . '/ressources/views/layout/default_account.php';

}elseif($page === 'detail'){
    
    require ROOT . '/ressources/views/layout/default_detailMedia.php';
}
else{
    require ROOT . '/ressources/views/layout/default.php';
}