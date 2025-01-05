<?php
namespace App\Table;

use Core\Database\Database;
use Core\Table\Table;


class PanierTable extends Table
{
     protected $table = 't_panier';

     public function __construct(Database $database)
     {
         parent::__construct($database);
     }

   
}