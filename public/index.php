<?php
use app\App;
use core\Auth\DBAuth;
define('ROOT', dirname(__DIR__)); 
require ROOT. "/app/App.php";
 App::load();

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

if ($page === '/') {

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