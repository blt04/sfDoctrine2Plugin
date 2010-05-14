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
 * Run DQL query task
 *
 * @package    symfony
 * @subpackage doctrine
 * @author     Jonathan H. Wage <jonwage@gmail.com>
 * @version    SVN: $Id: sfDoctrineRunDqlTask.class.php 15865 2009-02-28 03:34:26Z Jonathan.Wage $
 */
class sfDoctrineRunDqlTask extends sfDoctrineBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', true),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('dql', null, sfCommandOption::PARAMETER_REQUIRED, 'The SQL query to execute'),
      new sfCommandOption('file', null, sfCommandOption::PARAMETER_REQUIRED, 'The path to a SQL file to execute'),
      new sfCommandOption('depth', null, sfCommandOption::PARAMETER_REQUIRED, 'The depth to allow the data to output to'),
      new sfCommandOption('limit', null, sfCommandOption::PARAMETER_REQUIRED, 'Limit the returned results'),
      new sfCommandOption('hydrate', null, sfCommandOption::PARAMETER_OPTIONAL, 'The hydration mode (object/array/scalar)', "object"),
    ));

    $this->aliases = array();
    $this->namespace = 'doctrine';
    $this->name = 'run-dql';
    $this->briefDescription = 'Execute a DQL query';

    $this->detailedDescription = <<<EOF
The [doctrine:run-dql|INFO] task executes a DQL:

  [./symfony doctrine:run-dql --dql="SELECT u FROM User u"|INFO]

EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $args = array();
    if (isset($options['dql']))
    {
      $args[] = '--dql='.$options['dql'];
    }

    if (isset($options['depth']))
    {
      $args[] = '--depth='.$options['depth'];
    }

    if (isset($options["limit"]))
    {
      $args[] = '--limit='.$options["limit"];
    }

    if (isset($options["hydrate"]))
    {
      $args[] = '--hydrate='.$options["hydrate"];
    }

    $this->callDoctrineCli('orm:run-dql', $args);
  }
}