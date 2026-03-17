<?php

namespace App\Model\Db;

use Nette\Database\Table\ActiveRowtable,
    Nette\Database\Explorer;

class NewsModel
{
    const Table = "sys_news";

    private $db;

    public function __construct(Explorer $db)
    {
        $this->db = $db;
    }

    public function getRecords()
    {
        return $this->db->table(self::Table)->where("publish", 1)->order("published DESC");
    }

    public function getNewsByURL($url)
    {
        return $this->db->table(self::Table)->where("url", $url)->fetch();
    }

    public function getNewsByShareURL($type, $url)
    {
        return $this->db->table(self::Table)->where("shortlink_" . $type, $url)->fetch();
    }

    public function getRecordsByIds($ids)
    {
        return $this->db->table(self::Table)->where("id", $ids)->where("publish", 1)->order("published DESC");
    }

    public function getRecord($id)
    {
        return $this->db->table(self::Table)->get($id);
    }

    public function deleteRecord($id)
    {
        return $this->db->table(self::Table)->where("id", $id)->delete();
    }

    public function truncateTable()
    {
        return $this->db->table(self::Table)->delete();
    }

    public function getNewsByTag($tag)
    {

    }
}
