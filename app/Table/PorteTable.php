<?php
namespace app\Table;

use core\Database\Database;
use core\Table\Table;


class PorteTable extends Table
{
     protected $table = 'porte';

     public function __construct(Database $database)
     {
         parent::__construct($database);
     }

     
    public function count()

    {

        return $this->query('SELECT count(*) FROM '. $this->table);

    }

   
}