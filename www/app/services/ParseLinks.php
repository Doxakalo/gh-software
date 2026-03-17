<?php

namespace App\Service;

use PHPHtmlParser\Dom;
use PHPHtmlParser\Exceptions\CurlException;

class ParseLinks
{
    private $sRootLink;
    private $iCountOfPages;

    private $linkArray = array();
    private $iDeep;
    private $sDomain;
    private $sScheme;

    public function __construct($sRootLink, $iCountOfPages)
    {
        $this->sRootLink = $sRootLink;
        $this->iCountOfPages = $iCountOfPages;
        $this->iDeep = 0;
        $this->sDomain = "";
        $this->sScheme = "";
    }

    public function getAllLinks()
    {
        $this->recParseLinks($this->sRootLink);
        return $this->linkArray;
    }

    private function recParseLinks($link)
    {
        try {
            if (strlen($link) <= 1)
                return;

            if ($this->iDeep == 0) {
                $d = parse_url($link);

                if ($d != false) {
                    $this->sDomain = $d['host'];
                    $this->sScheme = $d['scheme'];
                } else
                    return;
            }

            $this->iDeep++;
            $dom = new Dom;
            $dom->loadFromUrl($link);
            $elements = $dom->getElementsByTag('a');

            foreach ($elements as $element) {
                if (count($this->linkArray) >= $this->iCountOfPages)
                    return;

                $links = $element->getAttribute('href');


                if ($links == '/' || $links == '?' || strpos($links, 'http') === false)
                    $links = $this->sScheme . "://" . $this->sDomain . $links;

                $p_links = parse_url($links);
                if ($p_links == FALSE)
                    continue;

                if (isset($p_links["host"]) && $p_links["host"] != $this->sDomain)
                    continue;

                if (!$this->linkExists($links) && strlen($links) > 1) {
                    $this->linkArray[] = $links;

                    if ($this->iDeep < 30) {
                        $this->recParseLinks($links);
                    }
                }
            }
            $this->iDeep--;
        } catch (CurlException $e){
            echo "Error :" . $e->getMessage();
        }
    }

    private function linkExists($link)
    {
        foreach($this->linkArray as $element)
            if($element == $link)
                return true;

        return false;
    }
}
