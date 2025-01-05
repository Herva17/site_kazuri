<?php
namespace App\Table;

use core\database\Database;
use core\Table\Table;


class LocataireTable extends Table
{
     protected $table = 'locataire';

     public function __construct(Database $database)
     {
         parent::__construct($database);
     }

     
    public function count()

    {

        return $this->query('SELECT count(*) FROM '. $this->table);

    }

   
}