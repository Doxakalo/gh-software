<?php

namespace App\Presenters;

use App\Forms\ContactForm;
use App\Forms\SubscribeForm;
use App\Service\Config\ApplicationConfig;

use Contributte\Translation\Translator; use Contributte;

class CookiePresenter extends BasePresenter
{
    public function __construct(SubscribeForm $subscribeForm, Translator $translator, ApplicationConfig $applicationConfig, ContactForm $contactForm)
    {
        parent::__construct($subscribeForm, $translator, $applicationConfig, $contactForm);
    }

    public function actionCookie()
    {
        $this->template->ignoreSearch = "1";
        $this->template->cookie = [
            'name' => self::STATS_COOKIE,
            'value' => $this->httpRequest->getCookie(self::STATS_COOKIE),
        ];
    }

    public function actionSetCookie()
    {
        $this->setCookie($this->httpRequest->getQuery('cookie_val'));
        $this->redirect("Cookie:cookie");

    }

    public function setCookie($cookieValue = true)
    {
        $this->httpResponse->setCookie(self::STATS_COOKIE, $cookieValue, '100 years', '/');
    }


    public function actionSetExpirationCookie(){
        $cookieName = "cc_cookie";

        if(isset($_COOKIE[$cookieName])){
            if(isset($_POST["expiration"])){
                $dataToSave = ($_POST["action"] === "update" ? json_encode($_POST) : $_COOKIE[$cookieName] );
                $expiration = $_POST["expiration"];
                //$expiration = 5;

                $this->httpResponse->deleteCookie($cookieName,"/",  "." . $_SERVER["HTTP_HOST"]);

                setcookie($cookieName, $dataToSave, time() + (86400 * $expiration), "/");
            }
        }
        exit();
    }



    public function actionShortlinkAdminCookie()
    {
        $this->template->ignoreSearch = "1";
        $cookie_name = $this->applicationConfig->getConfig()['shortlinks']['admin_cookie_name'];
        $cookie_value = $this->httpRequest->getCookie($cookie_name);

        if($cookie_value == $this->applicationConfig->getConfig()['shortlinks']['active_cookie_hash']){
            $cookie_value = '1';
        } else {
            $cookie_value = '0';
        }
        $this->template->shortlink_cookie = [
            'name' => $cookie_name,
            'value' => $cookie_value,
        ];
    }

    public function actionSetShortlinkAdminCookie()
    {
        $this->setShortlinkAdminCookie($this->httpRequest->getQuery('cookie_val'));
        $this->redirect("Cookie:shortlinkAdminCookie");
    }

    public function setShortlinkAdminCookie($cookieValue = true)
    {

        if($cookieValue == 1) {
            $cookieValue = $this->applicationConfig->getConfig()['shortlinks']['active_cookie_hash'];
        }

        $this->httpResponse->setCookie($this->applicationConfig->getConfig()['shortlinks']['admin_cookie_name'], $cookieValue, '1 years', '/');
    }
}
