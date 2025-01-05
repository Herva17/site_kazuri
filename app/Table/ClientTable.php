<?php
namespace App\Table;

use Core\Database\Database;
use Core\Table\Table;


class ClientTable extends Table
{
     protected $table = 't_client';

     public function __construct(Database $database)
     {
         parent::__construct($database);
     }

   
}