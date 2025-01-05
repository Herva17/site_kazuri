<?php
namespace App\Table;

use Core\Database\Database;
use Core\Table\Table;


class CommandeTable extends Table
{
     protected $table = ' bag';

     public function __construct(Database $database)
     {
         parent::__construct($database);
     }

    public function all()
     {
         return $this->query('SELECT * FROM '. $this->table .' order by  id desc limit 7');
     }

     
     public function countId(){
        
        
        return  $this->query('SELECT COUNT(id)  FROM  bag');
 
     }
     public function allnext()
     {
        return $this->query('SELECT * FROM '. $this->table .' order by id asc ');

     	return $n;
     }
    
}