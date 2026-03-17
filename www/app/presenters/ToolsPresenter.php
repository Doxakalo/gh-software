<?php

namespace App\Presenters;


use App\Forms\ContactForm;
use App\Forms\StoreForm;
use App\Forms\SubscribeForm;
use App\Model\FastSpringModel;
use App\Models\FileMaker\ResultModel;
use App\Models\FileMaker\RunsModel;
use App\Service\Config\ApplicationConfig;
use App\Service\Stores;
use Contributte;
use Contributte\Translation\Translator;
use League\HTMLToMarkdown\HtmlConverter;
use Nette\Application\UI\Form;
use stringEncode\Exception;

class ToolsPresenter extends BasePresenter
{
    // MODELS & SERVICES
    private $stores;
    private $resultModel;
    private $runsModel;

    // FORMS
    private $storeForm;

    // OTHER
    //private $contactFormSentData;

    public function __construct(SubscribeForm $subscribeForm, Translator $translator, ApplicationConfig $applicationConfig, Stores $stores, StoreForm $storeForm, ContactForm $contactForm, ResultModel $resultModel, RunsModel $runsModel, FastSpringModel $fastSpringModel)
    {
        parent::__construct($subscribeForm, $translator, $applicationConfig, $contactForm);
        $this->stores = $stores;
        $this->storeForm = $storeForm;
        $this->contactForm = $contactForm;
        $this->resultModel = $resultModel;
        $this->runsModel = $runsModel;
        $this->fastSpringModel = $fastSpringModel;

    }

    public function actionList()
    {
        $this->template->ignoreSearch = "1";
    }

    // Swiper test - remove after development
    public function actionSwiperTest()
    {
        $this->template->toolName = "Gonector";
        $this->template->pageType = "product_page";

        $this->template->templateCustomName = $this->templateCustomNameToolsDetail();
    }

    // Simple HASP
    public function actionSimpleHASP()
    {
        $this->customPageName = "SimpleHASP";
        $this->productCode = "shs";
        $this->subscribeForm->setListTag(strtoupper($this->productCode));
        $store = $this->stores->loadStoreItemsDetail($this->stores->getStoreDetail($this->productCode));
        $this->template->store = $store;
        $this->template->toolName = "SimpleHASP";
        $this->template->templateCustomName = $this->templateCustomNameToolsDetail();
        $this->template->pageType = "product_page";
    }

    public function renderSimpleHASP()
    {

    }

    // Simple Sound
    public function actionSimpleSound()
    {
        $this->customPageName = "SimpleSound";
        $this->productCode = "snd";
        $this->subscribeForm->setListTag(strtoupper($this->productCode));
        $store = $this->stores->loadStoreItemsDetail($this->stores->getStoreDetail($this->productCode));
        $this->template->store = $store;
        $this->template->toolName = "SimpleSound";
        $this->template->pageType = "product_page";

        $this->template->templateCustomName = $this->templateCustomNameToolsDetail();
    }

    // Toolbox
    public function actionToolbox()
    {
        $this->customPageName = "Toolbox";
        $this->productCode = "tbx";
        $this->subscribeForm->setListTag(strtoupper($this->productCode));

        $store = $this->stores->loadStoreItemsDetail($this->stores->getStoreDetail($this->productCode));

        $this->template->store = $store;
        $this->template->toolName = "Toolbox";
        $this->template->pageType = "product_page";

        $this->template->templateCustomName = $this->templateCustomNameToolsDetail();
    }

    // Toolbox
    public function actionTracksy()
    {
        $this->customPageName = "Tracksy";
        $this->template->toolName = "Tracksy";
        $this->template->pageType = "product_page";
    }

    // SimpleTalk
    public function actionSimpleTalk()
    {
        $this->customPageName = "SimpleTalk";
        $this->productCode = "stl";
        $this->subscribeForm->setListTag(strtoupper($this->productCode));
        $store = $this->stores->loadStoreItemsDetail($this->stores->getStoreDetail($this->productCode));
        $this->template->store = $store;
        $this->template->toolName = "SimpleTalk";
        $this->template->pageType = "product_page";

        $this->template->templateCustomName = $this->templateCustomNameToolsDetail();
    }

    // Bridge for Phidgets
    public function actionBridgeForPhidgets()
    {
        $this->customPageName = "Bridge for Phidgets";
        $this->productCode = "phd";
        $this->subscribeForm->setListTag(strtoupper($this->productCode));
        $store = $this->stores->loadStoreItemsDetail($this->stores->getStoreDetail($this->productCode));
        $this->template->store = $store;
        $this->template->toolName = "Bridge for Phidgets";
        $this->template->pageType = "product_page";

        $this->template->templateCustomName = $this->templateCustomNameToolsDetail();
    }

    // Bridge for Arduino
    public function actionBridgeForArduino()
    {
        $this->customPageName = "Bridge for Arduino";
        $this->productCode = "ard";
        $this->subscribeForm->setListTag(strtoupper($this->productCode));
        $store = $this->stores->loadStoreItemsDetail($this->stores->getStoreDetail($this->productCode));
        $this->template->store = $store;
        $this->template->toolName = "Bridge for Arduino";
        $this->template->pageType = "product_page";

        $this->template->templateCustomName = $this->templateCustomNameToolsDetail();
    }

    // Phone Companion
    public function actionPhoneCompanion()
    {
        $this->customPageName = "PhoneCompanion";
        $this->productCode = "phc";
        $this->subscribeForm->setListTag(strtoupper($this->productCode));
        $store = $this->stores->loadStoreItemsDetail($this->stores->getStoreDetail($this->productCode));
        $this->template->store = $store;
        $this->template->toolName = "PhoneCompanion";
        $this->template->pageType = "product_page";

        $this->template->templateCustomName = $this->templateCustomNameToolsDetail();
    }

    public function actionImportProducts(){
        $config = $this->stores->storesConfig->getConfig();
        $cacheDataRaw = file_get_contents(__DIR__ . "/cache-data.txt");
        $cacheData = json_decode($cacheDataRaw, true);
        $alreadyCreatedProducts = [];
        //$alreadyCreatedProducts = $this->fastSpringModel->getAllProductsPath();

        foreach($config as $productKey => $productProp){
            echo "<h1>" . $productKey . "</h1>";

            foreach($productProp["sections"] as $key => $sectionProp){
                echo "<h2>" . $sectionProp["setup"]["headline"] . "</h2><br>";

                foreach($sectionProp["items"] as $id => $itemProp){


                    if(isset($cacheData[$id])){
                        $product = $cacheData[$id];
                        $productCode = $product['product_code'];
                    } else {

                        $product = ((!empty($productsFiltered = array_filter($cacheData, function ($product) use ($id) {
                            return isset($product['product_code']) && $product['product_code'] === $id;
                        }))) ? reset($productsFiltered) : null);
                        $productCode = $id;
                    }

                    $converter = new HtmlConverter();

                    if(!in_array($productCode, $alreadyCreatedProducts)){
                        echo "<h4>" . $id . " / ".$product["product_code"] . " / " . $product["name"] ."</h4> <br>";
                        $data = [
                            "id" => $product["id"],
                            "code" => $product["product_code"],
                            "name" => $product["name"],
                            "image-url" => "https:" . $product["image_url"],
                            "description" => $product["descriptions"][0]["short_description"]
                        ];


                        if(isset($product["descriptions"][0]["full_description"])){
                            $longDescription = $product["descriptions"][0]["full_description"];
                            $longDescription = str_replace("<p>", "<br>", $longDescription);
                            $longDescription = str_replace("</p>", "<br><br>", $longDescription);
                            //$longDescriptionRaw = str_replace("<b>", "", $longDescription);
                            //$longDescriptionRaw = str_replace("</b>", "", $longDescriptionRaw);
                            $data["long-description"] = $converter->convert($longDescription);
                        }


                        $data["type"] = (isset($product["subscription"]) ? "subscription" : "product");

                        if(isset($product["subscription"])){
                            $data["subscription"] = [
                                "months" => $product["subscription"]["period"],
                            ];
                        }
                        if(isset($product["minimum_quantity"])){
                            $data["minimum_quantity"] = $product["minimum_quantity"];
                        }
                        if(isset($product["maximum_quantity"])){
                            $data["maximum_quantity"] = $product["maximum_quantity"];
                        }
                        $data["default-prices"] =  $product["prices"][0]["total"];

                        if($product["has_volume_discount"]){
                            $volumeDiscounts = $product["prices"][0]["volume_discounts"];
                            foreach($volumeDiscounts as $quantity => $discount){
                                $percentageDiscount = (($data["default-prices"] - $discount["extra_price"])/$data["default-prices"] * 100);
                                $priceDiscount = ($data["default-prices"] - $discount["extra_price"]);
                                $volumeDiscounts[$quantity]["perc-discount"] = $percentageDiscount;
                                $volumeDiscounts[$quantity]["price-discount"] = $priceDiscount;
                            }
                            $data["discount-prices"] = $volumeDiscounts;
                        }
                        echo "<pre style='width: 800px;word-wrap: break-word; white-space: pre-wrap; overflow-wrap: break-word;'>";
                        var_dump($data);
                        echo "</pre>";
                        $productIdsInList[] = $productCode;
                    } else {
                        $warning = false;
                        if(isset($product["minimum_quantity"])){
                            if(!isset($itemProp["min_quantity"]) || $product["minimum_quantity"] !== $itemProp["min_quantity"]){
                                $warning = true;
                            }
                        }

                        if(isset($product["maximum_quantity"])){
                            if(!isset($itemProp["max_quantity"]) || $product["maximum_quantity"] !== $itemProp["max_quantity"]){
                                $warning = true;
                            }
                        }

                        if($warning){
                            echo "<p style='color: rgba(220,119,78,0.39);'>" . $id . " / ".$productCode . " / " . $product["name"] ?? "-" ." - Nesprávné nastavení počtu kusů v konfiguračním souborů</p>";
                            if(isset($product["minimum_quantity"])) {
                                echo "<br>min_quantity: " . $product["minimum_quantity"];
                            }
                            if(isset($product["maximum_quantity"])) {
                                echo "<br>max_quantity: " . $product["maximum_quantity"];
                            }

                        } else {
                            echo "<p style='color: rgba(0,128,0,0.39);'>" . $id . " / ".$productCode . " / " . $product["name"] ?? "-" ." - Produkt je již vytvořen</p>";
                        }
                    }

                }
                echo "<br><br>";

            }


            echo "<br><br><br><br>";
        }

        exit();
    }

    // Simple File
    public function actionSimpleFile()
    {
        $this->customPageName = "SimpleFile";
        $this->productCode = "sfi";
        $this->subscribeForm->setListTag(strtoupper($this->productCode));
        $store = $this->stores->loadStoreItemsDetail($this->stores->getStoreDetail($this->productCode));
        $this->template->store = $store;
        $this->template->toolName = "SimpleFile";
        $this->template->pageType = "product_page";

        $this->template->templateCustomName = $this->templateCustomNameToolsDetail();
    }

    // SimpleDialog
    public function actionSimpleDialog()
    {
        $this->customPageName = "SimpleDialog";
        $this->productCode = "sdi";
        $this->subscribeForm->setListTag(strtoupper($this->productCode));
        $store = $this->stores->loadStoreItemsDetail($this->stores->getStoreDetail($this->productCode));

        $this->template->store = $store;
        $this->template->toolName = "SimpleDialog";
        $this->template->pageType = "product_page";

        $this->template->templateCustomName = $this->templateCustomNameToolsDetail();
    }

    // 24U FM Bench
    public function actionBench()
    {
        $this->customPageName = "FM Bench";
        $this->productCode = "fmb";
        $this->subscribeForm->setListTag(strtoupper($this->productCode));
        $store = $this->stores->loadStoreItemsDetail($this->stores->getStoreDetail($this->productCode));
        $this->template->store = $store;
        $this->template->toolName = "FM Bench";
        $this->template->pageType = "product_page";

        $this->template->templateCustomName = $this->templateCustomNameToolsDetail();
    }

    // fmRESTor
    public function actionFmRESTor()
    {
        $this->customPageName = "fmRESTor";
        $this->productCode = "fmr";
        $this->subscribeForm->setListTag(strtoupper($this->productCode));
        $store = $this->stores->loadStoreItemsDetail($this->stores->getStoreDetail($this->productCode));
        $this->template->store = $store;
        $this->template->toolName = "fmRESTor";
        $this->template->pageType = "product_page";

        $this->template->templateCustomName = $this->templateCustomNameToolsDetail();
    }

    // BenchTest
    public function actionBenchTest()
    {
        $this->customPageName = "BenchTest";
        $this->productCode = "bts";
        $this->subscribeForm->setListTag(strtoupper($this->productCode));
        $store = $this->stores->loadStoreItemsDetail($this->stores->getStoreDetail($this->productCode));
        $this->template->store = $store;
        $this->template->toolName = "BenchTest";
        $this->template->pageType = "product_page";

        $this->template->templateCustomName = $this->templateCustomNameToolsDetail();


    }

    public function actionBenchTestResults(){
        ini_set('memory_limit', '999M');
        set_time_limit(0);
        $this->setLayout("layoutBasic");

        $privateDataFolder = dirname(__DIR__, 2) . "/www/private-data/benchtest/data.json";

        $data = json_decode(file_get_contents($privateDataFolder), true);

        $score = [];
        foreach($data as $record){
            $score[] = $record["score"];
        }
        $this->template->minScore = min($score);
        $this->template->maxScore = max($score);

        $this->template->benchTestData = $data;
    }

    public function actionBenchTestResultDetail($id){
        ini_set('memory_limit', '999M');
        set_time_limit(0);
        $this->setLayout("layoutBasic");

        $privateDataFolder = dirname(__DIR__, 2) . "/www/private-data/benchtest/data.json";

        $runScriptScore = [];
        $data = json_decode(file_get_contents($privateDataFolder), true);

        foreach($data as $record){
            $scripts = $record["script_run"];

            foreach($scripts as $script){
                $runScriptScore[] = $script["cx_score"];
            }
        }

        $this->template->id = $id;
        $this->template->minScore = min($runScriptScore);
        $this->template->maxScore = max($runScriptScore);
        $this->template->benchTestData = $data;
    }

    public function actionBenchTestCache($token)
    {
        try {
            $force = false;
            if (isset($_GET["force"]) && $_GET["force"] === "true") {
                $force = true;
            }
            ini_set('memory_limit', '999M');
            set_time_limit(0);

            if ($token !== "6d6av987h96c6p9r83vdsfc64761xc8vx6v2a7q96") {
                echo "Invalid auth token";
                exit();
            }

            $privateDataFolder = dirname(__DIR__, 2) . "/www/private-data/benchtest";
            $dateCachePath = $privateDataFolder . "/data.json";

            $data = [];
            if($force === false) {
                if (file_exists($dateCachePath)) {
                    $currentCacheDataRaw = file_get_contents($dateCachePath);

                    if (!empty($currentCacheDataRaw)) {
                        $currentCacheDataArray = json_decode($currentCacheDataRaw, true);
                        if (!empty($currentCacheDataArray)) {
                            $data = $currentCacheDataArray;
                        }
                    }
                }
            }

            $formatDateForLastUpdate = "m/d/Y H:i:s";


            $dateTimeLastUpdate = null;
            $currentDateTime = new \DateTime();
            if (file_exists($privateDataFolder . "/last-update.txt")) {
                $lastUpdateDateTimeString = file_get_contents($privateDataFolder . "/last-update.txt");
                if (!empty($lastUpdateDateTimeString)) {
                    $dateTimeLastUpdate = \DateTime::createFromFormat($formatDateForLastUpdate, $lastUpdateDateTimeString);
                    if (empty($dateTimeLastUpdate)) {
                        $dateTimeLastUpdate = null;
                    }
                }
            }

            $queryRuns = [
                [
                    "result_set_valid" => 1
                ]
            ];

            if ($dateTimeLastUpdate !== null && $force === false) {
                $queryRuns[0]["_modified"] = "> " . $dateTimeLastUpdate->format($formatDateForLastUpdate);
            }

            $runsRaw = $this->runsModel->getRecordsByCriterions($queryRuns);

            if($this->runsModel->fm->isError($runsRaw)){
                if(!$this->runsModel->fm->isRecordExist($runsRaw)){
                    $this->resultExit([
                        "status" => "ok",
                        "message" => "Nebyla nalezena žádná data k aktualizaci"
                    ]);
                    exit();
                } else {
                    $this->runsModel->fm->isError($runsRaw, true);
                }
            }

            $runsResponse = $this->runsModel->fm->getResponse($runsRaw);

            $loadResultsForRunIds = [];
            $sort = [];
            if(isset($runsResponse["response"]["data"]) && !empty($runsResponse["response"]["data"])) {
                foreach ($runsResponse["response"]["data"] as $record) {
                    $run = $record["fieldData"];

                    $loadResultsForRunIds[] = $run["id"];


                    $data[$run["id"]] = [
                        "id" => $run["id"],
                        "test_set" => $run["test_set"],
                        "notes" => $run["notes"],
                        "contact_name" => $run["contact_name"],
                        "started" => $run["started"],
                        "finished" => $run["finished"],
                        "submitted" => $run["submitted"],
                        "environment" => json_decode($run["cx_environment_json"], true),
                        "score" => $run["cx_score"],
                        "url" => $run["url"],
                        "url_approved" => $run["url_approved"],
                        "script_run" => [],
                        "extract_result" => $run["extract_result"],
                        "tab" => $run["c_web_tab"]
                    ];
                }
            }
            $index = 0;
            foreach($data as $id => $run){
                $dataIndexed[$index] = $run;
                $sort["score"][$index] = $run["score"];
                $index = $index +1;
            }

            array_multisort(
                $sort["score"],
                SORT_DESC,
                $dataIndexed
            );
            $tmpData = [];
            foreach($dataIndexed as $key => $run){
                $tmpData[$run["id"]] = $run;
            }
            $data = $tmpData;

            if (!empty($loadResultsForRunIds) && $force === false) {
                foreach($loadResultsForRunIds as $runId){
                    $queryRusults[] = [
                        "run_id" => $runId
                    ];
                }

                $resultRaw = $this->resultModel->getRecordsByCriterions($queryRusults);
            } else {
                $resultRaw = $this->resultModel->getRecords();
            }

            if($this->resultModel->fm->isError($resultRaw)){
                if(!$this->resultModel->fm->isRecordExist($resultRaw)){
                    $this->resultExit([
                        "status" => "ok",
                        "message" => "Ač mělo dojít k obnově cache z důvodu, že došlo ke změně/přidání nových runů, tak se nepovedlo načíst výsledky k jednotlivým runům"
                    ]);
                    exit();
                } else {
                    $this->resultModel->fm->isError($resultRaw, true);
                }
            }
            $resultResponse = $this->resultModel->fm->getResponse($resultRaw);
            if(isset($resultResponse["response"]["data"]) && !empty($resultResponse["response"]["data"])) {
                foreach ($resultResponse["response"]["data"] as $record) {
                    $scriptRun = $record["fieldData"];

                    if (!empty($scriptRun["run_id"])) {
                        if (isset($data[$scriptRun["run_id"]])) {
                            $data[$scriptRun["run_id"]]["script_run"][] = $scriptRun;
                        }
                    }
                }
            }


            if (!file_exists($privateDataFolder)) {
                $oldmask = umask(0);
                mkdir($privateDataFolder, 0777);
                umask($oldmask);
            }

            $putTimestampForLastUpdateCacheResult = @file_put_contents($privateDataFolder . "/last-update.txt", $currentDateTime->format($formatDateForLastUpdate));

            if($putTimestampForLastUpdateCacheResult === false){
                throw new Exception("Nepovedlo se uložit soubor obsahující timestamp poslední obnovy cache", -9);
            }

            $putDataToCacheResult = @file_put_contents($privateDataFolder . "/data.json", json_encode($data));

            if($putDataToCacheResult === false){
                throw new Exception("Nepovedlo se uložit soubor obsahující data pro cache", -10);
            }
        } catch (\Exception $e){
            $this->sendJson([
                "status" => "error",
                "code" => $e->getCode(),
                "message" => $e->getMessage()
            ]);
        }
        $this->sendJson([
            "status" => "ok",
            "message" => "Aktualizace cache souboru proběhla úspěšně"
        ]);
        exit();
    }

    private function resultExit($data){
        header('Content-Type: application/json; charset=utf-8');

        echo json_encode($data);
        exit();
    }


    // LogMonitor
    public function actionLogMonitor()
    {
        $this->customPageName = "LogMonitor";
        $this->productCode = "lmr";
        $this->subscribeForm->setListTag(strtoupper($this->productCode));
        $store = $this->stores->loadStoreItemsDetail($this->stores->getStoreDetail($this->productCode));
        $this->template->store = $store;
        $this->template->toolName = "LogMonitor";
        $this->template->pageType = "product_page";

        $this->template->templateCustomName = $this->templateCustomNameToolsDetail();
    }

    // GONECTOR
    public function actionGonector()
    {
        $this->customPageName = "Gonector";
        $this->productCode = "gon";
        $this->subscribeForm->setListTag(strtoupper($this->productCode));
        $store = $this->stores->loadStoreItemsDetail($this->stores->getStoreDetail($this->productCode));
        $this->template->store = $store;
        $this->template->toolName = "Gonector";
        $this->template->pageType = "product_page";

        $this->template->templateCustomName = $this->templateCustomNameToolsDetail();

        $this->storeForm->setAddCustomFieldBundleId();
    }

    // Xgode
    public function actionXgode()
    {
        $this->customPageName = "Xgode";
        $this->productCode = "xgd";
        $this->subscribeForm->setListTag(strtoupper($this->productCode));
        $store = $this->stores->loadStoreItemsDetail($this->stores->getStoreDetail($this->productCode));
        $this->template->store = $store;
        $this->template->pageType = "product_page";

        $this->template->templateCustomName = $this->templateCustomNameToolsDetail();
        $this->template->toolName = "Xgode";




    }

    private function templateCustomNameToolsDetail()
    {
        return "tools-detail";
    }

    protected function createComponentStoreForm()
    {

        $form = $this->storeForm->create();


        if (isset($_GET["coupon"]) && !empty($_GET["coupon"])) {
            $coupon = $_GET["coupon"];

            /** @var \Nette\Form\Control\TextInput $formDiscountCode */
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

        if (isset($this->customPageName)) {
            $this->contactForm->setCurrentPage($this->presenter->getName() . ' [' . $this->customPageName . ']');
        } else {
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
        };

        $form->onSuccess[] = function () {
            $this->template->contactAdditionalInformationSuccess = true;
            $this->redrawControl('contactForm');
        };

        return $form;
    }

    protected function createComponentContactForm()
    {
        $form = $this->contactForm->create();

        if (isset($this->customPageName)) {
            $this->contactForm->setCurrentPage($this->presenter->getName() . ' [' . $this->customPageName . ']');
        } else {
            $this->contactForm->setCurrentPage($this->presenter->getName());
        }

        $this->contactForm->setNumberPrefix($this->numberPrefix());
        $form = $this->addNumberPrefix($form);

        $form["type"]->setDefaultValue("call");

        $form["name"]->setRequired($this->translator->translate(ContactForm::TRANSLATE_PATH . "items.name.validation.required"));
        $form["number"]->setRequired($this->translator->translate(ContactForm::TRANSLATE_PATH . "items.number.validation.required"));
        $form["topic"]->setRequired($this->translator->translate(ContactForm::TRANSLATE_PATH . "items.topic.validation.required"));
        $form["time"]->setRequired($this->translator->translate(ContactForm::TRANSLATE_PATH . "items.time.validation.required"));

        $form->onSuccess[] = function ($form) {
            $this->template->contactFormSuccess = true;
            $this->setContactFormSentData($form->getValues());
            $this->redrawControl('contactForm');
        };

        $this->redrawControl('contactForm');

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
}
