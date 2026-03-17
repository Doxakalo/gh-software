<?php

namespace App\Model\Db;

use Nette\Database\Table\ActiveRowtable,
    Nette\Database\Explorer;

class OrdersModel
{
    const Table = "carts";

    private $db;

    public function __construct(Explorer $db)
    {
        $this->db = $db;
    }

    public function createRecord($data){
        return $this->db->table(self::Table)->insert($data);
    }

    public function getRecord($id){
        return $this->db->table(self::Table)->get($id);
    }

    public function deleteRecord($id){
        return $this->db->table(self::Table)->where("id", $id)->delete();
    }

    public function truncateTable(){
        return $this->db->table(self::Table)->delete();
    }
}
