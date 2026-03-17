<?php

namespace App\Model\Db;

use Nette\Database\Table\ActiveRowtable,
    Nette\Database\Explorer;

class LogErrorRequestModel
{
    const Table = "log_error_request";

    private $db;

    public function __construct(Explorer $db)
    {
        $this->db = $db;
    }

    public function createRecord($data){
        return $this->db->table(self::Table)->insert($data);
    }
}
