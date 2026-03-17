<?php

namespace App\Presenters;

use App\Forms\ContactForm;
use App\Forms\SubscribeForm;
use App\Service\Config\ApplicationConfig;
use Contributte\Translation\Translator; use Contributte;

use Nette\Forms\Form;

class CustomAppsPresenter extends BasePresenter
{

    // OTHER
    //private $contactFormSentData;

    public function __construct(SubscribeForm $subscribeForm, Translator $translator, ApplicationConfig $applicationConfig, ContactForm $contactForm)
    {
        parent::__construct($subscribeForm, $translator, $applicationConfig, $contactForm);
        $this->contactForm = $contactForm;
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
            ->addRule(Form::EMAIL, $this->translator->translate(ContactForm::TRANSLATE_PATH . "items.email.validation.valid"));
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


    public function actionDefault()
    {
        $this->template->pageType = "service_page";
        $this->template->templateCustomName = "custom-apps";
    }
}
