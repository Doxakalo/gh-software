<?php

namespace App\Presenters;


use App\Forms\CancelSubscriptionForm;
use App\Forms\ContactForm;
use App\Forms\StoreForm;
use App\Forms\SubscribeForm;
use App\Models\FileMaker\ResultModel;
use App\Models\FileMaker\RunsModel;
use App\Service\Config\ApplicationConfig;
use App\Service\Stores;
use Contributte;
use Contributte\Translation\Translator;
use Nette\Application\UI\Form;

class CancelSubscriptionPresenter extends BasePresenter
{
    // MODELS & SERVICES


    // OTHER
    private $cancelSubscriptionForm;

    public function __construct(SubscribeForm $subscribeForm, Translator $translator, ApplicationConfig $applicationConfig, ContactForm $contactForm, CancelSubscriptionForm $cancelSubscriptionForm)
    {
        parent::__construct($subscribeForm, $translator, $applicationConfig, $contactForm);
        $this->cancelSubscriptionForm = $cancelSubscriptionForm;
    }

    public function actionDefault(){

    }

    protected function createComponentCancelSubscriptionForm()
    {
        $form = $this->cancelSubscriptionForm->create();

        $form->onSuccess[] = function () {
            $this->template->cancelSubriptionSentSucess = true;
            $this->template->numberOfActiveSubscription = $this->cancelSubscriptionForm->getNumberOfActiveSubscription();
            $this->template->subscriptionsToCancel = $this->cancelSubscriptionForm->getSubscriptionToCancel();
        };

        $form->onError[] = function (Form $form) {
           // var_dump($form->errors[0]);

        };

        $this->redrawControl('cancelSubscription');


        return $form;
    }
}
