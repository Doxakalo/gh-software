<?php

namespace App\Model\Db;

use Nette\Database\Table\ActiveRowtable,
    Nette\Database\Explorer;

class FormsLogModel
{
    const Table = "forms_log";

    private $db;

    public function __construct(Explorer $db)
    {
        $this->db = $db;
    }

    public function createRecord($data){
        return $this->db->table(self::Table)->insert($data);
    }

    public function getRecordsByCriterions($data){
        return $this->db->table(self::Table)->where($data)->fetchAll();
    }

    public function markLogAsSpam($id){
        return $this->db->table(self::Table)->where("id", $id)->update(["marked_as_spam" => 1]);
    }

    public function evaluateLogAsSpam($id){
        return $this->db->table(self::Table)->where("id", $id)->update(["evaluated_as_spam" => 1]);
    }
}
