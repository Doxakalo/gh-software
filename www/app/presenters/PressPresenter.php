<?php

namespace App\Presenters;

use App\Forms\ContactForm;
use App\Forms\SubscribeForm;
use App\Service\Config\ApplicationConfig;
use Contributte\Translation\Translator; use Contributte;

class PressPresenter extends BasePresenter
{
    public function __construct(SubscribeForm $subscribeForm, Translator $translator, ApplicationConfig $applicationConfig, ContactForm $contactForm)
    {
        parent::__construct($subscribeForm, $translator, $applicationConfig, $contactForm);
    }

    public function actionList()
    {
        $this->template->ignoreSearch = "1";
    }

    public function actionDetail($id)
    {
        $this->template->pageType = "press_release";
        $this->template->id = $id;
    }
}
