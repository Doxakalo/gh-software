<?php

namespace App\Service;

use App\Model\FastSpringCacheModel;
use App\Service\Config\StoresConfig;
use App\Utils\ArrayUtil;

class Stores
{
    // CONFIG
    public $storesConfig;

    // MODELS & SERVICES
    private $fastSpringCacheModel;

    public function __construct(StoresConfig $storesConfig, FastSpringCacheModel $fastSpringCacheModel)
    {
        $this->fastSpringCacheModel = $fastSpringCacheModel;
        $this->storesConfig = $storesConfig;
    }

    public function getStoreDetail($code)
    {
        return $this->storesConfig->getConfig()[$code];
    }

    public function loadStoreItemsDetail($storeProducts = [])
    {
        $storeProducts["storeStatus"] = true;
        foreach ($storeProducts["sections"] as $key => $section) {
            foreach ($section["items"] as $id => $setup) {
                $item = $this->fastSpringCacheModel->getProduct($id);
                if ($item === null) {
                    $storeProducts["storeStatus"] = false;
                } else {
                    $storeProducts["sections"][$key]["items"][$id] = $item;
                    $storeProducts["sections"][$key]["items"][$id]["setup"] = $setup;
                }
            }
        }


        return $storeProducts;
    }


}
