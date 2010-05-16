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
 * Load data fixtures task
 *
 * @package    symfony
 * @subpackage doctrine
 * @author     Jonathan H. Wage <jonwage@gmail.com>
 * @version    SVN: $Id: sfDoctrineLoadDataFixturesTask.class.php 15865 2009-02-28 03:34:26Z Jonathan.Wage $
 */
class sfDoctrineLoadDataFixturesTask extends sfDoctrineBaseTask
{
  /**
   * @see sfTask
   */
  protected function configure()
  {
    $this->addArguments(array(
      new sfCommandArgument('dir_or_file', sfCommandArgument::OPTIONAL | sfCommandArgument::IS_ARRAY, 'Directory or file to load'),
    ));

    $this->addOptions(array(
      new sfCommandOption('application', null, sfCommandOption::PARAMETER_OPTIONAL, 'The application name', true),
      new sfCommandOption('env', null, sfCommandOption::PARAMETER_REQUIRED, 'The environment', 'dev'),
      new sfCommandOption('append', null, sfCommandOption::PARAMETER_NONE, 'Don\'t delete current data in the database'),
    ));

    $this->aliases = array('doctrine-data-load', 'doctrine:data-load');
    $this->namespace = 'doctrine';
    $this->name = 'load-data-fixtures';
    $this->briefDescription = 'Load data fixtures from a directory or file';

    $this->detailedDescription = <<<EOF
The [doctrine:load-data-fixtures|INFO] task loads data fixtures from a directory or file:

  [./symfony doctrine:load-data-fixtures|INFO]

EOF;
  }

  /**
   * @see sfTask
   */
  protected function execute($arguments = array(), $options = array())
  {
    $databaseManager = $this->initDBM();

    if (!$options['append'])
    {
      $names = $databaseManager->getNames();

      $entityManagers = array();
      foreach ($names as $name)
      {
        $metadatas = $databaseManager
          ->getDatabase($name)
          ->getEntityManager()
          ->getMetadataFactory()
          ->getAllMetadata();

        foreach ($metadatas as $metadata)
        {
          if (!$metadata->isMappedSuperclass)
          {
            $entityManagers[$name][] = $metadata;
          }
        }
      }
      foreach ($entityManagers as $name => $classes)
      {
        $em = $databaseManager->getDatabase($name)->getEntityManager();
        $cmf = $em->getMetadataFactory();
        $classes = $this->getCommitOrder($em, $classes);
        for ($i = count($classes) - 1; $i >= 0; --$i)
        {
          $class = $classes[$i];
          if ($cmf->hasMetadataFor($class->name))
          {
            $this->logSection('doctrine', sprintf('Truncating "%s"', $class->name));
            try {
              $em->createQuery(sprintf('DELETE FROM %s t',$class->name))->execute();
            } catch (Exception $e) {}
          }
        }
      }
    }

    $paths = $arguments['dir_or_file'] ? $arguments['dir_or_file']:array(sfConfig::get('sf_data_dir').'/fixtures');

    $files = array();
    foreach ($paths as $path)
    {
      if (is_dir($path))
      {
        $found = sfFinder::type('file')
          ->name('*.php')
          ->in($path);
      } else {
        $found = array($path);
      }
      $files = array_merge($files, $found);
    }

    $files = array_unique($files);

    $em = $this->getEntityManager();
    foreach ($files as $file)
    {
      $this->logSection('doctrine', sprintf('Loading data fixtures from: "%s"', $file));

      $before = array_keys(get_defined_vars());
      include($file);
      $after = array_keys(get_defined_vars());
      $new = array_diff($after, $before);
      $entities = array_values($new);
      unset($entities[array_search('before', $entities)]);
      foreach ($entities as $entity) {
        $em->persist($$entity);
      }
      $em->flush();
    }
  }

  protected function getCommitOrder(\Doctrine\ORM\EntityManager $em, array $classes)
  {
    $calc = new Doctrine\ORM\Internal\CommitOrderCalculator;

    foreach ($classes as $class)
    {
      $calc->addClass($class);
      
      foreach ($class->associationMappings as $assoc)
      {
        if ($assoc->isOwningSide) {
          $targetClass = $em->getClassMetadata($assoc->targetEntityName);
          
          if ( ! $calc->hasClass($targetClass->name)) {
              $calc->addClass($targetClass);
          }
          
          // add dependency ($targetClass before $class)
          $calc->addDependency($targetClass, $class);
        }
      }
    }

    return $calc->getCommitOrder();
  }
}
