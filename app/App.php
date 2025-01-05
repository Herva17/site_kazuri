<?php

namespace App;

use core\Config;
use core\database\MysqlDatabase;

 /*
  * La class pacerelle de l"application (Assure la liaison entre entre notre core(backend) et notre forntend( dossier App))
  */
class App
{
    private static $_instance;
      /*
       * Instance de la DB
       */
    private $db_instance;

    /**
     * Permet d'intacnier cette Class App
     * @return App
     */
    public static function getInstance()
    {
        if(is_null(self::$_instance))
        {
            self::$_instance = new App();
        }

        return self::$_instance;
    }

    /**
     * La fonction bootstrap de l'application car il demarre le session et puis charge nos autoload
     */
    public static function load()
    {
        session_start();

        require ROOT . '/core/Autoload.php';

        \Core\Autoload::register();

        require ROOT . '/app/Autoload.php';
        \App\Autoload::register();
    }

    /**
     * Pour recuperer Nos Class Table
     * 
     * @param $name
     * @return mixed
     */
    public function getTable($name)
    {
        $class_name = '\\App\\Table\\'.ucfirst($name. 'Table');
        return new $class_name($this->getDB());
    }

    /**
     * Pour requiperer les données de la connection  ala base de donnée
     * @return MysqlDatabase
     */
    public function getDB()
    {
        $config = Config::getInstance(ROOT .'/config/config.php');

        $this->db_instance = new MysqlDatabase($config->get('db_name'), $config->get('db_user'), $config->get('db_pass'), $config->get('db_host'));

        return $this->db_instance;
    }

    /**
     * @param $page
     */
    public function forbidden($page)
    {
        header("HTTP/1.0 403 Forbidden");
//        die("Acces interdit...!");
        header("location:admin.php?p=".$page);
    }


    /**
     * Pour uploader les fichier
     * @param $champForm
     * @param $dir
     * @return bool
     */
    public function upload($champForm, $dir)
    {
        $dir = str_replace('\\', '/', $dir);

        if(!file_exists($dir))
        {
            mkdir($dir, 0777, true);
        }

        $avt = $champForm['name'];
        $avatar_tmp = $champForm['tmp_name'];
        if(!empty($avt)){
            $extensions = array('.jpg','.gif.','.png', '.JPG', '.JPEG', '.PNG', '.GIF', '.SGV');
            $extension = strrchr($champForm['name'], '.');

            $file = uniqid() . $extension;

            if (in_array($extension, $extensions)) {
                $deplacement = move_uploaded_file($avatar_tmp, $dir.$file);
                if ($deplacement)
                {
                    $_SESSION['photo'] = $file ;
                    return true;
                }

            }
        }
        return false;
    }

}