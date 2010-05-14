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
 * sfDoctrine pager class.
 *
 * @package    sfDoctrinePlugin
 * @subpackage pager
 * @author     Jonathan H. Wage <jonwage@gmail.com>
 * @version    SVN: $Id: sfDoctrinePager.class.php 21910 2009-09-11 12:33:49Z Kris.Wallsmith $
 */
class sfDoctrinePager extends sfPager implements Serializable
{
  protected
		$em                     = null,
    $query             			= null,
    $repositoryMethodName   = null,
    $repositoryMethodCalled = false;

	public function __construct($em, $class, $maxPerPage = 10)
	{
		$this->em = $em;
		parent::__construct($class, $maxPerPage);
	}

  /**
   * Get the name of the table method used to retrieve the query object for the pager
   *
   * @return string $repositoryMethodName
   */
  public function getRepositoryMethod()
  {
    return $this->repositoryMethodName;
  }

  /**
   * Set the name of the table method used to retrieve the query object for the pager
   *
   * @param string $repositoryMethodName 
   * @return void
   */
  public function setRepositoryMethod($repositoryMethodName)
  {
    $this->repositoryMethodName = $repositoryMethodName;
  }

  /**
   * Serialize the pager object
   *
   * @return string $serialized
   */
  public function serialize()
  {
    $vars = get_object_vars($this);
    unset($vars['query']);
    return serialize($vars);
  }

  /**
   * Unserialize a pager object
   *
   * @param string $serialized 
   */
  public function unserialize($serialized)
  {
    $array = unserialize($serialized);

    foreach ($array as $name => $values)
    {
      $this->$name = $values;
    }
  }

  /**
   * Returns a query for counting the total results.
   * 
   * @return Doctrine_Query
   */
  public function getCountQuery()
  {
    $query = clone $this->getQuery();
    $query->setFirstResult(null);
    $query->setMaxResults(null);
		$query->select('COUNT(DISTINCT a.id)');

    return $query;
  }

	public function count()
	{
		return $this->getCountQuery()->getQuery()->getSingleScalarResult();
	}

  /**
   * @see sfPager
   */
  public function init()
  {
    $count = $this->count();

    $this->setNbResults($count);

    $query = $this->getQuery();
    $query->setFirstResult(null);
    $query->setMaxResults(null);

    if (0 == $this->getPage() || 0 == $this->getMaxPerPage() || 0 == $this->getNbResults())
    {
      $query->setMaxResults(null);
    }
    else
    {
      $offset = ($this->getPage() - 1) * $this->getMaxPerPage();

      $this->setLastPage(ceil($this->getNbResults() / $this->getMaxPerPage()));

      $query->setFirstResult($offset ? $offset:null);
      $query->setMaxResults($this->getMaxPerPage());
    }
  }

  /**
   * Get the query for the pager.
   *
   * @return Doctrine_Query
   */
  public function getQuery()
  {
    if (!$this->repositoryMethodCalled && $this->repositoryMethodName)
    {
      $method = $this->repositoryMethodName;
      $this->query = $this->em->getRepository($this->getClass())->$method($this->query);
      $this->repositoryMethodCalled = true;
    }
    else if (!$this->query)
    {
      $this->query = $this->em->createQueryBuilder()
				->select('a')
				->from($this->getClass(), 'a');
    }

    return $this->query;
  }

  /**
   * Set query object for the pager
   *
   * @param Doctrine_Query $query
   */
  public function setQuery($query)
  {
    $this->query = $query;
  }

  /**
   * Retrieve the object for a certain offset
   *
   * @param integer $offset
   *
   * @return Doctrine_Record
   */
  protected function retrieveObject($offset)
  {
    $queryForRetrieve = clone $this->getQuery();
    $queryForRetrieve->setFirstResult($offset - 1);
    $queryForRetrieve->setMaxResults(1);

    return $queryForRetrieve->getSingleResult();
  }

  /**
   * Get all the results for the pager instance
   *
   * @param integer $hydrationMode \Doctrine\ORM\Query::HYDRATE_* constants
   *
   * @return Doctrine_Collection|array
   */
  public function getResults($hydrationMode = \Doctrine\ORM\Query::HYDRATE_OBJECT)
  {
    return $this->getQuery()->getQuery()->execute(array(), $hydrationMode);
  }
}