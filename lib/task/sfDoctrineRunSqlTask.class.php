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
 * Run SQL query task
 *
 * @package    symfony
 * @subpackage doctrine
 * @author     Jonathan H. Wage <jonwage@gmail.com>
 * @version    SVN: $Id: sfDoctrineRunSqlTask.class.php 15865 2009-02-28 03:34:26Z Jonathan.Wage $
 */
class sfDoctrineRunSqlTask extends sfDoctrineBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', true),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('sql', null, sfCommandOption::PARAMETER_REQUIRED, 'The SQL query to execute'),
      new sfCommandOption('file', null, sfCommandOption::PARAMETER_REQUIRED, 'The path to a SQL file to execute'),
      new sfCommandOption('depth', null, sfCommandOption::PARAMETER_REQUIRED, 'The depth to allow the data to output to'),
    ));

    $this->aliases = array();
    $this->namespace = 'doctrine';
    $this->name = 'run-sql';
    $this->briefDescription = 'Execute a SQL query or a file of SQL queries';

    $this->detailedDescription = <<<EOF
The [doctrine:run-sql|INFO] task executes a SQL query or a file of SQL queries:

  [./symfony doctrine:run-sql --sql="SELECT * FROM users"|INFO]

EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $args = array();

    if (isset($options['depth']))
    {
      $args[] = '--depth='.$options['depth'];
    }

    if (isset($options['sql']))
    {
      $args[] = '"' . $options['sql'] . '"';
    }


    $this->callDoctrineCli('dbal:run-sql', $args);
  }
}
