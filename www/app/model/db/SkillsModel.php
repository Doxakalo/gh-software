<?php

namespace App\Model\Db;

use Nette\Database\Table\ActiveRowtable,
    Nette\Database\Explorer;

class SkillsModel
{
    const Skill_Table = "skills";
    const Skill_Content_Table = "skill_content";

    private $db;

    public function __construct(Explorer $db)
    {
        $this->db = $db;
    }

    public function getRecordsByLang($lang){
        return $this->db->table(self::Skill_Content_Table)->where("id_lang", $lang)->where("skill.visible",1)->order("skill.skill_order DESC")->fetchAll();
    }

    public function getRecordsByURL($url){
        return $this->db->table(self::Skill_Content_Table)->where("url", $url)->fetch();
    }
}
