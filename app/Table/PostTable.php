<?php
namespace App\Table;

use Core\Database\Database;
use Core\Table\Table;


class PostTable extends Table
{
     protected $table = ' t_post';

     public function __construct(Database $database)
     {
         parent::__construct($database);
     }

    public function all()
     {
         return $this->query('SELECT * FROM '. $this->table);
     }
}