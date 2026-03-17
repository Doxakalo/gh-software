<?php

namespace App\Model\Db;

use Nette\Database\Table\ActiveRowtable;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection,
    Nette\Database\Explorer;

class NewsTagsModel
{
    const Table = "sys_news_tags";

    private $db;

    public function __construct(Explorer $db)
    {
        $this->db = $db;
    }

    public function getRecords(){
        return $this->db->table(self::Table)->where("publish",1);
    }

    public function getNewsByTagID($tagId){
        return $this->db->table(self::Table)->where("id_tags", $tagId);
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
