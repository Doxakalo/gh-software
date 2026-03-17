<?php

namespace App\Forms;

use App\Model\MailChimpModel,
    Nette\Application\UI\Form;
use Contributte\Translation\Translator;
use Contributte;
use Monolog\Logger;


use App\Model\PushoverModel;
use App\Service\Config\ApplicationConfig;
use Nette\Application\LinkGenerator;
use Nette\Http\Request;
use Nette\Mail\Message;
use Nette\Mail\Mailer;
use App\Model\db\FormsLogModel;

class ContactForm
{
    const TRANSLATE_PATH = "forms.contact.";

    private $logger;

    private $translator;
    public $mailChimpModel;
    public $applicationConfig;
    private $mailer;
    private $pushoverModel;
    private $formsLogModel;
    public $httpRequest;
    //private $contactFormSentData;
    private $numberPrefix;
    private $linkGenerator;


    /** @persistent */
    public $locale;

    private $currentPage = null;

    /**
     * Data about sent form
     *
     * @var mixed
     */
    private $contactFormSentData;

    /**
     * data setup for contant form
     *
     * @param mixed $contactFormSentData
     */


    public function __construct(MailChimpModel $mailChimpModel, Logger $logger, Translator $translator, ApplicationConfig $applicationConfig, Mailer $mailer, PushoverModel $pushoverModel, FormsLogModel $formsLogModel, Request $httpRequest, LinkGenerator $linkGenerator)
    {
        $this->mailChimpModel = $mailChimpModel;
        $this->translator = $translator;
        $this->logger = $logger;
        $this->applicationConfig = $applicationConfig;
        $this->mailer = $mailer;
        $this->pushoverModel = $pushoverModel;
        $this->formsLogModel = $formsLogModel;
        $this->linkGenerator = $linkGenerator;
        $this->httpRequest = $httpRequest;
    }

    public function create()
    {
        $form = new Form;

        //use as honeypot
        $form->addText("subject2", $this->translator->translate(self::TRANSLATE_PATH . "items.subject2.label"))
            ->setHtmlAttribute("class", "contactForm-SubjectField")
            ->setHtmlAttribute("placeholder", $this->translator->translate(self::TRANSLATE_PATH . "items.subject2.placeholder"));

        $nameLength = 50;
        $form->addText("name", $this->translator->translate(self::TRANSLATE_PATH . "items.name.label"))
            ->setHtmlAttribute("placeholder", $this->translator->translate(self::TRANSLATE_PATH . "items.name.placeholder"))
            ->addRule(Form::MaxLength, $this->translator->translate(self::TRANSLATE_PATH . "items.name.validation.maxLength", ["len" => $nameLength]), $nameLength);

        $numberLength = 30;
        $form->addText("number", $this->translator->translate(self::TRANSLATE_PATH . "items.number.label"))
            ->setHtmlAttribute("placeholder", $this->translator->translate(self::TRANSLATE_PATH . "items.number.placeholder"))
            ->setHtmlAttribute("type", "tel")
            ->addRule(Form::MaxLength, $this->translator->translate(self::TRANSLATE_PATH . "items.number.validation.maxLength", ["len" => $numberLength]), $numberLength);


        $form->addText("time", $this->translator->translate(self::TRANSLATE_PATH . "items.time.label"))
            ->setHtmlAttribute("placeholder", $this->translator->translate(self::TRANSLATE_PATH . "items.time.placeholder"));

        $emailLength = 50;
        $form->addText("email", $this->translator->translate(self::TRANSLATE_PATH . "items.email.label"))
            ->addRule(Form::MaxLength, $this->translator->translate(self::TRANSLATE_PATH . "items.email.validation.maxLength", ["len" => $emailLength]), $emailLength)
            ->setHtmlAttribute("placeholder", $this->translator->translate(self::TRANSLATE_PATH . "items.email.placeholder"));

        $topicLength = 100;
        $form->addText("topic", $this->translator->translate(self::TRANSLATE_PATH . "items.topic.label"))
            ->setHtmlAttribute("placeholder", $this->translator->translate(self::TRANSLATE_PATH . "items.topic.placeholder"))
            ->addRule(Form::MaxLength, $this->translator->translate(self::TRANSLATE_PATH . "items.topic.validation.maxLength", ["len" => $topicLength]), $topicLength);


        $messageLength = 3500;
        $form->addTextArea("message", $this->translator->translate(self::TRANSLATE_PATH . "items.message.label"))
            ->setHtmlAttribute("placeholder", $this->translator->translate(self::TRANSLATE_PATH . "items.message.placeholder"))
            ->addRule(Form::MaxLength, $this->translator->translate(self::TRANSLATE_PATH . "items.message.validation.maxLength", ["len" => $messageLength]), $messageLength);


        $form->addHidden("type");

        $form->addHidden("formid", rand(1, 1000000000000));
        $form->addHidden("predata");
        $form->addHidden("formKeystrokeCount")
            ->setDefaultValue(0)
            ->setHtmlAttribute("class", "contactForm-KeystrokeCount");

        $form->addHidden("formCreatedTime")
            ->setDefaultValue(date("Y-m-d H:i:s"));

        $form->addSubmit("process", $this->translator->translate(self::TRANSLATE_PATH . "items.submit.label"))
            ->setHtmlAttribute("class", "button");

        $form->onSuccess[] = array($this, 'process');

        return $form;
    }

    public function process(Form $form, $values)
    {
        try {
            if (!empty($values->predata)) {
                $preData = unserialize($values->predata);
                if (isset($values->email) && !empty($values->email)) {
                    $preData->email = $values->email;
                }
                if (isset($values->timezones) && !empty($values->timezones)) {
                    $preData->timezones = explode("||", $values->timezones)[1];
                }

                $values = $preData;
            }

            unset($values->predata);
            $log = $this->logContactForm($values);


            // honeypot check
            if (isset($values->subject2) && !empty($values->subject2)) {
                $this->formsLogModel->evaluateLogAsSpam($log->id);
                return $form;
            }

            if (isset($values->formKeystrokeCount) && $values->formKeystrokeCount === "0" && !empty($values->message)) {
                $this->formsLogModel->evaluateLogAsSpam($log->id);
                return $form;
            }


            $spamSecurityDirectoryMessage = $this->applicationConfig->getConfig()["spam-security-directory"]["message"];

            if (!empty($values->message)) {
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
                $lastLineMessage = strtolower(end($splitByLine));

                foreach ($spamSecurityDirectoryMessage["last-line"] as $combinationOfWorld) {
                    $resultOfFind = [];
                    foreach ($combinationOfWorld as $world) {
                        if (strpos($lastLineMessage, $world) !== false) {
                            $resultOfFind[] = true;
                        } else {
                            $resultOfFind[] = false;
                        }
                    }

                    $allWordlsFind = true;
                    foreach ($resultOfFind as $result) {
                        if ($result === false) {
                            $allWordlsFind = false;
                        }
                    }

                    if ($allWordlsFind === true) {
                        $this->formsLogModel->evaluateLogAsSpam($log->id);
                        return $form;
                    }
                }

                $messageRaw = strtolower(trim($values->message));

                foreach ($spamSecurityDirectoryMessage["whole-message"] as $combinationOfWorld) {
                    $resultOfFind = [];
                    foreach ($combinationOfWorld as $world) {
                        if (strpos($messageRaw, $world) !== false) {
                            $resultOfFind[] = true;
                        } else {
                            $resultOfFind[] = false;
                        }
                    }

                    $allWordlsFind = true;
                    foreach ($resultOfFind as $result) {
                        if ($result === false) {
                            $allWordlsFind = false;
                        }
                    }

                    if ($allWordlsFind === true) {
                        $this->formsLogModel->evaluateLogAsSpam($log->id);
                        return $form;
                    }
                }
            }


            $config = $this->applicationConfig->getConfig()["contact"];

            if (isset($config[$values->type])) {
                $contactTypeConfig = $config[$values->type];

                $message = "Byl vyplněněn kontaktní formulář - " . $contactTypeConfig["subject"] . ($this->getCurrentPage() !== null ? " - " . $this->getCurrentPage() : "") . '<br/><br/>';

                $link = $this->linkGenerator->link('Tech:MarkContactLogAsSpam', ["id" => $log->id]);

                $message .= '<b>Dostupné kontaktní údaje:</b><br/>';
                if (isset($values->name) && !empty($values->name)) {
                    $message .= 'Jméno: ' . $values->name . '<br/>';
                }
                if (isset($values->email) && !empty($values->email)) {
                    $message .= 'Email: ' . $values->email . '<br/>';
                }


                if (isset($values->number) && !empty($values->number)) {
                    if (isset($values->prefix_number) && !empty($values->prefix_number)) {
                        $prefixNumber = $this->getNumberPrefix()[$values->prefix_number];
                    }
                    $message .= 'Telefonní číslo: <a href="tel:' . strstr($prefixNumber, ' (', true) . '' . $values->number . '">';
                    $message .= strstr($prefixNumber, ' (', true) . '-' . $values->number;
                    $message .= '</a>' . strstr($prefixNumber, ' (') . '<br/>';
                }
                if (isset($values->topic) && !empty($values->topic)) {
                    $message .= 'Komunikace ohledně: ' . $values->topic . '<br/>';
                }
                if (isset($values->time) && !empty($values->time)) {
                    $message .= 'Čas: ' . $values->time . '<br/>';
                }
                if (isset($values->timezones) && !empty($values->timezones)) {
                    $message .= 'Timezone: ' . $values->timezones . '<br/>';
                }
                if (isset($values->message) && !empty($values->message)) {
                    $message .= 'Message: ' . nl2br($values->message) . '<br/>';
                }


                if (isset($contactTypeConfig["email"]) && $contactTypeConfig["email"] !== false) {
                    $message .= '<a href="' . $link . '">Označit jako spam</a><br/><br/>';


                    $mail = new Message;
                    //$mail->setFrom("noreply@24uSoftware.com", $contactTypeConfig["subject"])
                    if (isset($values->email) && !empty($values->email)) {
                        $replyTo = $values->email;
                    } else {
                        $replyTo = "noreply@24uSoftware.com";
                    }
                    $mail->setFrom($replyTo, $values->name . ' (by way of 24usoftware.com)')
                        ->addTo($contactTypeConfig["email"])
                        ->setSubject($contactTypeConfig["subject"] . (isset($values->name) && !empty($values->name) ? " - " . $values->name : ""))
                        ->setHtmlBody($message);
                    if (isset($values->email) && !empty($values->email)) {
                        $mail->addReplyTo($values->email);
                    }

                    $this->mailer->send($mail);
                }

                if (isset($contactTypeConfig["pushover_user"]) && $contactTypeConfig["pushover_user"] !== false) {
                    $pushoverMessage = '<a href="' . $link . '">Označit jako spam</a><br/><br/>';

                    $pushoverMessage .= $values->name;
                    if ($this->getCurrentPage() !== null) {
                        if ($this->getCurrentPage() == 'Support') {
                            $pushoverMessage .= ' - ' . $contactTypeConfig["subject"];
                        }
                        $pushoverMessage .= ' - ' . $this->getCurrentPage();
                    }
                    if ($values->type == "footer") {
                        $pushoverMessage .= ' - ' . $values->message;
                    } else {
                        $pushoverMessage .= ' - call about: ' . $values->topic;
                    }
                    $pushoverMessage .= '<br/><br/>' . $message;

                    $result = $this->pushoverModel->pushNotification([
                        "token" => $this->pushoverModel->config["auth"]["token"],
                        "user" => $contactTypeConfig["pushover_user"],
                        "title" => $contactTypeConfig["subject"] . (isset($values->name) && !empty($values->name) ? " - " . $values->name : ""),
                        "message" => $pushoverMessage,
                        "html" => 1
                    ]);

                    $this->pushoverModel->isResultError($result);
                    $contactInformation = [];
                    if (isset($values->number)) {
                        $contactInformation["number"] = $values->number;
                    }
                    if (isset($values->email)) {
                        $contactInformation["email"] = $values->email;
                    }
                    if (isset($values->name)) {
                        $contactInformation["name"] = $values->name;
                    }
                    $this->logger->debug("User ( " . json_encode($contactInformation) . " ) contact us - through pushover. Result: " . json_encode($result));
                }

                return $form;
            } else {
                throw new \Exception("Error - Missing configuration for selected type");
            }
        } catch (\Nette\Mail\SmtpException $e) {
            $this->logger->error("User ( " . $this->setFormUserIdentification($values) . " ) tried contact us - but contacting failed (contact via e-mail failed) Result: " . $e->getMessage());
            $form->addError($this->translator->translate(self::TRANSLATE_PATH . "errors.unexpected"));
        } catch (\Exception $e) {
            $this->logger->error("User ( " . $this->setFormUserIdentification($values) . " ) tried contact us - but contacting failed Result: " . $e->getMessage());
            $form->addError($this->translator->translate(self::TRANSLATE_PATH . "errors.unexpected"));
        }

        return $form;
    }

    private function logContactForm($values)
    {
        $data = [
            'type' => $values->type,
            'page' => ($this->getCurrentPage() !== null ? $this->getCurrentPage() : ""),
            'form_created_time' => $values->formCreatedTime,
            'form_submit_time' => date("Y-m-d H:i:s"),
            'first_visit_cookie_time' => $this->httpRequest->getCookie($this->applicationConfig->getConfig()["first_visit_cookie_name"]),
            'remote_ip' => $this->httpRequest->getRemoteAddress(),
            'http_referer' => $this->httpRequest->getHeader("referer"),
            'http_header_json' => json_encode($this->httpRequest->getHeaders(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_LINE_TERMINATORS),
            'val_json' => json_encode($values, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_LINE_TERMINATORS),
            'keystroke_count' => $values->formKeystrokeCount,
            'form-id' => $values->formid,
        ];

        return $this->formsLogModel->createRecord($data);
    }

    private function setFormUserIdentification($values)
    {
        if (isset($values->number)) {
            $userIdent = $values->number;
        } elseif (isset($values->email)) {
            $userIdent = $values->email;
        } else {
            $userIdent = $values->name;
        }
        return $userIdent;
    }

    public function setCurrentPage($currentPage)
    {
        $this->currentPage = $currentPage;
    }

    public function getCurrentPage()
    {
        return $this->currentPage;
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

    /**
     * @param mixed $numberPrefix
     */
    public function setNumberPrefix($numberPrefix)
    {
        $this->numberPrefix = $numberPrefix;
    }

    /**
     * @return mixed
     */
    public function getNumberPrefix()
    {
        return $this->numberPrefix;
    }
}
