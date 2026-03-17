<?php

namespace App\Presenters;

use App\Model\Db\OrdersModel;
use App\Model\FastSpringCacheModel;
use App\Model\FastSpringModel;
use App\Model\MailChimpModel;
use App\Model\XgodeModel;
use App\Service\Config\ApplicationConfig;
use Monolog\Logger;
use Contributte\Translation\Translator; use Contributte;

use Nette\Application\AbortException;
use Nette\Http\Request;

class StorePresenter extends \Nette\Application\UI\Presenter
{

    private $request;
    private $fastSpringCacheModel;
    private $fastSpringModel;
    private $applicationConfig;
    private $logger;
    private $mailChimpModel;
    private $xgodeModel;

    /** @persistent */
    public $locale;

    // SERVICES & MODELS
    protected $translator;

    public function __construct(Request $request, ApplicationConfig $applicationConfig, Logger $logger, Translator $translator, MailChimpModel $mailChimpModel, XgodeModel $xgodeModel, FastSpringModel $fastSpringModel, FastSpringCacheModel  $fastSpringCacheModel)
    {
        $this->request = $request;
        $this->fastSpringModel = $fastSpringModel;
        $this->fastSpringCacheModel = $fastSpringCacheModel;
        $this->applicationConfig = $applicationConfig;
        $this->translator = $translator;
        $this->mailChimpModel = $mailChimpModel;
        $this->xgodeModel = $xgodeModel;
        $this->logger = $logger;
    }

    /**
     * Result
     * @param $type
     * @param $response
     * @return false|string
     */
    private function result($type, $response)
    {
        header('Content-Type: application/json');
        return json_encode([
            "type" => $type,
            "response" => $response
        ]);
    }

    /**
     * Verificate request auth - basic
     * @return bool
     */
    private function authRequest()
    {
        // REQUEST AUTH
        $requestAuth = $this->request->getHeader("authorization");
        list($username, $password) = explode(':', base64_decode(substr($requestAuth, 6)));

        // CONFIG API AUTH
        $apiAuthCredentials = $this->applicationConfig->getConfig()["api-auth"];

        if ($username !== $apiAuthCredentials["username"] || $password !== $apiAuthCredentials["password"]) {
            return false;
        }

        return true;
    }

    /**
     * Pre-defined cart in database
     */
    public function actionOrderNotification()
    {
        $dataRaw = file_get_contents("php://input");

        if (!empty($dataRaw)) {
            $data = json_decode($dataRaw, true);

            foreach($data["events"] as $eventData) {

                if (isset($eventData["id"])) {
                    if(is_array($eventData["data"]["order"])){
                        $orderData = $eventData["data"]["order"];
                    } else {
                        $orderData = $eventData["data"];
                    }
                    $orderId = $eventData["id"];

                    $billingContact = $orderData["customer"];

                    $date = new \DateTime();
                    $date->setTimestamp($eventData["created"]);


                    foreach ($orderData["items"] as $item) {
                        $product = $item["product"];
                        $quantity = $item["quantity"];
                        $productBasicPart = substr($product, 0, 6);

                        // MAILCHIMP !== XGODE
                        $mailchimpData = array(
                            "email_address" => $billingContact["email"],
                            "status" => "subscribed",
                            "merge_fields" => [
                                "FNAME" => $billingContact["first"],
                                "LNAME" => $billingContact["last"],
                            ]
                        );

                        if ($productBasicPart === "24UXGD") {
                            // XGODE - CALL API WITH SUBSCRIBTION

                            $xgodeData = [];

                            // DEFAULT VALUES
                            $xgodeData['sub_type'] = "month";
                            $xgodeData['sub_count'] = $quantity;

                            // VALUES - DEPENDS ON SUBS TYPE
                            if ($product === "24UXGDIND") {
                                $xgodeData['sub_type'] = "single";
                            } elseif ($product === "24UXGDANN") {
                                $xgodeData['sub_count'] = $quantity * 12;
                            } elseif ($product === "24UXGDQRT") {
                                $xgodeData['sub_count'] = $quantity * 3;
                            }

                            $xgodeData["email"] = $billingContact["email"];
                            $xgodeData["name"] = $billingContact["first"];
                            $xgodeData["surname"] = $billingContact["last"];
                            $xgodeData["purchaseId"] = $orderData["order"];

                            $result = $this->xgodeModel->addSubscription($xgodeData);

                            $this->logger->info("FastSpring - " . $orderId . " -  add Subscription to Xgode - " . $billingContact["email"] . "; Result: " . json_encode($result));
                        } else {
                            $mailchimpData["merge_fields"]["BVERSION"] = substr($product, 6, 1);
                            $mailchimpData["merge_fields"]["BDATE"] = $date->format("Y-m-d");
                        }

                        try {
                            $listConfiguration = $this->mailChimpModel->config["lists"]["24UGENERIC_EN"];
                            $listId = $listConfiguration["id"];

                            $mailchimpData["merge_fields"]["BVERSION"] = substr($product, 6, 1);
                            $mailchimpData["merge_fields"]["BDATE"] = $date->format("Y-m-d");

                            if (isset($listConfiguration["gdpr"])) {
                                foreach ($listConfiguration["gdpr"] as $permissions => $permissionsVal) {
                                    $mailchimpData["marketing_permissions"][] = array(
                                        "marketing_permission_id" => $permissions,
                                        "enabled" => $permissionsVal
                                    );
                                }
                            }

                            $result = $this->mailChimpModel->addSubscriberToList($mailchimpData, $listId);

                            if (isset($result["response"]["title"]) && $result["response"]["title"] === "Member Exists") {
                                // IS OK WHEN USER EXISTS IN DATABASE
                            } else {
                                $this->mailChimpModel->isResultError($result);
                            }

                            // ADD TAG TO USER
                            $listTag = "Bought_" . substr($product, 3, 4);
                            $this->mailChimpModel->setSubscriberTag($billingContact["email"], $listTag, $listId);
                        } catch (\Exception $e) {
                            $this->logger->error("FastSpring - " . $orderId . " -   - Unsuccesfull add user - " . $billingContact["email"] . " to list " . $listId . "; Result: " . json_encode($result));
                        }
                    }
                } else {
                    $this->logger->error("FastSpring - Invalid order - missing orderId");
                }
            }
        } else {
            $this->logger->error("FastSpring - Missing data");
        }

        echo "Done";
        exit();
    }


    public function actionUpdateCache()
    {
        try {
            $this->fastSpringCacheModel->updateCache();

            echo "Update Cache FastSpring - OK";
        } catch (\Exception $e) {
            echo "Update Cache FastSpring - Failed";
        }
        exit();
    }

}
