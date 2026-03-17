<?php

namespace App\Presenters;

use App\Forms\ContactForm;
use App\Forms\SubscribeForm;
use App\Service\Config\ApplicationConfig;
use Contributte\Translation\Translator; use Contributte;

class PrivacyPolicyPresenter extends BasePresenter
{
    public function __construct(SubscribeForm $subscribeForm, Translator $translator, ApplicationConfig $applicationConfig, ContactForm $contactForm)
    {
        parent::__construct($subscribeForm, $translator, $applicationConfig, $contactForm);
    }

    public function actionDefault()
    {
        $this->template->pageType = "legal_info";
    }
}
