<?php
namespace App\Table;

use Core\Database\Database;
use Core\Table\Table;


class GarantieTable extends Table
{
     protected $table = 'garatie';

     public function __construct(Database $database)
     {
         parent::__construct($database);
     }

     
    public function count()

    {

        return $this->query('SELECT count(*) FROM '. $this->table);

    }

   
}