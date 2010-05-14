<?php

if (!isset($_SERVER['SYMFONY']))
{
  throw new RuntimeException('Could not find symfony core libraries.');
}

require_once $_SERVER['SYMFONY'].'/autoload/sfCoreAutoload.class.php';
sfCoreAutoload::register();

require_once(dirname(__FILE__).'/cleanup.php');

$projectPath = dirname(__FILE__).'/../fixtures/project';
require_once($projectPath.'/config/ProjectConfiguration.class.php');

if (!isset($app))
{
  $configuration = new ProjectConfiguration($projectPath);
} else {
  $configuration = ProjectConfiguration::getApplicationConfiguration($app, 'test', isset($debug) ? $debug : true);
  $context = sfContext::createInstance($configuration);
}

if (isset($app))
{
  $configuration->initializeDoctrine((isset($fixtures) && $fixtures ? true:false));
  $databaseManager = $context->getDatabaseManager();
}
else if (isset($database) && $database)
{
  $databaseManager = new sfDatabaseManager($configuration);

  $configuration->initializeDoctrine((isset($fixtures) && $fixtures ? true:false));
}

if (isset($databaseManager))
{
	$names = $databaseManager->getNames();
	$em = $databaseManager->getDatabase(end($names))->getEntityManager();
}

require_once $configuration->getSymfonyLibDir().'/vendor/lime/lime.php';

function autoload_again($class)
{
  $autoload = sfSimpleAutoload::getInstance();
  $autoload->reload();
  return $autoload->autoload($class);
}
spl_autoload_register('autoload_again');  