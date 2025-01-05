<?php

namespace core\database;

use \PDO;

class MysqlDatabase extends Database
{
    private $db_name;
    private $db_username;
    private $db_hostname;
    private $db_password;
    private $dbb;

    public function __construct($db_name, $db_username,  $db_password, $db_hostname = "localhost")
    {
        $this->db_name = $db_name;
        $this->db_username = $db_username;
        $this->db_hostname = $db_hostname;
        $this->db_password = $db_password;
    }

    public function getPDO()
    {
        if(null === $this->dbb)
        {
            // $dbb = new PDO('mysql:dbname=exercice_blog; host=localhost', 'root', '');
            $dbb = new PDO("mysql:host=$this->db_hostname; dbname=$this->db_name", $this->db_username, $this->db_password);
            $dbb->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            // $dbb->setAttribute(PDO::ATTR_CASE, PDO::CASE_NATURAL);
            // $dbb->setAttribute(PDO::ATTR_ORACLE_NULLS, PDO::NULL_EMPTY_STRING);

            $this->dbb = $dbb;
        }

        return $this->dbb;
    }

    public function query($statement, $class_name = null, $one = false)
    {
        $query = $this->getPDO()->query($statement);

        if (strpos($statement, 'UPDATE') === 0 ||
            strpos($statement, 'INSERT') === 0 ||
            strpos($statement, 'DELETE') === 0)
            {
                return $query;
            }

            if($class_name === null)
            {
                $query->setFetchMode(PDO::FETCH_OBJ);
            }else {
            //$query->setFetchMode(PDO::FETCH_CLASS, $class_name);
            }

            if($one)
            {
               $donnees =  $query->fetch();
            }else {
            $donnees =  $query->fetchAll();
            }

            return $donnees;
    }

    public function prepare($statement, $attributes, $class_name = null, $one = false)
    {

       
        $req = $this->getPDO()->prepare($statement);
        // var_dump($attributes);die();
        
        $res = $req->execute($attributes);
        // var_dump($res);die();

        if (
            strpos($statement, 'UPDATE') === 0 ||
            strpos($statement, 'INSERT') === 0 ||
            strpos($statement, 'DELETE') === 0
        )
        {
            return $res;
        }

        if ($class_name === null)
        {
            $req->setFetchMode(PDO::FETCH_OBJ);
        }
        else
        {
            //$req->setFetchMode(PDO::FETCH_CLASS, $class_name);
        }

        if ($one)
        {
            $datas = $req->fetch();
            
        }
        else
        {
            $datas = $req->fetchAll();
        }
        return $datas;
    }

    public function lastInsetId()
    {
        return $this->getPDO()->lastInsetId();
    }
}