<?php
namespace App\Table;

use Core\Database\Database;
use Core\Table\Table;


class CategorieTable extends Table
{
     protected $table = 'categorie';

     public function __construct(Database $database)
     {
         parent::__construct($database);
     }

    public function all()
     {
         return $this->query('SELECT * FROM '. $this->table);
     }

     public function countId(){
        
        
       return  $this->query('SELECT COUNT(*) RAND() FROM  categorie');

	}
    public function count(){
        return $this.('SELECT COUNT(*) FROM categorie');
    }
}