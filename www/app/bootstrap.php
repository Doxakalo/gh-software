<?php

require __DIR__ . '/../vendor/autoload.php';

$configurator = new Nette\Configurator;

// SET DEBUG MOD - CUSTOM
$pathEnvConfig = __DIR__ . '/env.json';
if (file_exists($pathEnvConfig)) {
    $configEnvJson = file_get_contents($pathEnvConfig);
    $configEnv = json_decode($configEnvJson, true);
    if (isset($configEnv["env-dev"]) && $configEnv["env-dev"] === true) {
        $configurator->setDebugMode(true);
    }
}


// enable for your remote IP
$configurator->enableTracy(__DIR__ . '/../log');

$configurator->setTimeZone('Europe/Prague');
$configurator->setTempDirectory(__DIR__ . '/../temp');

$configurator->createRobotLoader()
    ->addDirectory(__DIR__)
    ->register();

$configurator->addConfig(__DIR__ . '/config/config.neon');
$configurator->addConfig(__DIR__ . '/config/common.neon');
$configurator->addConfig(__DIR__ . '/config/config.local.neon');

// CUSTOM CONFIGS
$configurator->addConfig(__DIR__ . '/config/config.api.local.neon');
$configurator->addConfig(__DIR__ . '/config/config.product.neon');
$configurator->addConfig(__DIR__ . '/config/config.store.neon');
$configurator->addConfig(__DIR__ . '/config/config.translator.neon');

$container = $configurator->createContainer();
return $container;
