<?php

namespace App\Model\Db;

use Nette\Database\Table\ActiveRowtable,
    Nette\Database\Explorer;

class SearchTermsLog
{
    const Table = "search_terms_log";

    private $db;

    public function __construct(Explorer $db)
    {
        $this->db = $db;
    }

    public function createRecord($data){
        return $this->db->table(self::Table)->insert($data);
    }
}
