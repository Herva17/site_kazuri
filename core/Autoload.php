<?php

namespace core;

class Autoload
{
    static function register()
    {
        spl_autoload_register([__class__, 'autoload']);
    }
    static function autoload($class)
    {
        if (strpos($class, __NAMESPACE__ . "\\") === 0) {
            $class = str_replace(__NAMESPACE__ . "\\", "", $class);       //on remplace tutoriel\ par un vide
            $class = str_replace("\\", "/", $class);    //on remplace les \ par /   
          //var_dump($class);die();
            require __DIR__ . "/" . $class . ".php";                           //on charge tout les fichiers du dossier class
        }

    }
}