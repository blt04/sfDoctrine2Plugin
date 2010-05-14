<?php

/*
 * This file is part of the symfony package.
 * (c) Fabien Potencier <fabien.potencier@symfony-project.com>
 * (c) Jonathan H. Wage <jonwage@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/**
 * Base class for all symfony Doctrine tasks.
 *
 * @package    symfony
 * @subpackage doctrine
 * @author     Jonathan H. Wage <jonwage@gmail.com>
 * @author     Russell Flynn <russ@eatmymonkeydust.com>
 * @version    SVN: $Id: sfDoctrineBaseTask.class.php 15865 2009-02-28 03:34:26Z Jonathan.Wage $
 */
abstract class sfDoctrineBaseTask extends sfBaseTask
{
  static protected $databaseManagers = array();

  protected function prepareDoctrineCliArguments(array $arguments, array $keys = array())
  {
    $args = array();
    if ($keys)
    {
      foreach ($keys as $key)
      {
        if (isset($arguments[$key]) && $value = $arguments[$key])
        {
          $args[] = '--'.$key.'='.implode(',', (array) $value);
        }
      }
    } else {
      foreach ($arguments as $key => $value)
      {
        if ($value !== null)
        {
          $args[] = '--'.$key.'='.implode(',', (array) $value);
        }
      }
    }
    return $args;
  }

  protected function getCli()
  {
    $helperSet = new \Symfony\Components\Console\Helper\HelperSet(array(
        'em' => new \Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper($this->getEntityManager()),
        'db' => new \Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper($this->getEntityManager()->getConnection()),
    ));

    $cli = new \Symfony\Components\Console\Application('Doctrine Command Line Interface', Doctrine\Common\Version::VERSION);
    $cli->setCatchExceptions(false);
    $cli->setAutoExit(false);
    $cli->setHelperSet($helperSet);
    $cli->addCommands(array(
      // DBAL Commands
      new \Doctrine\DBAL\Tools\Console\Command\RunSqlCommand(),
      new \Doctrine\DBAL\Tools\Console\Command\ImportCommand(),

      // ORM Commands
      new \Doctrine\ORM\Tools\Console\Command\ClearCache\MetadataCommand(),
      new \Doctrine\ORM\Tools\Console\Command\ClearCache\ResultCommand(),
      new \Doctrine\ORM\Tools\Console\Command\ClearCache\QueryCommand(),
      new \Doctrine\ORM\Tools\Console\Command\SchemaTool\CreateCommand(),
      new \Doctrine\ORM\Tools\Console\Command\SchemaTool\UpdateCommand(),
      new \Doctrine\ORM\Tools\Console\Command\SchemaTool\DropCommand(),
      new \Doctrine\ORM\Tools\Console\Command\EnsureProductionSettingsCommand(),
      new \Doctrine\ORM\Tools\Console\Command\ConvertDoctrine1SchemaCommand(),
      new \Doctrine\ORM\Tools\Console\Command\GenerateRepositoriesCommand(),
      new \Doctrine\ORM\Tools\Console\Command\GenerateEntitiesCommand(),
      new \Doctrine\ORM\Tools\Console\Command\GenerateProxiesCommand(),
      new \Doctrine\ORM\Tools\Console\Command\ConvertMappingCommand(),
      new \Doctrine\ORM\Tools\Console\Command\RunDqlCommand(),
    ));

    return $cli;
  }

  protected function callDoctrineCli($task, $arguments = array())
  {
    $this->initDBM();

    $args = array($task);
    $args = array_merge($args, $arguments);

    $input = new \Symfony\Components\Console\Input\StringInput(join(" ", $args));

    $output = new sfDoctrineCliPrinter();
    $output->setFormatter($this->formatter);
    $output->setDispatcher($this->dispatcher);

    $cli = $this->getCli();
    return $cli->run($input, $output);
  }

  protected function initDBM()
  {
    $hash = spl_object_hash($this->configuration);

    if (!isset(self::$databaseManagers[$hash]))
    {
      self::$databaseManagers[$hash] = new sfDatabaseManager($this->configuration);
    }

    return self::$databaseManagers[$hash];
  }

  protected function getEntityManager($name = null)
  {
    $databaseManager = $this->initDBM();

    $names = $databaseManager->getNames();

    if ($name !== null)
    {
      if (!in_array($name, $names))
      {
        throw new sfException(
          sprintf('Could not get the entity manager for '.
                  'the database connection named: "%s"', $name)
        );
      }
      $database = $databaseManager->getDatabase($name);
    } else {
      $database = $databaseManager->getDatabase(end($names));
    }

    return $database->getEntityManager();
  }
}
