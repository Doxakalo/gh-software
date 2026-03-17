<?php

namespace App\Presenters;

use App\Forms\ContactForm;
use App\Forms\SubscribeForm;
use App\Forms\ShortlinkForm;
use App\Model\Db\NewsModel;
use App\Model\Db\NewsOGMediaModel;
use App\Model\Db\NewsTagsModel;
use App\Model\Db\SearchTermsLog;
use App\Model\Db\TagsModel;
use App\Service\Config\ApplicationConfig;
use Contributte\Translation\Translator; use Contributte;

use Nette\Database\Table\ActiveRow;
use Nette\Forms\Form;

class NewsPresenter extends BasePresenter
{
    private $newsModel;
    private $tagsModel;
    private $newsTagsModel;
    private $newsOGMediaModel;
    private $searchTermsLog;
    //private $contactFormSentData;
    private $shortlinkForm;

    public function __construct(SubscribeForm $subscribeForm, Translator $translator, ApplicationConfig $applicationConfig, NewsModel $newsModel, TagsModel $tagsModel, NewsTagsModel $newsTagsModel, SearchTermsLog $searchTermsLog, NewsOGMediaModel $newsOGMediaModel, ContactForm $contactForm, ShortlinkForm $shortlinkForm)
    {
        parent::__construct($subscribeForm, $translator, $applicationConfig, $contactForm);
        $this->newsModel = $newsModel;
        $this->tagsModel = $tagsModel;
        $this->newsTagsModel = $newsTagsModel;
        $this->searchTermsLog = $searchTermsLog;
        $this->newsOGMediaModel = $newsOGMediaModel;
        $this->newsOGMediaModel = $newsOGMediaModel;
        $this->contactForm = $contactForm;
        $this->shortlinkForm = $shortlinkForm;

    }


    protected function createComponentContactAdditionalInformationForm()
    {
        $form = $this->contactForm->create();

        if(isset($this->customPageName)){
            $this->contactForm->setCurrentPage($this->presenter->getName().' ['.$this->customPageName.']');
        }else{
            $this->contactForm->setCurrentPage($this->presenter->getName());
        }
        $this->contactForm->setNumberPrefix($this->numberPrefix());

        $form = $this->addTimezones($form);

        $form["type"]->setDefaultValue("call");
        $form["predata"]->setDefaultValue(serialize($this->getContactFormSentData()));
        $form["email"]->setRequired($this->translator->translate(ContactForm::TRANSLATE_PATH . "items.email.validation.required"))
            ->addRule(Form::Email, $this->translator->translate(ContactForm::TRANSLATE_PATH . "items.email.validation.valid"));
        $form["timezones"]->setRequired($this->translator->translate(ContactForm::TRANSLATE_PATH . "items.timezone.validation.required"))
            ->setPrompt($this->translator->translate(ContactForm::TRANSLATE_PATH . "items.timezone.placeholder"))->setDefaultValue(null);

        $form->onError[] = function () {
            $this->redrawControl('contactAdditionalInformationForm');
        };

        $form->onSuccess[] = function () {
            $this->template->contactAdditionalInformationSuccess = true;
            $this->redrawControl('contactForm');
        };

        return $form;
    }

    protected function createComponentContactForm()
    {
        $form = $this->contactForm->create();

        if(isset($this->customPageName)){
            $this->contactForm->setCurrentPage($this->presenter->getName().' ['.$this->customPageName.']');
        }else{
            $this->contactForm->setCurrentPage($this->presenter->getName());
        }

        $this->contactForm->setNumberPrefix($this->numberPrefix());
        $form = $this->addNumberPrefix($form);

        $form["type"]->setDefaultValue("call");

        $form["name"]->setRequired($this->translator->translate(ContactForm::TRANSLATE_PATH . "items.name.validation.required"));
        $form["number"]->setRequired($this->translator->translate(ContactForm::TRANSLATE_PATH . "items.number.validation.required"));
        $form["topic"]->setRequired($this->translator->translate(ContactForm::TRANSLATE_PATH . "items.topic.validation.required"));
        $form["time"]->setRequired($this->translator->translate(ContactForm::TRANSLATE_PATH . "items.time.validation.required"));

        $form->onSuccess[] = function ($form) {
            $this->template->contactFormSuccess = true;
            $this->setContactFormSentData($form->getValues());
            $this->redrawControl('contactForm');
        };

        $this->redrawControl('contactForm');

        return $form;
    }

    /**
     * @param mixed $contactFormSentData
     */
    public function setContactFormSentData($contactFormSentData)
    {
        $this->contactFormSentData = $contactFormSentData;
    }

    /**
     * @return mixed
     */
    public function getContactFormSentData()
    {
        return $this->contactFormSentData;
    }



    public function actionDetail($url)
    {
        try {
            $news = $this->newsModel->getNewsByURL($url);
            if(empty($news)){
                $this->redirect("News:list");
            }

            $this->subscribeForm->setListTag($news->mailchimp_tag);
            $newsTags = $news->related('sys_news_tags');

            $this->customPageName = $news->name;

            $newsTagsIds = [];
            foreach ($newsTags as $newsTag) {
                $newsTagsIds[] = $newsTag->id_tags;
            }
            $tags = $this->tagsModel->getTagsByNewsIds($newsTagsIds);
            $this->template->tags = $tags;
            $this->template->news = $news;
            $ogMedia = $this->newsOGMediaModel->getOGMediaByNewsID($news->id);
            $this->template->systemURL = $this->applicationConfig->getConfig()["system-url"];

            $this->template->ogMedia = $ogMedia;
            $this->template->templateCustomName = "news-detail";
            $this->template->imageLink = $this->link("//News:image", $news->id);
            $this->template->shareOnTwitter = (isset($_GET["utm_source"]) && $_GET["utm_source"] === "Twitter" ? true : false);
            $linkToRedirect = null;

            if ($news->link_intern !== "" && $news->link_intern !== null) {
                $linkToRedirect = $news->link_intern;
            } elseif ($news->link_extern !== "" && $news->link_extern !== null) {
                $linkToRedirect = $news->link_extern;
            }

            $hashtags = "";
            foreach ($tags as $tag) {
                $hashtags .= "#" . $tag->name . " ";
            }

            $cookie_name = $this->applicationConfig->getConfig()['shortlinks']['admin_cookie_name'];
            $cookie_value = $this->httpRequest->getCookie($cookie_name);

            if($cookie_value == $this->applicationConfig->getConfig()['shortlinks']['active_cookie_hash']){
                $article_shortlinks_admin = 1;
            } else {
                $article_shortlinks_admin = 0;
            }


            $this->template->hashtagString = $hashtags;
            $this->template->article_shortlinks = $this->getArticleShortlinks();
            $this->template->article_shortlinks_admin = $article_shortlinks_admin;
            $this->template->pageType = "news_article";
            $this->template->redirectOnExtern = ($linkToRedirect !== null ? $linkToRedirect : false);
            if ($linkToRedirect !== null) {
                $this->template->ignoreSearch = "1";
            }

        } catch (\Exception $e) {
            $this->redirect("News:list");
        }
    }

    private function strContainsAll($haystack, array $needles)
    {
        foreach ($needles as $needle) {
            if (strpos($haystack, $needle) === false) {
                return false;
            }
        }
        return true;
    }

    public function actionImageOG($id, $newsId)
    {
        $image = $this->newsOGMediaModel->getRecord($id);
        if (!empty($image)) {
            $systemURL = $this->applicationConfig->getConfig()["system-url"];

            $img = file_get_contents($systemURL.'/uploaded-images/og-images/'.$newsId.'/'.$image->filename);

            header('Content-type: '. $image->type);
            echo $img;
        } else {
            exit();
        }
    }

    public function actionImage($id)
    {

        $news = $this->newsModel->getRecord($id);

        switch ($news->preview_image_ext) {
            case "gif":
                $ctype = "image/gif";
                break;
            case "png":
                $ctype = "image/png";
                break;
            case "jpeg":
            case "jpg":
                $ctype = "image/jpeg";
                break;
            default:
        }

        header('Content-type: ' . $ctype);

        echo base64_decode($news->preview_image_base64);
        exit();
    }

    public function actionShareNews($type)
    {
        $url = urldecode($_GET["url"]);
        $allowTypes = ["facebook", "twitter", "linkedin"];

        if (!in_array($type, $allowTypes)) {
            $this->redirect("News:list");
        }

        $news = $this->newsModel->getNewsByShareURL($type, $url);

        $newsTags = $news->related('sys_news_tags');

        $newsTagsIds = [];
        foreach ($newsTags as $newsTag) {
            $newsTagsIds[] = $newsTag->id_tags;
        }
        $tags = $this->tagsModel->getTagsByNewsIds($newsTagsIds);

        $this->template->tags = $tags;
        $this->template->news = $news;
        $this->template->imageLink = $this->link("//News:image", $news->id);

        $this->template->type = $type;
        $this->template->shortlink = $url;
    }

    public function actionList()
    {
        $newsList = array();
        $searchTerm = false;

        //try {
        if (isset($_GET["search"])) {
            $newsList = $this->newsModel->getRecords();

            $searchString = mb_strtolower(strip_tags(trim($_GET["search"])));
            $searchTermsArray = explode(" ", $searchString);

            if (!empty($searchString)) {
                // LOG TERM
                $this->logSearchTerm("news", $searchString);

                foreach ($newsList as $id => $news) {
                    $newsContent = mb_strtolower(strip_tags($news->name));
                    $newsContent .= mb_strtolower(strip_tags($news->preview_description));
                    $newsContent .= mb_strtolower(strip_tags($news->content));

                    $allTermsFind = $this->strContainsAll($newsContent, $searchTermsArray);

                    if ($allTermsFind === false) {
                        unset($newsList[$id]);
                    }
                }
            }
            $searchTerm = $_GET["search"];

        } else if (isset($_GET["tag"])) {
            $tagString = strtolower(strip_tags(trim($_GET["tag"])));
            $tag = $this->tagsModel->getRecordByTagName($tagString);

            $this->logSearchTerm("tag", $tagString);

            if ($tag !== false) {
                $ids = [];
                $newsTagList = $this->newsTagsModel->getNewsByTagID($tag->id);
                foreach ($newsTagList as $newsTag) {
                    $ids[] = $newsTag->id_news;
                }

                $newsList = $this->newsModel->getRecordsByIds($ids);

            }
            $searchTerm = $_GET["tag"];

        } else {
            $newsList = $this->newsModel->getRecords();
        }

        $newsHashtags = [];
        foreach ($newsList as $id => $news) {
            $newsTags = $news->related('sys_news_tags');

            $newsTagsIds = [];
            foreach ($newsTags as $newsTag) {
                $newsTagsIds[] = $newsTag->id_tags;
            }
            $newsHashtags[$news->id] = $this->tagsModel->getTagsByNewsIds($newsTagsIds);
        }
        $this->template->newsHashtags = $newsHashtags;
        /*} catch (\Exception $e) {
            $this->template->systemUnavailable = true;
        }*/

        $this->template->searchTerm = $searchTerm;
        $this->template->newsList = $newsList;
        $this->template->ignoreSearch = "1";
    }

    private function logSearchTerm($type, $searchString){
        // NO LOGGING FOR SEARCHES FROM SERVER IP ADRESS
        if($this->httpRequest->getRemoteAddress() === $_SERVER["SERVER_ADDR"]) return false;
        
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
        $this->searchTermsLog->createRecord([
            "type" => $type,
            "term" => $searchString,
            "page" => $urlPathReferer,
            "withdraw_stats_cookie" => intval($this->httpRequest->getCookie(self::STATS_COOKIE)),
            "remote_ip" => $this->httpRequest->getRemoteAddress(),
        ]);
    }

    private function getArticleShortlinks(){
        $actualLink = (substr($_SERVER['HTTP_HOST'], 0, 3) === 'www' ? substr($_SERVER['HTTP_HOST'], 4) : $_SERVER['HTTP_HOST']) . $_SERVER['REQUEST_URI'];
        $shortlinkApiResult = @file_get_contents($this->applicationConfig->getConfig()["shortlinks-url"].'/?action=get_shortlinks_by_url&url='.$actualLink);

        if($shortlinkApiResult && $shortlinkApiResult != "ERROR") {
            $articleShortlinks = json_decode($shortlinkApiResult, true);

            return $articleShortlinks;
        }
    }

    protected function createComponentSubscribeTipsForm()
    {
        $form = $this->subscribeForm->create();

        if($this->subscribeForm->getListTag() == NULL){
            $this->subscribeForm->setListTag("NEWS");
        }
        /** @var \Nette\Forms\Controls\TextInput $formListId */
        $formListId = $form["listId"];
        $formListId->setDefaultValue($this->subscribeForm->mailChimpModel->config["lists"]["24UGENERIC_".strtoupper($this->locale)]["id"]);

        $form->onSuccess[] = function () {
            $this->template->resultType = (in_array($this->subscribeForm->getResult(), [SubscribeForm::RESULT_USER_RESUBSCRIBE, SubscribeForm::RESULT_NEW_USER]) ? "send_email" : "no_send_email");
            $this->template->stFormSuccess = true;
        };

        $this->redrawControl('stForm');


        return $form;
    }

    protected function createComponentShortlinkForm()
    {
       
        $form = $this->shortlinkForm->create();
        /** @var \Nette\Forms\Controls\TextInput $formUrl */
        $formUrl = $form['url'];
        $formUrl->setDefaultValue((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']);

        /** @var \Nette\Forms\Controls\TextInput $formNote */
        $formNote = $form['note'];    
        $formNote->setDefaultValue("");

        /** @var \Nette\Forms\Controls\TextInput $formCategory */
        $formCategory = $form['category'];
        $formCategory->setDefaultValue("Sociální sítě");

        /** @var \Nette\Forms\Controls\TextInput $formMinClick */
        $formMinClick = $form['min_click'];
        $formMinClick->setDefaultValue("10");

        /** @var \Nette\Forms\Controls\TextInput $formMaxClick */
        $formMaxClick = $form['max_click'];
        $formMaxClick->setDefaultValue("20");

        /** @var \Nette\Forms\Controls\TextInput $formDaysLimit */
        $formDaysLimit = $form['days_limit'];
        $formDaysLimit->setDefaultValue("7");

        /** @var \Nette\Forms\Controls\TextInput $formExpectedString */
        $formExpectedString = $form['expected_string'];
        $formExpectedString->setDefaultValue(""); //TODO

        /** @var \Nette\Forms\Controls\HiddenField $formFlagMonitored */
        $formFlagMonitored = $form['flag_monitored'];
        $formFlagMonitored->setDefaultValue("1");

        $form->onSuccess[] = function () {
            $this->template->shortlinkForm = true;
            $this->template->article_shortlinks = $this->getArticleShortlinks();
            $this->redrawControl('shortlinkList');
            $this->redrawControl('shortlinkForm');
        };

        $this->redrawControl('shortlinkForm');

        return $form;
    }
}
