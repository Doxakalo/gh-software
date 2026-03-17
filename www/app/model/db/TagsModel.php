<?php

namespace App\Model\Db;

use Nette\Database\Table\ActiveRowtable,
    Nette\Database\Explorer;

class TagsModel
{
    const Table = "sys_tags";

    private $db;

    public function __construct(Explorer $db)
    {
        $this->db = $db;
    }

    public function getRecords(){
        return $this->db->table(self::Table)->where("publish",1)->order("published DESC");
    }

    public function getRecord($id){
        return $this->db->table(self::Table)->get($id);
    }

    public function getTagsByNewsIds($ids){
        return $this->db->table(self::Table)->where("id",$ids);
    }


    public function getRecordByTagName($tag){
        return $this->db->table(self::Table)->where("name",$tag)->fetch();
    }

    public function deleteRecord($id){
        return $this->db->table(self::Table)->where("id", $id)->delete();
    }

    public function truncateTable(){
        return $this->db->table(self::Table)->delete();
    }
}
