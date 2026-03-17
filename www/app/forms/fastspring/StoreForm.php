<?php

namespace App\Forms;

use App\Model\FastSpringCacheModel;
use App\Model\FastSpringModel;
use Nette\Application\UI\Form;
use Monolog\Logger;

use App\Service\Config\ApplicationConfig;
use Contributte\Translation\Translator; use Contributte;

use Nette\Application\AbortException;
use Nette\Mail\Mailer;
use Nette\Mail\Message;
use Nette\Http\Request;
use Nette\Forms\Controls\TextInput;


class StoreForm
{
    public $result;
    private $logger;
    private $mailer;
    public $httpRequest;
    public $applicationConfig;
    private $addCustomFieldBundleId = false;

    public $mailChimpModel;
    public $fastSpringCacheModel;
    public $fastSpringModel;



    /** @persistent */
    public $locale;

    /** @var Translator @inject */
    public $translator;


    public function __construct(Logger $logger, Mailer $mailer, Translator $translator, Request $httpRequest, ApplicationConfig $applicationConfig, FastSpringModel $fastSpringModel, FastSpringCacheModel $fastSpringCacheModel)
    {
        $this->translator = $translator;
        $this->logger = $logger;
        $this->mailer = $mailer;
        $this->httpRequest = $httpRequest;
        $this->applicationConfig = $applicationConfig;
        $this->fastSpringModel = $fastSpringModel;
        $this->fastSpringCacheModel = $fastSpringCacheModel;
    }

    public function create()
    {
        $form = new Form;

        //use as honeypot
        $form->addText("subject2", "Subject")
            ->setHtmlAttribute("class", "contactForm-SubjectField")
            ->setHtmlAttribute("placeholder", "Please do not fill");

        $form->addText("email", "E-mail")
            ->setHtmlAttribute("placeholder", "Your e-mail")
            ->addCondition(Form::Filled)
                ->addRule(Form::Email, 'Please enter valid e-mail address')
            ->endCondition();

        $c1 = date('j') + 3;
        $c2 = date('N') + 2;
        $s = $c1 + $c2;

        $form->addText('spamSecureSum', sprintf('Sum the numbers %s+%s', $c1, $c2))
            ->setHtmlAttribute("placeholder", sprintf('Sum the numbers %s+%s', $c1, $c2))
            ->setHtmlAttribute("id", "frm-buy-spamSecureSum")
            ->setHtmlAttribute("class", "frm-buy-spamSecureSum")
            ->setRequired('Unknown error, please refresh the page')
            ->addRule(Form::Equal, 'Unknown error, please refresh the page', $s);

        $form->addHidden('c1', $c1)
            ->setHtmlAttribute("id", "frm-buy-c1");
        $form->addHidden('c2', $c2)
            ->setHtmlAttribute("id", "frm-buy-c2");

        $form->addHidden("personal_info_required");

        /** @var TextInput $emailInput */
        $emailInput = $form['email'];
        $emailInput->setRequired('Please enter your e-mail');
        $form->addText("fullname", "Full name")
            ->setHtmlAttribute("placeholder", "Your name")
            ->setHtmlAttribute("class", "extra-order")
            ->addConditionOn($emailInput, $form::Equal, '1')
                ->setRequired('Please enter your name');

        $form->addText("phone", "Phone")
            ->setHtmlAttribute("placeholder", "Your phone")
            ->setHtmlAttribute("class", "extra-order");

        $form->addText("discount_code", "If you have a discount code, enter it here:")
            ->setHtmlAttribute("placeholder", "Your Discount Code");

        
        /** @var \Nette\Forms\Controls\TextInput $email */
        $email = $form['email'];
        $email->setRequired('Please enter your e-mail');

        /** @var TextInput $bundleIdReq */
        $bundleIdReq = $form["bundleId_required"];

        $form->addHidden("bundleId_required");
            $form->addText("bundleId", "BundleID")
                ->setHtmlAttribute("placeholder", "BundleID")
                ->addConditionOn($bundleIdReq , Form::Equal, 1)
                ->setRequired('Please enter your BundleID');

        $nonExtraOrderLabel = "Proceed to checkout";
        $extraOrderLabel = "Send order";
        $form->addSubmit("process", $nonExtraOrderLabel)
            ->setHtmlAttribute("class", "button")
        ->setHtmlAttribute("data-extra-order-label", $extraOrderLabel)
        ->setHtmlAttribute("data-non-extra-order", $nonExtraOrderLabel);

        $form->onSuccess[] = array($this, 'process');

        return $form;
    }

    public function process(Form $form, $values)
    {

        try {
            if (isset($values->subject2) && !empty($values->subject2)) {
                return $form;
            }

            if ((!isset($values->email) || empty($values->email))) {
                throw new \Exception("Empty contact informations");
            }

            $input = $_POST;

            $hardwareFind = false;
            $products = [];
            foreach ($input as $name => $value) {
                if (strpos($name, 'product_') !== false) {
                    $quantity = intval($value);
                    if ($quantity !== 0) {
                        $product = explode("_", $name);
                        $productId = $product[1];
                        $type = $product[2];

                        $products[] = [
                            "product" => $productId,
                            "quantity" => intval($quantity)
                        ];

                    }
                }
            }

                // DEFINE BASIC STORE PARAMETERS
                $data = [
                    "contact" => [
                        "email" => $values->email,
                    ],
                ];

                // ADD AFFILIATE;
                $affiliate = $this->httpRequest->getCookie($this->fastSpringModel->getConfig()["affiliate_cookie"]);
                if($affiliate){
                    $data["tags"][]['affiliate'] = $affiliate;
                } else {
                    $data["tags"]['affiliate'] = "";
                }
                $affiliateID= $this->httpRequest->getCookie($this->fastSpringModel->getConfig()["affiliate_id_cookie"]);
                if($affiliateID){
                    $data["tags"]['affiliateid'] = $affiliateID;
                } else {
                    $data["tags"]['affiliateid'] = "";
                }

                if(isset($values->bundleId)){
                    $data["tags"]['bundleId'] = $values->bundleId;
                }

                // ADD DISCOUNT CODE
                if(isset($values->discount_code) && !empty($values->discount_code)){
                    $data["coupon"] = $values->discount_code;
                }

                foreach ($products as $item) {
                    $data["items"][] = [
                        "product" => $item["product"],
                        "quantity" => $item["quantity"],
                    ];
                }

                $langISO = $this->translator->getLocale() === "cs" ? "cs" : "en";
                $countryISO = $langISO === "cs" ? "CZ" : "US";


            if (isset($values->personal_info_required) && intval($values->personal_info_required) === 1) {
                $defaultLanguage = "en";
                $defaultCurrency = "USD";
                // Ověření a sanitizace HTTP_HOST
                $host = filter_var($_SERVER['HTTP_HOST'], FILTER_SANITIZE_URL);
                $uri = filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL);


                $message = "An order has been placed on the ".$host." website.<br/><br/>";
                $message .= "<b>Customer details:</b><br/><br/>". $values->fullname ."<br/>" .$values->email ."<br/>" .$values->phone . "<br/><br/><br/>";

                $space = false;
                if(isset($values->bundleId) && !empty($values->bundleId)) {
                    $space = true;
                    $message .= "<b>BundleId: </b>" . $values->discount_code . "<br/>";
                }
                if(isset($values->discount_code) && !empty($values->discount_code)) {
                    $space = true;
                    $message .= "<b>Discount code:</b>" . $values->discount_code . "<br/>";
                }
                if($space === true){
                    $message .= "<br/><br/><br/>";
                }

                $message .= "<b>Ordered items (prices do not include discount code):</b><br/><br/>";

                $message .= "<table>";
                $message .= "<thead>";
                $message .= "<tr>";
                $message .= "<td style='padding: 0.5em 1em; font-weight: bold;padding-left: 0'>Name</td>";
                $message .= "<td style='padding: 0.5em 1em; font-weight: bold'>Product code</td>";
                $message .= "<td style='padding: 0.5em 1em; font-weight: bold'>Quantity</td>";
                $message .= "<td style='padding: 0.5em 1em; font-weight: bold'>Price/piece (Ex Vat)</td>";
                $message .= "<td style='padding: 0.5em 1em; font-weight: bold'>Total price (Ex Vat)</td>";
                $message .= "</thead>";
                $message .= "<tbody>";
                $message .= "</tr>";
                $totalPriceExVat = 0;
                foreach ($products as $item) {
                    $message .= "<tr>";

                    $productId = $item["product"];
                    $product = $this->fastSpringCacheModel->getProduct($productId);
                    $name = $product["display"][$defaultLanguage];
                    $piecePrice = $product["pricing"]["price"][$defaultCurrency];
                    
                    $totalProductPriceExVat = round(($piecePrice ?? 1) * $item["quantity"], 2);

                    $message .= "<td style='padding: 0 1em;padding-left: 0'>".$name ."</td>";
                    $message .= "<td style='padding: 0 1em;'>".$productId."</td>";
                    $message .= "<td style='padding: 0 1em;'>".$item["quantity"]."x</td>";
                    $message .= "<td style='padding: 0 1em;'>$".$piecePrice ?? '-' ."</td>";
                    $message .= "<td style='padding: 0 1em;'>$".$totalProductPriceExVat."</td>";
                    $message .= "</tr>";

                    $totalPriceExVat = round($totalPriceExVat + $totalProductPriceExVat, 2);

                }
                $message .= "</tbody>";
                $message .= "</table><br/>";
                $message .= "<b>Total price (Ex Vat):</b> $" . $totalPriceExVat . "</br>";

                $message .= "<br/><br/>";

                $mail = new Message;

                if(isset($values->email) && !empty($values->email)){
                    $replyTo = $values->email;
                } else {
                    $replyTo = "noreply@24uSoftware.com";
                }

                $mail->setFrom($replyTo, $values->fullname)
                    ->setSubject("Order from " .$host. $uri)
                    ->setHtmlBody($message);

                if(isset($this->applicationConfig->getConfig()["extra-orders-sent-to-emails"]) && !empty($this->applicationConfig->getConfig()["extra-orders-sent-to-emails"])){
                    foreach($this->applicationConfig->getConfig()["extra-orders-sent-to-emails"] as $email) {
                        $mail->addTo($email);
                    }
                }

                $result = $this->mailer->send($mail);

                $this->result = null;
            } else {
                $result = $this->fastSpringModel->createSession($data, $countryISO, $langISO);

                $response = $result["response"];
                if (!isset($response["id"]) || empty($response["id"])) {
                    throw new \Exception(json_encode($result));
                }

                $this->result = $response;
            }
        } catch (AbortException $ex) {
            throw $ex;
        } catch (\Exception $e) {
            $errors = json_decode($e->getMessage(), true);
            if(is_array($errors)){
                if(isset($errors["response"]["validation_errors"][0]["code"])){
                    $form->addError($errors["response"]["validation_errors"][0]["code"]);
                } else {
                    $form->addError("Unexepted error");
                }
            } else {
                $form->addError("Unexepted error");
            }
            
            /** @var \Tracy\ILogger $this->logger */
            $this->logger->error("Store order - unsuccesfull redirect to order n." . "\nResult: " . $e->getMessage());
        }

        return true;
    }

    /**
     * @param mixed $addCustomFieldBundleId
     */
    public function setAddCustomFieldBundleId(): void
    {
        $this->addCustomFieldBundleId = true;
    }

    /**
     * @return mixed
     */
    public function getAddCustomFieldBundleId()
    {
        return $this->addCustomFieldBundleId;
    }
}
