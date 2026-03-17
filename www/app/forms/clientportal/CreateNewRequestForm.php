<?php

namespace App\Forms\ClientPortal;

use App\model\ClientPortalModel;
use App\Model\Db\FormsLogModel;
use App\Model\PushoverModel,
    Nette\Forms\Form;
use Cassandra\Date;
use Contributte\Translation\Translator;
use Monolog\Logger;


use App\Service\Config\ApplicationConfig;
use App\Service\Config\ProductsConfig;
use Nette\Application\LinkGenerator;
use Nette\Http\Request;
use Nette\Http\Session;
use Nette\Mail\Mailer;
use Nette\Mail\Message;

class CreateNewRequestForm
{
    // CONSTS
    CONST TRANSLATE_PATH = "forms.client-portal-create-ticket.";

    private $logger;

    private $translator;

    // MODELS AND SERVICES
    public $products;

    // INTERN
    private $productCode = null;
    private $groupId;
    private $subject;
    private $currentPage = null;

    public $applicationConfig;
    private $mailer;
    private $pushoverModel;
    private $formsLogModel;
    public $httpRequest;
    private $linkGenerator;
    private $clientPortalModel;
    private $lng;
    private $premiumSupport;

    public function __construct(Logger $logger, Translator $translator, ApplicationConfig $applicationConfig, Mailer $mailer, PushoverModel $pushoverModel, FormsLogModel $formsLogModel,Request $httpRequest, LinkGenerator $linkGenerator, ProductsConfig $productsConfig, ClientPortalModel $clientPortalModel)
    {
        $this->translator = $translator;
        $this->logger = $logger;
        $this->applicationConfig = $applicationConfig;
        $this->mailer = $mailer;
        $this->pushoverModel = $pushoverModel;
        $this->formsLogModel = $formsLogModel;
        $this->linkGenerator = $linkGenerator;
        $this->httpRequest = $httpRequest;
        $this->products = $productsConfig;
        $this->clientPortalModel = $clientPortalModel;
    }



    public function create()
    {
        $form = new Form;

        //use as honeypot
        $form->addText("subject2", $this->translator->translate(self::TRANSLATE_PATH . "items.subject2.label"))
            ->setHtmlAttribute("class", "contactForm-SubjectField")
            ->setHtmlAttribute("placeholder", $this->translator->translate(self::TRANSLATE_PATH . "items.subject2.placeholder"));

        $form->addText("topic", $this->translator->translate(self::TRANSLATE_PATH . "items.topic.label"))
            ->setHtmlAttribute("placeholder", $this->translator->translate(self::TRANSLATE_PATH . "items.topic.placeholder"))
            ->setHtmlAttribute("maxlength", 35)
            ->setRequired($this->translator->translate(self::TRANSLATE_PATH . "items.topic.validation.required"));

        $form->addText("email", $this->translator->translate(self::TRANSLATE_PATH . "items.email.label"))
            ->addRule(Form::Email, $this->translator->translate(self::TRANSLATE_PATH . "items.email.validation.valid"))
            ->setHtmlType("placeholder", $this->translator->translate(self::TRANSLATE_PATH . "items.email.placeholder"))
            ->setRequired($this->translator->translate(self::TRANSLATE_PATH . "items.email.validation.required"));

        $form->addTextArea("message", $this->translator->translate(self::TRANSLATE_PATH . "items.message.label"))
            ->setHtmlAttribute("placeholder", $this->translator->translate(self::TRANSLATE_PATH . "items.message.placeholder"))
            ->setHtmlAttribute("rows", 8)
            ->setRequired($this->translator->translate(self::TRANSLATE_PATH . "items.message.validation.required"));

        $form->addText("fullname", $this->translator->translate(self::TRANSLATE_PATH . "items.fullname.label"))
            ->setHtmlAttribute("placeholder", $this->translator->translate(self::TRANSLATE_PATH . "items.fullname.placeholder"))
            ->setRequired($this->translator->translate(self::TRANSLATE_PATH . "items.fullname.validation.required"));

        $form->addSubmit("createTicket", $this->translator->translate(self::TRANSLATE_PATH . "items.submit.label"))
            ->setHtmlAttribute("class","button");

        $form->addHidden("type", "product_support");



        $form->addHidden("formid", rand(1, 1000000000000));
        $form->addHidden("formKeystrokeCount")
            ->setDefaultValue(0)
            ->setHtmlAttribute("class", "contactForm-KeystrokeCount");
        $form->addHidden("formCreatedTime")
            ->setDefaultValue(date("Y-m-d H:i:s"));

        $form->onSuccess[] = array($this, 'process');

        return $form;
    }

    public function process(Form $form, $values)
    {
        try {
            $log = $this->logContactForm($values);



            // honeypot check
            if (isset($values->subject2) && !empty($values->subject2)) {
                $this->formsLogModel->evaluateLogAsSpam($log->id);
                return $form;
            }

            if(isset($values->productCode)){
                $this->setProductCode($values->productCode);
            } else {
                $this->setProductCode("other");
            }

            $product = $this->products->getProductByCode($this->getProductCode());

            if(empty($values->message)){
                $this->formsLogModel->evaluateLogAsSpam($log->id);
                return $form;
            }
            if(isset($values->formKeystrokeCount) && $values->formKeystrokeCount === "0" && !empty($values->message)){
                $this->formsLogModel->evaluateLogAsSpam($log->id);
                return $form;
            }

            $spamSecurityDirectoryMessage = $this->applicationConfig->getConfig()["spam-security-directory"]["message"];

            if(!empty($values->message)) {
                // CHECK IF MESSAGE IS NOT SHORT
                $messageRaw = $values->message;
                $messageRaw = str_replace("\n", " ", $messageRaw);
                $messageRaw = str_replace("\t", " ", $messageRaw);
                $messageBySpacesArray = explode(" ", trim($messageRaw));
                $numberOfWords = count($messageBySpacesArray);

                if ($numberOfWords <= 2) {
                    $this->formsLogModel->evaluateLogAsSpam($log->id);
                    return $form;
                }


                // CHECK IF MESSAGE NOT CONTAINS SOME OF THE UNALLOWED WORDS
                $messageRaw = trim($values->message);
                $splitByLine = explode("\n", $messageRaw);
                $lastLineMessageSplitByLine = strtolower(end($splitByLine));
                $lastLineMessageSplitByDots = explode(".", $lastLineMessageSplitByLine);
                if(!empty($lastLineMessageSplitByDots)) {
                    foreach ($lastLineMessageSplitByDots as $index => $sentense) {
                        if(empty($sentense)){
                            unset($lastLineMessageSplitByDots[$index]);
                        }
                    }
                }
                $lastLineMessage = trim(end($lastLineMessageSplitByDots));

                foreach($spamSecurityDirectoryMessage["last-line"] as $combinationOfWorld){
                    $resultOfFind = [];
                    foreach($combinationOfWorld as $world){
                        if (strpos($lastLineMessage, $world) !== false) {
                            $resultOfFind[] = true;
                        } else {
                            $resultOfFind[] = false;
                        }
                    }

                    $allWordlsFind = true;
                    foreach($resultOfFind as $result){
                        if($result === false){
                            $allWordlsFind = false;
                        }
                    }

                    if($allWordlsFind === true){
                        $this->formsLogModel->evaluateLogAsSpam($log->id);
                        return $form;
                    }
                }

                $messageRaw = strtolower(trim($values->message));

                foreach($spamSecurityDirectoryMessage["whole-message"] as $combinationOfWorld){
                    $resultOfFind = [];
                    foreach($combinationOfWorld as $world){
                        if (strpos($messageRaw, $world) !== false) {
                            $resultOfFind[] = true;
                        } else {
                            $resultOfFind[] = false;
                        }
                    }

                    $allWordlsFind = true;
                    foreach($resultOfFind as $result){
                        if($result === false){
                            $allWordlsFind = false;
                        }
                    }

                    if($allWordlsFind === true){
                        $this->formsLogModel->evaluateLogAsSpam($log->id);
                        return $form;
                    }
                }
            }


            $messageSplitBySpaceArray = explode(' ', $values->message);
            $firstFiveWorlsFromMessage = "";
            foreach($messageSplitBySpaceArray as $index => $word){
                if($index < 5){
                    $firstFiveWorlsFromMessage .= $word . " ";
                }
            }
            $first30characters = mb_substr($values->message, 0, 30);

            $stringToSubject = (strlen(str_replace(" ", "", $firstFiveWorlsFromMessage)) <= 30 ? $firstFiveWorlsFromMessage : $first30characters);


            $requestName = ($this->getLng() === "cs" ? "[CZ]" : "[EN]") . " " . (isset($values->premium_support) && $values->premium_support === true ? "Premium Support: " : "Web Support: ") . $values->topic;

            $priorityId =  (isset($values->premium_support) && $values->premium_support === true ? 4 : 3); // 4 - Urgent | 3 - High

            $request = $this->clientPortalModel->createRequest([
                "name" => $requestName,
                "fullname" => $values->fullname,
                "subject" => $values->topic,
                "email" => $values->email,
                "tracker" => "support",
                "priority_id" => $priorityId,
                "project_id" => $product["cp-configuration-for-new-request"]["project_id"],
                "description" => $values->message,
                "is_external" => 1
            ]);

            if(isset($values->premium_support) && $values->premium_support === true) {
                $this->pushoverModel->pushNotification([
                    "token" => $this->pushoverModel->config["auth"]["token"],
                    "user" => $this->applicationConfig->getConfig()["contact"]["premium_support"]["pushover_user"],
                    "title" => $this->applicationConfig->getConfig()["contact"]["premium_support"]["subject"],
                    "message" => "Skrze webové stránky " . $_SERVER['HTTP_HOST'] . " byl vytvořen premium support pro produkt: <b>" . $product["name"] . "</b><br><br><b>Zákazníkem:</b><br>" . $values->fullname . "<br>" . $values->email . "<br><br>Prosím o ověření, že zákazník má prémium support zakoupený.",
                    "html" => 1
                ]);

                $this->setPremiumSupport(true);
            } else {
                $this->setPremiumSupport(false);
            }


            $this->clientPortalModel->isResultError($request);

            return $form;
        } catch (\Exception $e) {
            $htmlMessage = "<b>Webové stránky: </b>" . $_SERVER["HTTP_HOST"] . "<br>";
            $htmlMessage .= "<b>LogID: </b>" . $log->id . "<br>";
            $htmlMessage .= "<b>Důvod: </b> Někdo se pokusil vyplnit produktový formulář a nepodařilo se kontaktovat CP API z důvodu vytvoření nového požadavku";
            $this->pushoverModel->pushNotification([
                "token" => $this->pushoverModel->config["auth"]["token"],
                "user" => $this->applicationConfig->getConfig()["contact"]["technical-issue"]["pushover_user"],
                "title" => $this->applicationConfig->getConfig()["contact"]["technical-issue"]["subject"] . " "  . $_SERVER["HTTP_HOST"],
                "message" => $htmlMessage,
                "html" => 1
            ]);

            $this->logger->error("User tried create a new request, but failed: " . $e->getMessage());
            $form->addError($this->translator->translate(self::TRANSLATE_PATH . "errors.unexpected"));
        }


        return true;
    }



    private function logContactForm($values){

        $data=[
            'type' => $values->type,
            'page' => ($this->getCurrentPage() !== null ? $this->getCurrentPage() : ""),
            'form_created_time' => $values->formCreatedTime,
            'form_submit_time' => date("Y-m-d H:i:s"),
            'first_visit_cookie_time' => $this->httpRequest->getCookie($this->applicationConfig->getConfig()["first_visit_cookie_name"]),
            'remote_ip' => $this->httpRequest->getRemoteAddress(),
            'http_referer' => $this->httpRequest->getHeader("referer"),
            'http_header_json' => json_encode($this->httpRequest->getHeaders(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_LINE_TERMINATORS  ),
            'val_json' => json_encode($values, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_LINE_TERMINATORS ),
            'keystroke_count' => $values->formKeystrokeCount,
            'form-id' => $values->formid,
        ];

        return $this->formsLogModel->createRecord($data);
    }

    /**
     * @param mixed $productCode
     */
    public function setProductCode($productCode)
    {
        $this->productCode = $productCode;
    }

    /**
     * @return mixed
     */
    public function getProductCode()
    {
        return $this->productCode;
    }

    /**
     * @param mixed $groupId
     */
    public function setGroupId($groupId)
    {
        $this->groupId = $groupId;
    }

    /**
     * @return mixed
     */
    public function getGroupId()
    {
        return $this->groupId;
    }

    /**
     * @param mixed $subject
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    public function setCurrentPage($currentPage){
        $this->currentPage = $currentPage;
    }

    public function getCurrentPage(){
        return $this->currentPage;
    }

    /**
     * @return mixed
     */
    public function getLng()
    {
        return $this->lng;
    }

    /**
     * @param mixed $lng
     */
    public function setLng($lng): void
    {
        $this->lng = $lng;
    }

    /**
     * @return mixed
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param mixed $premiumSupport
     */
    public function setPremiumSupport($premiumSupport): void
    {
        $this->premiumSupport = $premiumSupport;
    }

    /**
     * @return mixed
     */
    public function getPremiumSupport()
    {
        return $this->premiumSupport;
    }
}
