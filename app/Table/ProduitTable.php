<?php
namespace App\Table;

use Core\Database\Database;
use Core\Table\Table;


class ProduitTable extends Table
{
     protected $table = 't_produit';

     public function __construct(Database $database)
     {
         parent::__construct($database);
     }

    public function all()
     {
         return $this->query('SELECT * FROM '. $this->table);
     }

     public function countId(){
        
        
       return  $this->query('SELECT COUNT(*) RAND() FROM  t_produit');

	}
    public function count(){
        return $this.('SELECT COUNT(*) FROM t_approvision');
    }
}