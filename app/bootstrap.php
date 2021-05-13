<?php

$paths = __DIR__ . DIRECTORY_SEPARATOR . 'path.php';
require_once $paths;

require_once(__DIR__ . '/../vendor/PHPMailer-master/PHPMailerAutoload.php');

require __DIR__ . '/../vendor/autoload.php';

$configurator = new Nette\Configurator;

//$configurator->setDebugMode('23.75.345.200'); // enable for your remote IP
$configurator->enableDebugger(__DIR__ . '/../log');

$configurator->setTempDirectory(__DIR__ . '/../temp');

$configurator->createRobotLoader()
	->addDirectory(__DIR__)
	->register();

$configurator->addConfig(__DIR__ . '/config/config.neon');
$configurator->addConfig(__DIR__ . '/config/config.local.neon');

$container = $configurator->createContainer();

$panel = new Dibi\Bridges\Tracy\Panel;
$panel->register($container->getByType(\Dibi\Connection::class));

return $container;
