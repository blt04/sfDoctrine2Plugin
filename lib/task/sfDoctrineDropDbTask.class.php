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
 * Drop database task
 *
 * @package    symfony
 * @subpackage doctrine
 * @author     Jonathan H. Wage <jonwage@gmail.com>
 * @version    SVN: $Id: sfDoctrineDropDbTask.class.php 15865 2009-02-28 03:34:26Z Jonathan.Wage $
 */
class sfDoctrineDropDbTask extends sfDoctrineBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', true),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('no-confirmation', null, sfCommandOption::PARAMETER_NONE, 'Whether to force dropping of the database')
    ));

    $this->aliases = array();
    $this->namespace = 'doctrine';
    $this->name = 'drop-db';
    $this->briefDescription = 'Drop the databases for your configured connections';

    $this->detailedDescription = <<<EOF
The [doctrine:drop-db|INFO] task drops the databases for your configured connections:

  [./symfony doctrine:drop-db|INFO]

EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $databaseManager = $this->initDBM();
    $names = $databaseManager->getNames();

    if (
      !$options['no-confirmation']
      &&
      !$this->askConfirmation(array('This command will remove all data in your database connections named: '.implode(', ', $names), 'Are you sure you want to proceed? (y/N)'), 'QUESTION_LARGE', false)
    )
    {
      $this->logSection('doctrine', 'task aborted');

      return 1;
    }

    foreach ($names as $name)
    {
      $database = $databaseManager->getDatabase($name);
      $connection = $database->getEntityManager()->getConnection();
      $params = $connection->getParams();
      $name = isset($params['path']) ? $params['path']:$params['dbname'];

      try {
        $connection->getSchemaManager()->dropDatabase($name);
        $this->logSection('doctrine', sprintf('Dropped database for connection named: "%s"', $name));
      } catch (Exception $e) {
        $this->logSection('doctrine', sprintf('Could not drop database for connection named: "%s". Error: '.$e->getMessage(), $name));
        return 1;
      }
    }
  }
}
