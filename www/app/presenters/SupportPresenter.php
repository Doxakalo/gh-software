<?php

namespace App\Presenters;

use App\Forms\ContactForm;
use App\Forms\ClientPortal\CreateNewRequestForm;
use App\Forms\StoreForm;
use App\Forms\SubscribeForm;
use App\Model\FastSpringCacheModel;
use App\Model\FastSpringModel;
use App\Service\Config\ApplicationConfig;
use App\Service\Stores;
use Contributte\Translation\Translator; use Contributte;

use Nette\Application\UI\Form;

class SupportPresenter extends BasePresenter
{

    // OTHER
    //private $contactFormSentData;
    private $storeForm;
    private $createNewRequestForm;
    private $fastSpringCacheModel;
    public $fastSpringModel;
    private $stores;

    public function __construct(SubscribeForm $subscribeForm, CreateNewRequestForm $createNewRequestForm, Translator $translator, ApplicationConfig $applicationConfig, ContactForm $contactForm, StoreForm $storeForm, Stores $stores, FastSpringModel $fastSpringModel, FastSpringCacheModel $fastSpringCacheModel)
    {
        $this->contactForm = $contactForm;
        parent::__construct($subscribeForm, $translator, $applicationConfig, $contactForm);
        $this->storeForm = $storeForm;
        $this->stores = $stores;
        $this->createNewRequestForm = $createNewRequestForm;
        $this->fastSpringModel = $fastSpringModel;
        $this->fastSpringCacheModel = $fastSpringCacheModel;
    }

    protected function createComponentStoreForm()
    {

        $form = $this->storeForm->create();

        if (isset($_GET["coupon"]) && !empty($_GET["coupon"])) {
            $coupon = $_GET["coupon"];

            /** @var \Nette\Forms\Controls\TextInput $formDiscountCode*/
            $formDiscountCode = $form["discount_code"];
            $formDiscountCode->setDefaultValue($coupon);
        }

        $form->onError[] = function (Form $form) {
            $this->template->customError = $form->errors[0];
            $this->template->storeFormError = true;
            $this->redrawControl('storeForm');
        };

        $form->onSuccess[] = function (Form $form) {
            if ($this->storeForm->result === null) {
                $this->template->storeFormSuccess = true;
                $this->redrawControl('storeForm');
            } else {
                $this->redirectUrl($this->fastSpringModel->getSessionUrlById($this->storeForm->result["id"]));
            }
        };

        $this->redrawControl('storeForm');

        return $form;
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
            ->addRule(\Nette\Forms\Form::Email, $this->translator->translate(ContactForm::TRANSLATE_PATH . "items.email.validation.valid"));
        $form["timezones"]->setRequired($this->translator->translate(ContactForm::TRANSLATE_PATH . "items.timezone.validation.required"))
            ->setPrompt($this->translator->translate(ContactForm::TRANSLATE_PATH . "items.timezone.placeholder"))->setDefaultValue(null);

        $form->onError[] = function () {
            $this->redrawControl('contactAdditionalInformationForm');
            $this->redrawControl('contactAdditionalInformationForm2');
            $this->redrawControl('contactAdditionalInformationForm3');
        };

        $form->onSuccess[] = function () {
            $this->template->contactAdditionalInformationSuccess = true;
            $this->redrawControl('contactOngoingCareForm');
            $this->redrawControl('contactScheduledAssistance');
            $this->redrawControl('contactImmediateHelpForm');
        };

        return $form;
    }


    protected function createComponentContactImmediateHelpForm()
    {
        $form = $this->contactForm->create();

        if(isset($this->customPageName)){
            $this->contactForm->setCurrentPage($this->presenter->getName().' ['.$this->customPageName.']');
        }else{
            $this->contactForm->setCurrentPage($this->presenter->getName());
        }

        $this->addNumberPrefix($form);
        $this->contactForm->setNumberPrefix($this->numberPrefix());

        /** @var \Nette\Forms\Control\TextInput $formType */
        $formType = $form["type"];
        $formType->setDefaultValue("immediate_help");

        /** @var \Nette\Forms\Control\TextInput $formNumber */
        $formNumber = $form["number"];
        $formNumber->setRequired($this->translator->translate(ContactForm::TRANSLATE_PATH . "items.number.validation.required"));

        $form["process"]->caption = $this->translator->translate("forms.support-immediate-help.items.submit.label");


        $form->onSuccess[] = function ($form) {
            $this->template->contactImmediateHelpFormSuccess = true;
            $this->setContactFormSentData($form->getValues());
        };

        $this->redrawControl('contactImmediateHelpForm');

        return $form;
    }



    protected function createComponentContactScheduledAssistanceForm()
    {
        $form = $this->contactForm->create();

        if(isset($this->customPageName)){
            $this->contactForm->setCurrentPage($this->presenter->getName().' ['.$this->customPageName.']');
        }else{
            $this->contactForm->setCurrentPage($this->presenter->getName());
        }

        $this->addNumberPrefix($form);
        $this->contactForm->setNumberPrefix($this->numberPrefix());

        /** @var \Nette\Forms\Controls\TextInput $formType */
        $formType = $form["type"];
        $formType->setDefaultValue("scheduled_assistance");

        /** @var \Nette\Forms\Control\TextInput $formNumber */
        $formNumber = $form["number"];
        $formNumber->setRequired($this->translator->translate(ContactForm::TRANSLATE_PATH . "items.number.validation.required"));

        /** @var \Nette\Forms\Control\TextInput $formName */
        $formName = $form["name"];
        $formName->setRequired($this->translator->translate(ContactForm::TRANSLATE_PATH . "items.name.validation.required"));

        /** @var \Nette\Forms\Control\TextInput $formTopic */
        $formTopic = $form["topic"];
        $formTopic->setRequired($this->translator->translate(ContactForm::TRANSLATE_PATH . "items.topic.validation.required"));

        /** @var \Nette\Forms\Control\TextInput $formTime */
        $formTime = $form["time"];
        $formTime->setRequired($this->translator->translate(ContactForm::TRANSLATE_PATH . "items.time.validation.required"));

        $form->onSuccess[] = function ($form) {
            $this->template->contactScheduledAssistanceFormSuccess = true;
            $this->setContactFormSentData($form->getValues());
            //$this->redrawControl('contactForm');
        };

        $this->redrawControl('contactScheduledAssistance');

        return $form;
    }

    protected function createComponentContactOngoingCareForm()
    {
        $form = $this->contactForm->create();

        if(isset($this->customPageName)){
            $this->contactForm->setCurrentPage($this->presenter->getName().' ['.$this->customPageName.']');
        }else{
            $this->contactForm->setCurrentPage($this->presenter->getName());
        }

        $this->contactForm->setNumberPrefix($this->numberPrefix());
        $this->addNumberPrefix($form);

        /** @var \Nette\Forms\Control\TextInput $formType */
        $formType = $form["type"];
        $formType->setDefaultValue("ongoing_care");

        /** @var Nette\Forms\Control\TextInput $formName*/
        $formName = $form["name"];
        $formName->setRequired($this->translator->translate(ContactForm::TRANSLATE_PATH . "items.name.validation.required"));

        /** @var Nette\Forms\Control\TextInput $formNumber*/
        $formNumber = $form["number"];
        $formNumber->setRequired($this->translator->translate(ContactForm::TRANSLATE_PATH . "items.number.validation.required"));

        /** @var Nette\Forms\Control\TextInput $formTopic*/
        $formTopic = $form["topic"];
        $formTopic->setRequired($this->translator->translate(ContactForm::TRANSLATE_PATH . "items.topic.validation.required"));

        /** @var Nette\Forms\Control\TextInput $formTime*/
        $formTime = $form["time"];
        $formTime->setRequired($this->translator->translate(ContactForm::TRANSLATE_PATH . "items.time.validation.required"));

        $form->onSuccess[] = function ($form) {
            $this->template->contactOngoingCareFormSuccess = true;
            $this->setContactFormSentData($form->getValues());
            $this->redrawControl('contactOngoingCareForm');
        };

        $this->redrawControl('contactOngoingCareForm');

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

    protected function createComponentCreateClientPortalNewRequestForm()
    {
        $form = $this->createNewRequestForm->create();

        $form->addHidden("subject");

        $productSelect = [];
        foreach($this->createNewRequestForm->products->getConfig() as $productCode => $product){
            $productSelect[$productCode] = $product["name"];
        }

        $form->addSelect("productCode", "Product", $productSelect)
            ->setPrompt($this->translator->translate(CreateNewRequestForm::TRANSLATE_PATH . "items.product.prompt"))
            ->setRequired($this->translator->translate(CreateNewRequestForm::TRANSLATE_PATH . "items.product.validation.required"));

        $form->addCheckbox("premium_support", $this->translator->translate(CreateNewRequestForm::TRANSLATE_PATH . "items.premium-support.label"));

        $this->createNewRequestForm->setCurrentPage($this->presenter->getName());
        $this->createNewRequestForm->setLng($this->locale);

        $form->onSuccess[] = function (Form $form) {
            $this->template->premiumSupport = $this->createNewRequestForm->getPremiumSupport();
            $this->template->csztFormSuccess = true;
        };

        $this->redrawControl('csztForm');


        return $form;
    }

    public function actionDefault()
    {
        $store = $this->stores->loadStoreItemsDetail($this->stores->getStoreDetail("im_support"));
        $this->template->store = $store;
        $this->template->toolName = "im_support";

        $this->template->pageType = "service_page";
    }
}
