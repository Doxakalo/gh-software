<?php

namespace App\Model\Db;

use Nette\Database\Table\ActiveRowtable,
    Nette\Database\Explorer;

class SearchPagesContentModel
{
    const Table = "search_pages_content";

    private $db;

    public function __construct(Explorer $db)
    {
        $this->db = $db;
    }

    public function createRecord($data){
        return $this->db->table(self::Table)->insert($data);
    }

    public function truncateTable(){
        return $this->db->table(self::Table)->delete();
    }

    public function getRecords(){
        return $this->db->table(self::Table)->fetchAll();
    }

    public function getRecordsByLang($locale){
        return $this->db->table(self::Table)->where('language ?', $locale)->fetchAll();
    }

    public function getRecord($id){
        return $this->db->table(self::Table)->get($id);
    }


    public function optimizeTableSearch(){
        return $this->db->query("OPTIMIZE TABLE " . self::Table);
    }

    public function searchTerm($term, $locale){
        return $this->db->query("SELECT 
                                      id,
                                      title,
                                      headline,
                                      url,
                                      content,
                                      type,
                                      language_content,
                                      language,
                                      score_multiplier,
                                      MATCH(title) AGAINST(? IN NATURAL LANGUAGE MODE) AS title_score,
                                      MATCH(headline) AGAINST(? IN NATURAL LANGUAGE MODE) AS headline_score,
                                      MATCH(content) AGAINST(? IN NATURAL LANGUAGE MODE) AS content_score,
                                      (1.5 * MATCH(title) AGAINST(? IN NATURAL LANGUAGE MODE) +
                                       1.2 * MATCH(headline) AGAINST(? IN NATURAL LANGUAGE MODE) +
                                       1.0 * MATCH(content) AGAINST(? IN NATURAL LANGUAGE MODE)
                                      ) * score_multiplier AS rank_score
                                    FROM search_pages_content
                                    WHERE 
                                        language = '" . $locale . "' AND
                                      MATCH(title, headline, content) AGAINST(? IN NATURAL LANGUAGE MODE)
                                    ORDER BY rank_score DESC;", $term, $term, $term, $term, $term, $term, $term);
    }
}
