<?php
namespace App\Table;

use Core\Database\Database;
use Core\Table\Table;


class PaiementTable extends Table
{
     protected $table = 'paiement';

     public function __construct(Database $database)
     {
         parent::__construct($database);
     }

   
}