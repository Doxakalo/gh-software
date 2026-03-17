<?php

namespace App\Service;

use App\Service\Config\TranslatorConfig;

class Translator
{
    public $translatorConfig;

    public function __construct(TranslatorConfig $translatorConfig)
    {
        $this->translatorConfig = $translatorConfig;
    }

    public function getDefaultLang(){
        return $this->translatorConfig->getConfig()['defaultlang'];
    }

    public function getAvailableLanguages(){
        return $this->translatorConfig->getConfig()['available_languages'];
    }

    public function getPageTranslations($presenterName, $actionName)
    {
        $config = $this->translatorConfig->getConfig();
        if (isset($config['translations'][strtolower($presenterName)][strtolower($actionName)])){
            $availableLanguages = $config['translations'][strtolower($presenterName)][strtolower($actionName)];
        }
        else {
            $availableLanguages = false;
        }
        return $availableLanguages;
    }
}
