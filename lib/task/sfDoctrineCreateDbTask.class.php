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
 * Create database task
 *
 * @package    symfony
 * @subpackage doctrine
 * @author     Jonathan H. Wage <jonwage@gmail.com>
 * @version    SVN: $Id: sfDoctrineCreateDbTask.class.php 15865 2009-02-28 03:34:26Z Jonathan.Wage $
 */
class sfDoctrineCreateDbTask extends sfDoctrineBaseTask
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

    $this->aliases = array('doctrine:build-db');
    $this->namespace = 'doctrine';
    $this->name = 'create-db';
    $this->briefDescription = 'Create the databases for your configured connections';

    $this->detailedDescription = <<<EOF
The [doctrine:create-db|INFO] task creates the databases for your configured connections:

  [./symfony doctrine:create-db|INFO]

EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $databaseManager = $this->initDBM();
    foreach ($databaseManager->getNames() as $name)
    {
      $database = $databaseManager->getDatabase($name);
      $connection = $database->getEntityManager()->getConnection();
      $params = $connection->getParams();
      $name = isset($params['path']) ? $params['path']:$params['dbname'];

      unset($params['dbname']);

      $tmpConnection = \Doctrine\DBAL\DriverManager::getConnection($params);

      try {
        $tmpConnection->getSchemaManager()->createDatabase($name);
        $this->logSection('doctrine', sprintf('Created database for connection named: "%s"', $name));
      } catch (Exception $e) {
        $this->logSection('doctrine', sprintf('Could not create database for connection named: "%s". Error: '.$e->getMessage(), $name));
        $tmpConnection->close();
        return 1;
      }
  
      $tmpConnection->close();
    }
  }
}
