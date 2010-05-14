<?php

if (!isset($_SERVER['SYMFONY']))
{
  throw new RuntimeException('Could not find symfony core libraries.');
}

if (!isset($app))
{
  $app = 'frontend';
}

require_once $_SERVER['SYMFONY'].'/autoload/sfCoreAutoload.class.php';
sfCoreAutoload::register();

require_once(dirname(__FILE__).'/cleanup.php');

require_once dirname(__FILE__).'/../fixtures/project/config/ProjectConfiguration.class.php';
$configuration = ProjectConfiguration::getApplicationConfiguration($app, 'test', isset($debug) ? $debug : true);
$context = sfContext::createInstance($configuration);

$configuration->initializeDoctrine((isset($fixtures) && $fixtures ? true:false));
$databaseManager = $context->getDatabaseManager();
$names = $databaseManager->getNames();
$em = $databaseManager->getDatabase(end($names))->getEntityManager();
