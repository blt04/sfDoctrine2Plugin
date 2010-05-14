<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) Jonathan H. Wage <jonwage@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

require_once(dirname(__FILE__).'/sfDoctrineBaseTask.class.php');

/**
 * Create classes for the current model.
 *
 * @package    symfony
 * @subpackage doctrine
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Jonathan H. Wage <jonwage@gmail.com>
 * @version    SVN: $Id: sfDoctrineBuildModelTask.class.php 20867 2009-08-06 21:43:11Z Kris.Wallsmith $
 */
class sfDoctrineBuildModelTask extends sfDoctrineBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', true),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
    ));

    $this->aliases = array('doctrine-build-model');
    $this->namespace = 'doctrine';
    $this->name = 'build-model';
    $this->briefDescription = 'Creates classes for the current model';

    $this->detailedDescription = <<<EOF
The [doctrine:build-model|INFO] task creates model classes from the schema:

  [./symfony doctrine:build-model|INFO]

The task read the schema information in [config/doctrine/*.yml|COMMENT]
from the project and all installed plugins.

The model classes files are created in [lib/model/doctrine|COMMENT].

This task never overrides custom classes in [lib/model/doctrine|COMMENT].
It only replaces files in [lib/model/doctrine/base|COMMENT].
EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $opts = array();

    $plugins = $this->configuration->getPlugins();
    foreach ($this->configuration->getAllPluginPaths() as $plugin => $path)
    {
      if (!in_array($plugin, $plugins))
      {
        continue;
      }
      $path = $path.'/config/doctrine';
      $opts['from'][] = $path;
    }
    
    $opts['from'][] = sfConfig::get('sf_config_dir').'/doctrine';
    $opts['to'] = 'annotation';
    $opts['dest'] = sfConfig::get('sf_lib_dir').'/entities/doctrine';

    $task = new sfDoctrineConvertMappingTask($this->dispatcher, $this->formatter);
    $task->setCommandApplication($this->commandApplication);
    $task->setConfiguration($this->configuration);
    $task->run(array(), $opts);

    $this->reloadAutoload();
  }
}