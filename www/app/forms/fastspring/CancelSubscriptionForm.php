<?php

namespace App\Forms;

use App\Model\FastSpringModel;
use Nette\Application\UI\Form;
use App\Service\Config\ApplicationConfig;
use Contributte\Translation\Translator;
use Monolog\Logger;

use Contributte;

use Nette\Application\AbortException;
use Nette\Mail\Mailer;
use Nette\Mail\Message;
use Nette\Http\Request;


class CancelSubscriptionForm
{
    public $result;
    private $logger;
    private $mailer;
    public $httpRequest;

    private $translator;
    public $mailChimpModel;
    public $fastSpringModel;
    public $applicationConfig;

    /** @persistent */
    public $locale;

    private $numberOfActiveSubscription = 0;
    private $subscriptionToCancel = [];

    public function __construct(Logger $logger, Mailer $mailer, Translator $translator, Request $httpRequest, ApplicationConfig $applicationConfig, FastSpringModel $fastSpringModel)
    {
        $this->translator = $translator;
        $this->logger = $logger;
        $this->mailer = $mailer;
        $this->httpRequest = $httpRequest;
        $this->applicationConfig = $applicationConfig;
        $this->fastSpringModel = $fastSpringModel;
    }

    public function create()
    {
        $form = new Form;

        $form->addText("order_number", "Order number")
            ->setHtmlAttribute("placeholder", "Order number");


        $form->addText("email", "E-mail")
            ->setHtmlAttribute("placeholder", "Your e-mail")
            ->addCondition(Form::Filled)
            ->addRule(Form::Email, 'Please enter valid e-mail address')
            ->endCondition();

        $labelSubmitButton = "Cancel subscription";
        $form->addSubmit("process", $labelSubmitButton)
            ->setHtmlAttribute("class", "button")
            ->setHtmlAttribute("data-default-label", $labelSubmitButton);

        $form->onSuccess[] = array($this, 'process');

        return $form;
    }

    public function process(Form $form, $values)
    {
        $orderNumber = !empty($values->order_number) ? $values->order_number : null;
        $email = !empty($values->email) ? $values->email : null;


        if($orderNumber === null && $email === null){

            $form->addError("Please fill in at least one field");
            return $form;
        }

        $orders = [];

        $subscriptionsToCancel = [];

        $activeSubscription = $this->fastSpringModel->getAllSubscriptions(["status" => "active"]);

        if (!empty($orderNumber)) {
            //$ordersRaw = $this->fastSpringModel->getOrderById($orderNumber);
            //$ordersRaw = $this->fastSpringModel->getAllOrders();

            foreach($activeSubscription as $subscription) {
                if($subscription["state"] !== "active"){
                    continue;
                }
                if($subscription["initialOrderReference"] === $orderNumber || $subscription["initialOrderId"] === $orderNumber) {
                    $subscription["account-prop"] = $this->fastSpringModel->getAccountById($subscription["account"]);

                    $subscriptionsToCancel[] = $subscription;
                    if(empty($email)){
                        $email = $subscription["account-prop"]["contact"]["email"];
                    }
                }
            }
        } elseif (!empty($email)) {
            $accountsRawBasedOnEmail = $this->fastSpringModel->getAllAccountsBasedOnEmail($email);

            $accountsBasedOnEmail = [];
            if(!empty($accountsRawBasedOnEmail)){
                foreach($accountsRawBasedOnEmail as $account){
                    $accountsBasedOnEmail[$account["id"]] = $account;
                }
            }

            foreach($activeSubscription as $subscription) {
                if($subscription["state"] !== "active"){
                    continue;
                }
                if(isset($accountsBasedOnEmail[$subscription["account"]])) {
                    $subscription["account-prop"] = $accountsBasedOnEmail[$subscription["account"]];
                    $subscriptionsToCancel[] = $subscription;
                }
            }
        }

        $numberOfSubscriptionForCancel = count($subscriptionsToCancel);
        $this->setNumberOfActiveSubscription($numberOfSubscriptionForCancel);
        $this->setSubscriptionToCancel($subscriptionsToCancel);


        if ($numberOfSubscriptionForCancel === 1) {
            $resultCancelSubscription = $this->fastSpringModel->cancelSubscription($subscriptionsToCancel[0]["id"]);
        }

        if ($numberOfSubscriptionForCancel > 1 || isset($resultCancelSubscription) && $resultCancelSubscription === false) {

            $mail = new Message;
            $replyTo = $email;


            $message = "Someone has requested subscription cancellation but has more than one active subscription. Please contact the customer ASAP to follow up and help them to cancel the correct subscription(s).<br/><br/>.";
            if (!empty($email)) {
                $message .= "User e-mail: " . $email;
            } else {
                $message .= "Order number: " . $orderNumber;
            }

            $message .= "<br><br>User Subscriptions:<br><br>";
            $message .= "<table style='text-align: left'>";
            $message .= "<thead>";
            $message .= "<tr><th>Product</th><th>ID</th><th>Status</th></tr>";
            $message .= "<thead>";
            $message .= "<tbody>";

            foreach ($subscriptionsToCancel as $subscription) {
                $message .= "<tr><td style='text-align: left'>" . $subscription["display"] . "</td><td style='text-align: left'>" . $subscription["id"] . "</td><td style='text-align: left'> " . ($subscription["active"] ? "Active" : "In-active") . " </td></tr>";
            }
            $message .= "</tbody>";
            $message .= "</table>";
            $message .= "<br><br>";


            $mail->setFrom($replyTo, $replyTo . ' (by way of 24usoftware.com)')
                ->setSubject("Cancel subscription")
                ->setHtmlBody($message);

            foreach($this->applicationConfig->getConfig()["sent-cancel-subscription-notice-to-emails"] as $email) {
                $mail->addTo($email);
            }
            if (isset($replyTo) && !empty($replyTo)) {
                $mail->addReplyTo($replyTo);
            }

            $this->mailer->send($mail);
        }


        return true;
    }

    /**
     * @param int $numberOfActiveSubscription
     */
    public function setNumberOfActiveSubscription(int $numberOfActiveSubscription): void
    {
        $this->numberOfActiveSubscription = $numberOfActiveSubscription;
    }

    /**
     * @return int
     */
    public function getNumberOfActiveSubscription(): int
    {
        return $this->numberOfActiveSubscription;
    }

    /**
     * @param array $subscriptionToCancel
     */
    public function setSubscriptionToCancel(array $subscriptionToCancel): void
    {
        $this->subscriptionToCancel = $subscriptionToCancel;
    }

    /**
     * @return array
     */
    public function getSubscriptionToCancel(): array
    {
        return $this->subscriptionToCancel;
    }
}
