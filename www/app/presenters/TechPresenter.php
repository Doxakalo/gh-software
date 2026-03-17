<?php

namespace App\Presenters;

use App\Model\Db\FormsLogModel;
use App\Model\Db\SearchPagesContentModel;
use App\Service\Application;
use Nette\Application\UI\Presenter;
use PHPHtmlParser\Dom;
use PHPHtmlParser\Exceptions\CurlException;

final class TechPresenter extends Presenter
{
    private $application;

    // MODELS
    private $searchPagesContentModel;
    private $formsLogModel;

    public function __construct(Application $application, SearchPagesContentModel $searchPagesContentModel, FormsLogModel $formsLogModel)
    {
        $this->application = $application;
        $this->searchPagesContentModel = $searchPagesContentModel;
        $this->formsLogModel = $formsLogModel;
    }

    private function setGenerateSearchInProcess()
    {
        file_put_contents(dirname(dirname(__DIR__)) . '/www/private-data/generate-search-process.txt', 1);
    }

    private function setGenerateSearchNoNInProcess()
    {
        file_put_contents(dirname(dirname(__DIR__)) . '/www/private-data/generate-search-process.txt', 0);
    }

    private function isGenerateSearchInProcess()
    {
        $generateSearchInProcess = @file_get_contents(dirname(dirname(__DIR__)) . '/www/private-data/generate-search-process.txt');

        if ($generateSearchInProcess !== false) {
            if (intval($generateSearchInProcess) === 0) {
                return false;
            }
            return true;
        } else {
            return false;
        }
    }

    public function clearHTMLCode($str)
    {
        $str = preg_replace('#<[^>]+>#', ' ', $str);
        $str = trim(strip_tags($str));
        $str = preg_replace('/\s+/', ' ', $str);

        return $str;
    }

    public function actionMarkContactLogAsSpam($id)
    {
        try {
            $this->formsLogModel->markLogAsSpam($id);
            $this->template->message = "Označeno jako spam";
        } catch (\Exception $e) {
            $this->template->message = $e->getMessage();
        }
    }


    public function actionGenerateSearchRecords()
    {
        set_time_limit(60 * 30);
        ini_set('memory_limit', '-1');

        $ignoreExtensions = ["jpg", "jpeg", "png", "gif", "svg", "zip", "dmg", "pdf", "mp4", "mov", "css", "webm", "fmp12", "exe", "msi", "txt", "js", "html"];
        $ignoreURlContains = ["mailto", "javascript", "tel", "#", "url-convert-redirect", "/news/image/", "tracy_bar", "image/png", ".html", "nette", "latte", "%5c%22", "/benchtest/results", ".com/www", ".cz/www"];
        $links = $this->application->getAllWebsiteLink($this->application->applicationConfig->getConfig()["url"], $ignoreExtensions, $ignoreURlContains);

        $this->searchPagesContentModel->truncateTable();
        $errors = [];
        $ignore = [];
        $add = [];


        foreach ($links as $link) {
            $link = htmlentities($link);

            try {
                $dom = new Dom;
                $dom->loadFromUrl($link);

                $html = $dom->find('html');

                $ignoreSearch = filter_var($html->getAttribute('data-ignore-search'), FILTER_VALIDATE_BOOLEAN);
                $scoreMultiplier = 1;
                $scoreMultiplerAttrName = "data-search-priority";
                $scoreMultiplierRawEl = $dom->find('*['.$scoreMultiplerAttrName.']')[0];
                if(!empty($scoreMultiplierRawEl)){
                    $scoreMultiplierCustomerInput = filter_var($scoreMultiplierRawEl->getAttribute($scoreMultiplerAttrName), FILTER_VALIDATE_INT);
                    if($scoreMultiplierCustomerInput !== false){
                        $scoreMultiplier = $scoreMultiplierCustomerInput;
                    }
                }

                if ($ignoreSearch === false) {
                    $title = $dom->find('title')->text;
                    $mainHeadline = $dom->find('h1')->innerHtml;
                    $mainContent = $dom->find('#content')->innerHtml;
                    $type = $html->getAttribute('data-page-type');
                    $language = $html->getAttribute('lang');
                    $languageContent = $html->getAttribute('lang-content');

                    // REPLACE ALL NEW LINES AND BREAK LINES FOR SPACES
                    $mainHeadline = preg_replace("/\r\n|\r|\n/", ' ', $mainHeadline);
                    $mainHeadline = preg_replace('/<br\s*\/?>/i', ' ', $mainHeadline);
                    $mainHeadline = preg_replace('/\s+/', ' ', $mainHeadline);

                    $this->searchPagesContentModel->createRecord([
                        "title" => $title,
                        "type" => (!empty($type) ? preg_replace('/\s+/', ' ', $type) : null),
                        "content" => $this->clearHTMLCode($mainContent),
                        "headline" => $this->clearHTMLCode($mainHeadline),
                        "language" => $language,
                        "language_content" => $languageContent,
                        "url" => $link,
                        "score_multiplier" => $scoreMultiplier,

                    ]);
                    $add[] = $link;
                } else {
                    $ignore[] = $link;
                }
            } catch (CurlException $e) {
                $errors[] =  "Error1: - " . $link;
            } catch (\TypeError $e) {
                $errors[] =  "Error2: - " . $link;
            } catch (\Exception $e) {
                $errors[] =  "Error3: - " . $link;
            }
        }

        $this->searchPagesContentModel->optimizeTableSearch();

        header("Content-Type: text/plain");

        echo "------ ADD ------" . "\n";
        print_r($add);

        echo "------ IGNORE ------" . "\n";
        print_r($ignore);

        echo "------ ERRORS ------" . "\n";
        print_r($errors);

        exit();
    }
}
