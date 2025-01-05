<?php
namespace App\Table;

use Core\Database\Database;
use Core\Table\Table;


class UserTable extends Table
{
     protected $table = 'utilisateur';

     public function __construct(Database $database)
     {
         parent::__construct($database);
     }

   
}