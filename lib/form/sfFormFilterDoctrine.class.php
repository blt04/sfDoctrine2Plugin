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
 * sfFormFilterDoctrine is the base class for filter forms based on Doctrine objects.
 *
 * Available options:
 *
 *  * query:        The query object to use
 *  * repository_method: A method on the table class that will either filter the passed query object or create a new one
 *
 * @package    symfony
 * @subpackage form
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Jonathan H. Wage <jonwage@gmail.com>
 * @version    SVN: $Id: sfFormFilterDoctrine.class.php 11690 2008-09-20 19:50:03Z fabien $
 */
abstract class sfFormFilterDoctrine extends sfFormFilter
{
	protected $em;

  public function __construct($em, $defaults = array(), $options = array(), $CSRFSecret = null)
  {
		$this->em = $em;
		parent::__construct($defaults, $options, $CSRFSecret);
	}

  /**
   * Returns the current model name.
   *
   * @return string The model class name
   */
  abstract public function getModelName();

  /**
   * Returns the fields and their filter type.
   *
   * @return array An array of fields with their filter type
   */
  abstract public function getFields();

  /**
   * Get the name of the table method used to retrieve the query object for the filter
   *
   * @return string
   */
  public function getRepositoryMethod()
  {
    return $this->getOption('repository_method');
  }

  /**
   * Set the name of the table method used to retrieve the query object for the filter
   *
   * The specified method will be passed the query object before any changes
   * are made based on incoming parameters.
   *
   * @param string $tableMethod
   */
  public function setRepositoryMethod($tableMethod)
  {
    $this->setOption('repository_method', $tableMethod);
  }

  /**
   * Sets the query object to use.
   * 
   * @param Doctrine_Query $query
   */
  public function setQuery($query)
  {
    $this->setOption('query', $query);
  }

  /**
   * Returns a Doctrine Query based on the current values form the form.
   *
   * @return Query A Doctrine Query object
   */
  public function getQuery()
  {
    if (!$this->isValid())
    {
      throw $this->getErrorSchema();
    }

    return $this->buildQuery($this->getValues());
  }

  /**
   * Processes cleaned up values with user defined methods.
   *
   * To process a value before it is used by the buildQuery() method,
   * you need to define an convertXXXValue() method where XXX is the PHP name
   * of the column.
   *
   * The method must return the processed value or false to remove the value
   * from the array of cleaned up values.
   *
   * @param  array An array of cleaned up values to process
   *
   * @return array An array of cleaned up values processed by the user defined methods
   */
  public function processValues($values)
  {
    // see if the user has overridden some column setter
    $originalValues = $values;
    foreach ($originalValues as $field => $value)
    {
      if (method_exists($this, $method = sprintf('convert%sValue', self::camelize($field))))
      {
        if (false === $ret = $this->$method($value))
        {
          unset($values[$field]);
        }
        else
        {
          $values[$field] = $ret;
        }
      }
    }

    return $values;
  }

  /**
   * Builds a Doctrine Query based on the passed values.
   *
   * @param  array    An array of parameters to build the Query object
   *
   * @return Query A Doctrine Query object
   */
  public function buildQuery(array $values)
  {
		$this->metadata = $this->getMetadata();
    return $this->doBuildQuery($this->processValues($values));
  }

  /**
   * Builds a Doctrine query with processed values.
   *
   * Overload this method instead of {@link buildQuery()} to avoid running
   * {@link processValues()} multiple times.
   *
   * @param  array $values
   *
   * @return Doctrine_Query
   */
  protected function doBuildQuery(array $values)
  {
    $query = isset($this->options['query']) ? clone $this->options['query'] : $this->em->createQueryBuilder()->select('a')->from($this->metadata->name, 'a');

    if ($method = $this->getRepositoryMethod())
    {
      $query = $this->em->getRepository($this->getModelName())->$method($query);
    }

    foreach ($this->getFields() as $field => $type)
    {
      if (!isset($values[$field]) || null === $values[$field] || '' === $values[$field])
      {
        continue;
      }

      if ($this->metadata->hasField($field))
      {
        $method = sprintf('add%sColumnQuery', self::camelize($this->getFieldName($field)));
      }
      else
      {
        // not a "real" column
        if (!method_exists($this, $method = sprintf('add%sColumnQuery', self::camelize($field))))
        {
          throw new LogicException(sprintf('You must define a "%s" method to be able to filter with the "%s" field.', $method, $field));
        }  
      }

      if (method_exists($this, $method))
      {
        $this->$method($query, $field, $values[$field]);
      }
      else
      {
        if (!method_exists($this, $method = sprintf('add%sQuery', $type)))
        {
          throw new LogicException(sprintf('Unable to filter for the "%s" type.', $type));
        }

        $this->$method($query, $field, $values[$field]);
      }
    }
    return $query;
  }

  protected function addForeignKeyQuery($query, $field, $value)
  {
    $fieldName = $this->getFieldName($field);

    if (is_array($value))
    {
      $query->orWhere($query->expr()->in(sprintf('%s.%s', $query->getRootAlias(), $fieldName), $value));
    }
    else
    {
      $query->andWhere(sprintf('%s.%s = :%s', $query->getRootAlias(), $fieldName, $fieldName));
			$query->setParameter($fieldName, $value);
    }
  }

  protected function addEnumQuery($query, $field, $value)
  {
    $fieldName = $this->getFieldName($field);

    $query->andWhere(sprintf('%s.%s = :'.$fieldName, $query->getRootAlias(), $fieldName));
		$query->setParameter($fieldName, $value);
  }

  protected function addTextQuery($query, $field, $values)
  {
    $fieldName = $this->getFieldName($field);

    if (is_array($values) && isset($values['is_empty']) && $values['is_empty'])
    {
      $query->andWhere(sprintf('%s.%s IS NULL', $query->getRootAlias(), $fieldName));
    }
    else if (is_array($values) && isset($values['text']) && '' != $values['text'])
    {
      $query->andWhere(sprintf('%s.%s LIKE :'.$fieldName, $query->getRootAlias(), $fieldName));
			$query->setParameter($fieldName, '%'.$values['text'].'%');
    }
  }

  protected function addNumberQuery($query, $field, $values)
  {
    $fieldName = $this->getFieldName($field);

    if (is_array($values) && isset($values['is_empty']) && $values['is_empty'])
    {
      $query->andWhere(sprintf('%s.%s IS NULL', $query->getRootAlias(), $fieldName));
    }
    else if (is_array($values) && isset($values['text']) && '' != $values['text'])
    {
      $query->andWhere(sprintf('%s.%s = :'.$fieldName, $query->getRootAlias(), $fieldName));
			$query->setParameter($fieldName, $values['text']);
    }
  }

  protected function addBooleanQuery($query, $field, $value)
  {
    $fieldName = $this->getFieldName($field);
    $query->andWhere(sprintf('%s.%s = :'.$fieldName, $query->getRootAlias(), $fieldName));
		$query->setParameter($fieldName, $value);
  }

  protected function addDateQuery($query, $field, $values)
  {
    $fieldName = $this->getFieldName($field);

    if (isset($values['is_empty']) && $values['is_empty'])
    {
      $query->andWhere(sprintf('%s.%s IS NULL', $query->getRootAlias(), $fieldName));
    }
    else
    {
      if (null !== $values['from'] && null !== $values['to'])
      {
        $query->andWhere(sprintf('%s.%s >= :from', $query->getRootAlias(), $fieldName));
        $query->setParameter('from', $values['from']);
				$query->andWhere(sprintf('%s.%s <= :to', $query->getRootAlias(), $fieldName));
				$query->setParameter('to', $values['to']);
      }
      else if (null !== $values['from'])
      {
        $query->andWhere(sprintf('%s.%s >= :from', $query->getRootAlias(), $fieldName));
				$query->setParameter('from', $values['from']);
      }
      else if (null !== $values['to'])
      {
        $query->andWhere(sprintf('%s.%s <= :to', $query->getRootAlias(), $fieldName));
				$query->setParameter('to', $values['to']);
      }
    }
  }

  /**
   * Used in generated forms when models use inheritance.
   */
  protected function setupInheritance()
  {
  }

  protected function getColName($field)
  {
    return $this->metadata->getColumnName($field);
  }

  protected function getFieldName($colName)
  {
    return $this->metadata->getFieldName($colName);
  }

  protected function camelize($text)
  {
    return sfToolkit::pregtr($text, array('#/(.?)#e' => "'::'.strtoupper('\\1')", '/(^|_|-)+(.)/e' => "strtoupper('\\2')"));
  }

  protected function getMetadata()
  {
		$model = $this->getModelName();
		$databaseManager = sfContext::getInstance()->getDatabaseManager();
	  $names = $databaseManager->getNames();
    foreach ($names as $name)
    {
      $em = $databaseManager->getDatabase($name)->getEntityManager();
      $cmf = $em->getMetadataFactory();
      if ($cmf->hasMetadataFor($model))
      {
				$this->em = $em;
        $this->metadata = $cmf->getMetadataFor($model);
				return $this->metadata;
      }
    }
    return false;
  }
}