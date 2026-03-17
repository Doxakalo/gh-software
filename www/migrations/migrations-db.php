#!/usr/bin/env php
<?php

require_once __DIR__ . '/../vendor/autoload.php';

use Doctrine\DBAL\DriverManager;
use Doctrine\Migrations\Configuration\Migration\PhpFile;
use Doctrine\Migrations\Configuration\Connection\ExistingConnection;
use Doctrine\Migrations\DependencyFactory;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\ArgvInput;
use Doctrine\Migrations\Tools\Console\Command\DiffCommand;
use Doctrine\Migrations\Tools\Console\Command\DumpSchemaCommand;
use Doctrine\Migrations\Tools\Console\Command\ExecuteCommand;
use Doctrine\Migrations\Tools\Console\Command\GenerateCommand;
use Doctrine\Migrations\Tools\Console\Command\LatestCommand;
use Doctrine\Migrations\Tools\Console\Command\MigrateCommand;
use Doctrine\Migrations\Tools\Console\Command\RollupCommand;
use Doctrine\Migrations\Tools\Console\Command\StatusCommand;
use Doctrine\Migrations\Tools\Console\Command\VersionCommand;
use Nette\Neon\Neon;

$input = new ArgvInput();
$env = $input->getFirstArgument();
if (!in_array($env, ['sample', 'structure', 'core'], true)) {
    fwrite(STDERR, "Použití: php migrations-db.php [sample|structure|core] <command> [options]\n");
    exit(1);
}

$_SERVER['argv'] = array_slice($_SERVER['argv'], 1);
$_SERVER['argc']--;

$configPath = __DIR__ . '/../app/config/migrations.neon';
if (!file_exists($configPath)) {
    throw new \RuntimeException("Config file not found at $configPath");
}

$configNeon = file_get_contents($configPath);
$config = Neon::decode($configNeon);

$dbConfig = $config['migrations']['default'] ?? null;
if (!$dbConfig) {
    throw new \RuntimeException("Missing 'migrations.default' section in config");
}

$dsn = $dbConfig['dsn'];
$user = $dbConfig['user'];
$password = $dbConfig['password'];

preg_match('/host=([^;]+)/', $dsn, $hostMatch);
preg_match('/port=([^;]+)/', $dsn, $portMatch);
preg_match('/dbname=([^;]+)/', $dsn, $dbMatch);

$host = $hostMatch[1] ?? 'localhost';
$port = isset($portMatch[1]) ? (int)$portMatch[1] : 3306;
$dbname = $dbMatch[1] ?? '';

$connectionParams = [
    'driver'   => 'pdo_mysql',
    'host'     => $host,
    'port'     => $port,
    'dbname'   => $dbname,
    'user'     => $user,
    'password' => $password,
    'charset'  => 'utf8mb4',
];
$conn = DriverManager::getConnection($connectionParams);

$migrationsConfigFile = __DIR__ . "/migrations-{$env}.php";
if (!file_exists($migrationsConfigFile)) {
    throw new \RuntimeException("Migrations config file not found: {$migrationsConfigFile}");
}
$migrationsConfig = new PhpFile($migrationsConfigFile);

$connectionLoader = new ExistingConnection($conn);
$dependencyFactory = DependencyFactory::fromConnection($migrationsConfig, $connectionLoader);

$cli = new Application("Doctrine Migrations ({$env})");

$cli->addCommands([
    new DiffCommand($dependencyFactory),
    new DumpSchemaCommand($dependencyFactory),
    new ExecuteCommand($dependencyFactory),
    new GenerateCommand($dependencyFactory),
    new LatestCommand($dependencyFactory),
    new MigrateCommand($dependencyFactory),
    new RollupCommand($dependencyFactory),
    new StatusCommand($dependencyFactory),
    new VersionCommand($dependencyFactory),
]);

$cli->run();
