<?php

namespace App\Presenters;

use App\Forms\ContactForm;
use App\Forms\SubscribeForm;
use App\Model\Db\LogErrorRequestModel;
use App\Service\Config\ApplicationConfig;
use Contributte\Translation\Translator; use Contributte;

use Nette;


final class Error4xxPresenter extends BasePresenter
{
    private $logErrorRequestModel;

    public function __construct(SubscribeForm $subscribeForm, Translator $translator, ApplicationConfig $applicationConfig, LogErrorRequestModel $logErrorRequestModel, ContactForm $contactForm)
    {
        parent::__construct($subscribeForm, $translator, $applicationConfig, $contactForm);
        $this->logErrorRequestModel = $logErrorRequestModel;

    }

    public function startup()
    {
        parent::startup();
        if (!$this->getRequest()->isMethod(Nette\Application\Request::FORWARD)) {
            $this->error();
        }
    }

    public function renderDefault(Nette\Application\BadRequestException $exception)
    {
        $request = [];
        if (isset($_SERVER["HTTP_REFERER"]) && !empty($_SERVER["HTTP_REFERER"])) {
            $request["referer"] = $_SERVER["HTTP_REFERER"];
        }
        if (isset($_SERVER["REQUEST_URI"]) && !empty($_SERVER["REQUEST_URI"])) {
            $request["page"] = $_SERVER["REQUEST_URI"];
        }
        if(isset($request["page"])){
            $this->logErrorRequestModel->createRecord($request);
        }

        $this->redirect("Homepage:default");

        // load template 403.latte or 404.latte or ... 4xx.latte
        //$file = __DIR__ . "/templates/Error/{$exception->getCode()}.latte";
        //$file = is_file($file) ? $file : __DIR__ . '/templates/Error/4xx.latte';
        //$this->template->setFile($file);
    }
}
