<?php
namespace app\Table;

use core\database\Database;
use core\Table\Table;


class BatimentTable extends Table
{
     protected $table = 'batiment';

     public function __construct(Database $database)
     {
         parent::__construct($database);
     }

     
    public function count()

    {

        return $this->query('SELECT count(*) FROM '. $this->table);

    }

   
}