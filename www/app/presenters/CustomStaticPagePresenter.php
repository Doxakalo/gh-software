<?php

namespace App\Presenters;

use App\Forms\ContactForm;
use App\Forms\SubscribeForm;
use App\Service\Config\ApplicationConfig;
use Contributte\Translation\Translator; use Contributte;

class CustomStaticPagePresenter extends BasePresenter
{
    public function __construct(SubscribeForm $subscribeForm, Translator $translator, ApplicationConfig $applicationConfig, ContactForm $contactForm)
    {
        parent::__construct($subscribeForm, $translator, $applicationConfig, $contactForm);
    }

    public function actionXgodeTerms()
    {
        $this->template->pageType = "legal_info";
        $this->template->templateCustomName = "privacypolicy";
    }

    public function actionGdpr()
    {
        $this->template->pageType = "legal_info";
        $this->template->templateCustomName = "privacypolicy";
    }

    public function actionOou()
    {
        $this->template->pageType = "legal_info";
        $this->template->templateCustomName = "privacypolicy";
    }
}
