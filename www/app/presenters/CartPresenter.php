<?php

namespace App\Presenters;

use App\Model\Db\OrdersModel;
use App\Service\Config\ApplicationConfig;
use Monolog\Logger;

use Contributte\Translation\Translator; use Contributte;

use Nette\Application\AbortException;
use Nette\Http\Request;

class CartPresenter extends \Nette\Application\UI\Presenter {
    CONST TRANSLATE_PATH = "generic.";

    CONST SUCCESS = "success";
    CONST ERROR = "error";

    private $request;
    private $ordersModel;
    private $applicationConfig;
    private $httpRequest;
    private $logger;

    /** @persistent */
    public $locale;

    // SERVICES & MODELS
    protected $translator;

    public function __construct(Request $request, OrdersModel $ordersModel, ApplicationConfig $applicationConfig, Logger $logger, Translator $translator, Request $httpRequest)
    {
        $this->request = $request;
        $this->ordersModel = $ordersModel;
        $this->applicationConfig = $applicationConfig;
        $this->translator = $translator;
        $this->httpRequest = $httpRequest;
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
    public function actionCreate()
    {
        if ($this->request->isMethod('POST')) {

            // CHECK REQUEST AUTH
            if ($this->authRequest()) {

                // LOAD REQUEST PARAMETER
                $parameters = $this->request->getPost();
                $rawData = $this->request->getRawBody();

                if (!empty($rawData)) {

                    // CHECK IF RECEIVED JSON IS VALID
                    $items = json_decode($rawData, true);

                    if (is_array($items)) {

                        // ADD JSON TO DATABASE AND RETURN RECORD ID
                        $result = $this->ordersModel->createRecord([
                            "data" => $rawData
                        ]);

                        echo $this->result(self::SUCCESS, $result->id);
                    } else {
                        echo $this->result(self::ERROR, "Invalid json");
                    }
                } else {
                    echo $this->result(self::ERROR, "Invalid request parameters");
                }
            } else {
                echo $this->result(self::ERROR, "Invalid request auth");
            }
        } else {
            echo $this->result(self::ERROR, "Invalid request method");
        }
        exit();
    }

    /**
     * Load cart from database by Id
     * @param $orderId
     */
    public function actionGet($orderId)
    {
        // CHECK REQUEST AUTH
        if ($this->authRequest()) {

            $result = $this->ordersModel->getRecord($orderId);

            if ($result) {
                echo $this->result(self::SUCCESS, json_decode($result["data"]));
            } else {
                echo $this->result(self::SUCCESS, "Cart doesnt't exist");
            }
        } else {
            echo $this->result(self::ERROR, "Invalid request auth");
        }
        exit();
    }


    /**
     * Cancel Cart ( Remove record from database by Id )
     * @param $orderId
     */
    public function actionCancel($orderId)
    {
        // CHECK REQUEST AUTH
        if ($this->authRequest()) {

            $result = $this->ordersModel->deleteRecord($orderId);
            if ($result) {
                echo $this->result(self::SUCCESS, "Cart was cancled");
            } else {
                echo $this->result(self::SUCCESS, 'Delete was unsuccesfull');
            }
        } else {
            echo $this->result(self::ERROR, "Invalid request auth");
        }
        exit();
    }

    /**
     * Old method that is not use anymore. It was working on MyCommerce, but on FastSpring is not working anymore. Removed at 27.02.2025 (git)
     * @param $orderId
     * @throws \Nette\Application\AbortException
     */
    public function actionOrder($orderId)
    {
        // TODO MESSAGE
        $this->redirect("Support:default");
        exit();
    }
}
