<?php

namespace core\Auth;

use core\database\Database;


class DBAuth
{
    private $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function getAuthUserId()
    {
        if($this->isLogged())
        {
            return $_SESSION['auth'];
        }
        return false;
    }

    public function login($tel, $motdepasse)
    {
        $user = $this->db->prepare("SELECT * FROM utilisateur WHERE tel = ?", [$tel], null, true);
        // var_dump($user);die();

        if($user)
        {
            // if(password_verify($password, $user->password)) si le mot de pass est hacher
            if($motdepasse === $user->motdepasse) //si non
            {
                $_SESSION['auth'] = $user->tel;
                return true; 
            }
        }
        return false;
    }
    public function forbidden(){
       echo 'interdit';
    }
     

    public function isLogged()
    {
       return isset($_SESSION['auth']); 
    }

}