<?php

if (!isset($_SERVER['SYMFONY']))
{
  $_SERVER['SYMFONY'] = '/Users/jwage/Sites/symfonysvn/1.4/lib';
}

require_once $_SERVER['SYMFONY'].'/autoload/sfCoreAutoload.class.php';
sfCoreAutoload::register();

class ProjectConfiguration extends sfProjectConfiguration
{
  public $globalConfig = false;
  public $specificConfig = false;

  public function setup()
  {
    $this->enablePlugins('sfDoctrine2Plugin');
    $this->setPluginPath('sfDoctrine2Plugin', realpath(__DIR__.'/../../../..'));
  }

  public function initializeDoctrine($fixtures = false)
  {
    chdir(sfConfig::get('sf_root_dir'));
    $task = new sfDoctrineBuildTask($this->dispatcher, new sfFormatter());
    $task->setConfiguration($this);

    $options = array('db', 'no-confirmation');
    if ($fixtures)
    {
      $options[] = 'and-load';
    }
    $task->run(array(), $options);
  }

  public function configureDoctrineConnection(\Doctrine\ORM\Configuration $config)
  {
    $this->globalConfig = true;
  }

  public function configureDoctrineConnectionDoctrine1(\Doctrine\ORM\Configuration $config)
  {
    $this->specificConfig = true;
  }
}