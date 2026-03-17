<?php

namespace App\Presenters;

use App\Forms\ContactForm;
use App\Forms\SubscribeForm;
use App\Model\Db\SearchPagesContentModel;
use App\Model\Db\SearchTermsLog;
use App\Service\Config\ApplicationConfig;
use Contributte;
use Contributte\Translation\Translator;

class SearchPresenter extends BasePresenter
{
    public $searchPagesContentModel;
    private $searchTermsLog;

    public function __construct(SubscribeForm $subscribeForm, Translator $translator, ApplicationConfig $applicationConfig, SearchPagesContentModel $searchPagesContentModel, SearchTermsLog $searchTermsLog, ContactForm $contactForm)
    {
        parent::__construct($subscribeForm, $translator, $applicationConfig, $contactForm);
        $this->searchPagesContentModel = $searchPagesContentModel;
        $this->searchTermsLog = $searchTermsLog;
    }

    public function actionDefault()
    {
        $pagesCount = 0;

        $searchStringRaw = $_GET["search"];
        $searchString = mb_strtolower(strip_tags(trim($searchStringRaw)));

        if (!empty($searchString)) {
            foreach($this->applicationConfig->getConfig()["search"]["typos"] as $correctWord => $typos){
                // REPLACE TYPOS IN SEARCH TERMS
                $pattern = '/\b(' . implode('|', array_map('preg_quote', $typos)) . ')\b/i';
                $searchString = preg_replace($pattern, $correctWord, $searchString);
            }

            // LOG TERM
            $url = null;
            $urlPathReferer = null;
            if (isset($_SERVER["HTTP_REFERER"]) && !empty($_SERVER["HTTP_REFERER"])) {
                $url = $_SERVER["HTTP_REFERER"];
            } else {
                $url = $_SERVER["REQUEST_URI"];
            }

            if ($url !== null) {
                $parsedRefererURL = parse_url($url);
                $urlPathReferer = $parsedRefererURL["path"];
            }

            // NO LOGGING FOR SEARCHES FROM SERVER IP ADRESS
            if($this->httpRequest->getRemoteAddress() !== $_SERVER["SERVER_ADDR"]) {
                $this->searchTermsLog->createRecord([
                    "type" => "global",
                    "term" => $searchString,
                    "page" => $urlPathReferer,
                    "withdraw_stats_cookie" => intval($this->httpRequest->getCookie(self::STATS_COOKIE)),
                    "remote_ip" => $this->httpRequest->getRemoteAddress(),
                ]);
            }


            $pages = $this->searchPagesContentModel->searchTerm($searchString, $this->locale);
            $pagesCount = $pages->getRowCount();
        }

        $this->template->switchLangLinkCS = $this->link('Search:default', ["locale" => "cs", "search" => $searchString]);
        $this->template->switchLangLinkEN = $this->link('Search:default', ["locale" => "en", "search" => $searchString]);

        $this->template->terms = $searchStringRaw;
        $this->template->pages = $pages;
        $this->template->pagesCount = $pagesCount;

    }
}
