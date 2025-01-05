<?php

namespace App\Table;



use Core\Database\Database;

use Core\Table\Table;





class LikeTable extends Table

{

     protected $table = ' t_likes';



     public function __construct(Database $database)

     {

         parent::__construct($database);

     }



    public function all()

     {

         return $this->query('SELECT * FROM '. $this->table .' order by  id desc limit 7');

     }

     public function allnext()

     {

        return $this->query('SELECT * FROM '. $this->table .' order by id asc ');



     	return $n;

     }

}