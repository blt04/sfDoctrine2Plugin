<?php

$database = true;
require_once(dirname(__FILE__).'/../bootstrap/unit.php');

$t = new lime_test(5, new lime_output_color());

$names = $databaseManager->getNames();
$t->is($names, array('doctrine1', 'doctrine2'));

$em = $databaseManager->getDatabase('doctrine1')->getEntityManager();
$t->is(get_class($em), 'Doctrine\ORM\EntityManager');
$t->is($em->getConnection()->getParams(), array('driver' => 'pdo_sqlite', 'path' => realpath(__DIR__.'/../../tests/fixtures/project/data/test1.sqlite')));

$t->is($configuration->globalConfig, true);
$t->is($configuration->specificConfig, true);