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
 * sfDoctrineRoute represents a route that is bound to a Doctrine class.
 *
 * A Doctrine route can represent a single Doctrine object or a list of objects.
 *
 * @package    symfony
 * @subpackage doctrine
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Jonathan H. Wage <jonwage@gmail.com>
 * @version    SVN: $Id: sfDoctrineRoute.class.php 11475 2008-09-12 11:07:23Z fabien $
 */
class sfDoctrineRoute extends sfObjectRoute
{
  protected
    $query = null;

  public function setListQuery(Doctrine_Query $query)
  {
    if (!$this->isBound())
    {
      throw new LogicException('The route is not bound.');
    }

    $this->query = $query;
  }

	public function getMetadataFor($modelName)
	{
		$databaseManager = sfContext::getInstance()->getDatabaseManager();
    $names = $databaseManager->getNames();
    foreach ($names as $name)
    {
      $em = $databaseManager->getDatabase($name)->getEntityManager();
      $cmf = $em->getMetadataFactory();
      if ($cmf->hasMetadataFor($modelName))
      {
        return $cmf->getMetadataFor($modelName);
      }
    }
    return false;
	}

	protected function getQueryForParameters($parameters)
	{
		$modelName = $this->options['model'];
		$databaseManager = sfContext::getInstance()->getDatabaseManager();
    $names = $databaseManager->getNames();
    foreach ($names as $name)
    {
      $em = $databaseManager->getDatabase($name)->getEntityManager();
      $cmf = $em->getMetadataFactory();
      if ($cmf->hasMetadataFor($modelName))
      {
				$this->repository = $em->getRepository($modelName);
				$this->em = $em;
				$this->cmf = $em->getMetadataFactory();
				$this->metadata = $this->cmf->getMetadataFor($modelName);
       	break;
      }
    }

    $variables = array();
    $values = array();
    foreach($this->getRealVariables() as $variable)
    {
      if ($this->metadata->hasField($variable))
      {
        $variables[] = $variable;
        $values[$variable] = $parameters[$variable];
      }
    }

    if (!isset($this->options['method']))
    {
      if (null === $this->query)
      {
        $qb = $this->em->createQueryBuilder()
					->select('a')
					->from($modelName, 'a');
        foreach ($values as $fieldName => $value)
        {
          $qb->andWhere('a.'. $fieldName . ' = :'.$fieldName);
					$qb->setParameter($fieldName, $value);
        }
      }
      else
      {
        $qb = $this->query;
      }
      if (isset($this->options['method_for_query']))
      {
        $method = $this->options['method_for_query'];
        $qb = $this->repository->$method($qb);
      }
    }
    else
    {
      $method = $this->options['method'];
      $qb = $this->repository->$method($this->filterParameters($parameters));
    }

    return $qb;
	}

  protected function getObjectForParameters($parameters)
  {
		$qb = $this->getQueryForParameters($parameters);
		$query = $qb->getQuery();

    return $query->getSingleResult();
  }

  protected function getObjectsForParameters($parameters)
  {
		$qb = $this->getQueryForParameters($parameters);
		$query = $qb->getQuery();

    return $query->execute();
  }

  protected function doConvertObjectToArray($object)
  {
    if (isset($this->options['convert']) || method_exists($object, 'toParams'))
    {
      return parent::doConvertObjectToArray($object);
    }

    $parameters = array();

    foreach ($this->getRealVariables() as $variable)
    {
			if ($value = $object->$variable)
			{
      	$parameters[$variable] = $value;
    	}
		}
    return $parameters;
  }
}