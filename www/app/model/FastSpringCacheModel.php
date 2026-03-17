<?php

namespace App\Model;

use App\Service\APIService;
use Nette;

class FastSpringCacheModel
{
    private $config;
    private $APIService;
    private $fastSpringModel;

    private $cacheDirPath;

    CONST ERROR_HTTP_CODE = [500, 400, 403, 404, 401];

    CONST CACHE_FILENAME = 'cache-fastspring.txt';

    public function __construct($config, APIService $APIService, FastSpringModel $fastSpringModel)
    {
        $this->config = $config;
        $this->APIService = $APIService;
        $this->fastSpringModel = $fastSpringModel;

        $this->cacheDirPath = dirname(__DIR__, 2) . '/temp/fast-spring/';
    }

    public function getConfig()
    {
        return $this->config;
    }


    public function updateCache()
    {
        $productRaw = $this->fastSpringModel->getAllProducts();

        if ($productRaw !== false) {

            $productsJson = json_encode($productRaw);

            if (!is_dir($this->cacheDirPath)) {
                // dir doesn't exist, make it
                mkdir($this->cacheDirPath, 0777, true);
            }

            file_put_contents($this->cacheDirPath . self::CACHE_FILENAME, $productsJson);
        } else {
            throw new \Exception("FastSpring - Update cache - Failed - No products" . json_encode($productRaw));
        }
    }

    private function cacheExist()
    {
        return file_exists($this->cacheDirPath . self::CACHE_FILENAME);
    }

    public function getProduct($productPath)
    {
        try {
            if (!$this->cacheExist()) {
                $this->updateCache();
            }

            $productsJson = file_get_contents($this->cacheDirPath . self::CACHE_FILENAME);

            $products = json_decode($productsJson, true);

            if(isset($products[$productPath])){
                return $products[$productPath];
            } else {
                return null;
            }
        } catch (\Exception $e) {
            return null;
        }
    }
}
