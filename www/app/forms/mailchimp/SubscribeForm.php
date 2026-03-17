<?php

namespace App\Forms;

use App\Model\MailChimpModel,
    Nette\Application\UI\Form;
use Contributte\Translation\Translator; use Contributte;
use stringEncode\Exception;
use Monolog\Logger;

class SubscribeForm
{
    CONST TRANSLATE_PATH = "forms.mailchimp-subscribe.";
    CONST RESULT_USER_EXISTS = 1;
    CONST RESULT_NEW_USER = 2;
    CONST RESULT_USER_RESUBSCRIBE = 3;

    private $logger;
    private $listTag = null;

    private $translator;
    public $mailChimpModel;

    private $result;


    /** @persistent */
    public $locale;



    public function __construct(MailChimpModel $mailChimpModel, Logger $logger, Translator $translator)
    {
        $this->mailChimpModel = $mailChimpModel;
        $this->translator = $translator;
        $this->logger = $logger;
    }

    public function create()
    {

        $form = new Form;

        $nameLength = 30;
        $form->addText("name", $this->translator->translate(self::TRANSLATE_PATH . "items.name.label"))
            ->addRule(Form::MaxLength, $this->translator->translate(self::TRANSLATE_PATH . "items.name.validation.maxLength", ["len" => $nameLength]), $nameLength)
            ->setHtmlAttribute("placeholder", $this->translator->translate(self::TRANSLATE_PATH . "items.name.placeholder"));

        $surnameLength = 50;
        $form->addText("surname", $this->translator->translate(self::TRANSLATE_PATH . "items.surname.label"))
            ->addRule(Form::MaxLength, $this->translator->translate(self::TRANSLATE_PATH . "items.surname.validation.maxLength", ["len" => $surnameLength]), $surnameLength)
            ->setHtmlAttribute("placeholder", $this->translator->translate(self::TRANSLATE_PATH . "items.surname.placeholder"));

        $emailLength = 50;
        $form->addText("email", $this->translator->translate(self::TRANSLATE_PATH . "items.email.label"))
            ->addRule(Form::MaxLength, $this->translator->translate(self::TRANSLATE_PATH . "items.email.validation.maxLength", ["len" => $emailLength]), $emailLength)
            ->addRule(Form::Email, $this->translator->translate(self::TRANSLATE_PATH . "items.email.validation.valid"))
            ->setHtmlAttribute("placeholder", $this->translator->translate(self::TRANSLATE_PATH . "items.email.placeholder"))
            ->setRequired($this->translator->translate(self::TRANSLATE_PATH . "items.email.validation.required"));

        $form->addHidden("listId");

        $form->addHidden("customFormTag")
            ->setHtmlId("customFormTag");


        $form->addSubmit("subscribe", $this->translator->translate(self::TRANSLATE_PATH . "items.submit.label"))
            ->setHtmlAttribute("class", "button GA_Mailchimp_log");
        $form->onSuccess[] = array($this, 'process');
        $form->onValidate[] = array($this, 'onValidate');

        return $form;
    }


    public function process(Form $form, $values)
    {
        try {
            $data = array(
                "email_address" => $values->email,
            );

            if (isset($values->name) && isset($values->surname)) {
                $data["merge_fields"] = [
                    "FNAME" => $values->name,
                    "LNAME" => $values->surname,
                ];
            }

            // GDPR - ADD MARKETING PERMISSIONS
            $product = null;
            $availableProductList = $this->mailChimpModel->config["lists"];
            foreach ($availableProductList as $name => $value) {
                if ($value["id"] === $values->listId) {
                    $product = $name;
                }
            }

            if($product === null){
                throw new Exception("Invalid product");
            }

            if ($product !== null) {
                if (isset($availableProductList[$product]["gdpr"])) {
                    foreach ($availableProductList[$product]["gdpr"] as $permissions => $permissionsVal) {
                        $data["marketing_permissions"][] = array(
                            "marketing_permission_id" => $permissions,
                            "enabled" => $permissionsVal
                        );
                    }
                }
            }

            // SET STATUS
            $data["status"] = (isset($availableProductList[$product]["status_subscribe"]) ? $availableProductList[$product]["status_subscribe"] : "pending");

            $result = $this->mailChimpModel->addSubscriberToList($data, $values->listId);
            $this->logger->debug("Trying add user to mailing list " . $values->email . " to list " . $values->listId . "; Result: " . (isset($result) ? json_encode($result) : ""));

            if (isset($result["response"]["title"])) {
                $title = $result["response"]["title"];

                if ($title === "Member Exists") {
                    //$form->addError($this->translator->translate(self::TRANSLATE_PATH . "errors.email-used"));
                    $memberResult = $this->mailChimpModel->getDetailOfListMember($values->listId, $values->email);
                    $this->logger->debug("DEBUG - User exist in mailing list" . $values->email . " to list " . $values->listId . "; Result: " . (isset($memberResult) ? json_encode($memberResult) : ""));

                    $this->mailChimpModel->isResultError($memberResult);


                    if($memberResult["response"]["status"] === "unsubscribed"){
                        $this->setResult(self::RESULT_USER_RESUBSCRIBE);
                        $dataToResubscribe = [
                            "status" => "pending"
                        ];
                        if(isset($data["marketing_permissions"])){
                            $dataToResubscribe["marketing_permissions"] = $data["marketing_permissions"];
                        }
                        $updateStatusResult = $this->mailChimpModel->updateMemberInList($dataToResubscribe, $values->listId, $values->email);

                        $this->logger->debug("Trying update user in mailing list " . $values->email . " to list " . $values->listId . "; Result: " . (isset($updateStatusResult) ? json_encode($updateStatusResult) : ""));
                    } else {
                        $this->setResult(self::RESULT_USER_EXISTS);
                    }

                    if ($this->getListTag() !== null) {
                        $this->mailChimpModel->setSubscriberTag($values->email, $this->getListTag(), $values->listId);
                    }

                    if(isset($values->customFormTag) && !empty($values->customFormTag)){
                        $this->mailChimpModel->setSubscriberTag($values->email, $values->customFormTag, $values->listId);
                    }

                    return true;
                }
            }
            $this->mailChimpModel->isResultError($result);

            $this->setResult(self::RESULT_NEW_USER);

            if ($this->getListTag() !== null) {
                $this->mailChimpModel->setSubscriberTag($values->email, $this->getListTag(), $values->listId);
            }
            if(isset($values->customFormTag) && !empty($values->customFormTag)){
                $this->mailChimpModel->setSubscriberTag($values->email, $values->customFormTag, $values->listId);
            }

        } catch (\Exception $e) {
            $this->logger->debug("Unsuccesfull add user - " . $values->email . " to list " . $values->listId . "; Result: " . (isset($result) ? json_encode($result) : ""));
            $form->addError($this->translator->translate(self::TRANSLATE_PATH . "errors.unexpected"));
        }

        return true;
    }

    /**
     * @return mixed
     */
    public function getListTag()
    {
        return $this->listTag;
    }

    /**
     * @param mixed $listTag
     */
    public function setListTag($listTag)
    {
        if(!empty($listTag)){
            $this->listTag = "Info_" . $listTag;
        }
    }

    public function setResult($result){
        $this->result = $result;
    }

    /**
     * @return mixed
     */
    public function getResult()
    {
        return $this->result;
    }
}
