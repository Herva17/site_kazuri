<?php 
namespace app\Table;
use core\database\Database;
use core\Table\Table;


class MediaTable extends Table
{
protected $table = 'medias';

public function __construct(Database $database)
{
parent::__construct($database);
}

public function count()

{

return $this->query('SELECT count(*) FROM '. $this->table);

}

}