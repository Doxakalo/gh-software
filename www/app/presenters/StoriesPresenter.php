<?php

namespace App\Presenters;

use App\Forms\ContactForm;
use App\Forms\SubscribeForm;
use App\Model\Db\SkillsModel;
use App\Service\Config\ApplicationConfig;
use Contributte\Translation\Translator; use Contributte;

use Nette\Forms\Form;

class StoriesPresenter extends BasePresenter
{

    // OTHER
    //private $contactFormSentData;

    // MODELS
    private $skillsModel;

    public function __construct(SubscribeForm $subscribeForm, Translator $translator, ApplicationConfig $applicationConfig, ContactForm $contactForm, SkillsModel $skillsModel)
    {
        parent::__construct($subscribeForm, $translator, $applicationConfig, $contactForm);
        $this->contactForm = $contactForm;
        $this->skillsModel = $skillsModel;
    }

    public function actionDetail()
    {

    }

    public function actionSkillDetail($url, $backlink = null)
    {

        $skill = $this->skillsModel->getRecordsByURL($url);
        $this->customPageName = "Our Skills / " . $skill->title;

        $this->template->templateCustomName = "news-detail";
        $this->template->isTranslated = true;
        $this->template->backlink = $backlink;

        $this->template->skill = $skill;
        $this->template->ignoreSearch = "1";
    }

    public function actionList()
    {
        $this->customPageName = "Stories/Our Skills";

        $skills = $this->skillsModel->getRecordsByLang($this->locale);
        $this->template->skills = $skills;
        $this->template->selfLink = $this->link('this');


        $this->template->ignoreSearch = "1";
    }

    public function actionSynetrans()
    {
        $this->customPageName = "Synetrans";
        $this->template->templateCustomName = "stories-detail";
        $this->template->pageType = "customer_story";
    }

    public function actionAdiutor()
    {
        $this->customPageName = "Adiutor";
        $this->template->templateCustomName = "stories-detail";
        $this->template->pageType = "customer_story";
    }

    public function actionJogs()
    {
        $this->customPageName = "Jogs";
        $this->template->templateCustomName = "stories-detail";
        $this->template->pageType = "customer_story";
    }

    public function actionEwemanage()
    {
        $this->customPageName = "Ewemanage";
        $this->template->templateCustomName = "stories-detail";
        $this->template->pageType = "customer_story";
    }

    public function actionBreedbook()
    {
        $this->customPageName = "Breedbook";
        $this->template->templateCustomName = "stories-detail";
        $this->template->pageType = "customer_story";
    }

    public function actionDartis()
    {
        $this->customPageName = "Dartis";
        $this->template->templateCustomName = "stories-detail";
        $this->template->pageType = "customer_story";
    }


    public function actionJennpro()
    {
        $this->customPageName = "Jennpro";
        $this->template->templateCustomName = "stories-detail";
        $this->template->pageType = "customer_story";
    }
    public function actionJennpro2()
    {
        $this->customPageName = "Jennpro";
        $this->template->templateCustomName = "stories-detail";
        $this->template->pageType = "customer_story";
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
}
