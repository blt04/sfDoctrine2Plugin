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
 * Represents a Doctrine column
 *
 * @package    symfony
 * @subpackage doctrine
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Jonathan H. Wage <jonwage@gmail.com>
 * @version    SVN: $Id: sfDoctrineAdminColumn.class.php 12356 2008-10-23 21:54:50Z Jonathan.Wage $
 */
class sfDoctrineColumn implements ArrayAccess
{
  /**
   * Array mapping Doctrine column types to the native symfony type
   */
  static $doctrineToSymfony = array(
    'boolean'   => 'BOOLEAN',
    'string'    => 'LONGVARCHAR',
    'integer'   => 'INTEGER',
    'date'      => 'DATE',
    'timestamp' => 'TIMESTAMP',
    'time'      => 'TIME',
    'float'     => 'FLOAT',
    'double'    => 'DOUBLE',
    'clob'      => 'CLOB',
    'blob'      => 'BLOB',
    'object'    => 'LONGVARCHAR',
    'array'     => 'LONGVARCHAR',
    'decimal'   => 'DECIMAL',
  );

  /**
   * Store the name of the related class for this column if it is
   * a foreign key
   *
   * @var string
   */
  protected $foreignClassName = null;

  /**
   * Doctrine_Table instance this column belongs to
   *
   * @var Doctrine_Table $table
   */
  protected $metadata = null;

  /**
   * Field name of the column
   *
   * @var string
   */
  protected $name = null;

  /**
   * Definition of the column
   *
   * @var array $definition
   */
  protected $definition = array();

  protected $generator;

  public function __construct($name, array $fieldMapping, $metadata, $generator)
  {
    $this->name = $name;
    $this->fieldMapping = $fieldMapping;
    $this->metadata = $metadata;
    $this->generator = $generator;
  }

  /**
   * Get the name of the column
   *
   * @return string $name
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * Get the alias/field name
   *
   * @return string $fieldName
   */
  public function getFieldName()
  {
    return $this->name;
  }

  /**
   * Get the column name
   *
   * @return string $columName
   */
  public function getColumName()
  {
    return $this->fieldMapping['columnName'];
  }

  /**
   * Get php name. Exists for backwards compatibility with propel orm
   *
   * @return string $fieldName
   */
  public function getPhpName()
  {
    return $this->getFieldName();
  }

  /**
   * Get the Doctrine type of the column
   *
   * @return void
   */
  public function getDoctrineType()
  {
    return isset($this->fieldMapping['type']) ? $this->fieldMapping['type']:null;
  }

  /**
   * Get symfony type of the column
   *
   * @return void
   */
  public function getType()
  {
    $doctrineType = $this->getDoctrineType();

    // we simulate the CHAR/VARCHAR types to generate input_tags
    if ('string' == $doctrineType && null !== $this->getSize() && $this->getSize() <= 255)
    {
      return 'VARCHAR';
    }

    return $doctrineType ? self::$doctrineToSymfony[$doctrineType] : 'VARCHAR';
  }

  /**
   * Get size/length of the column
   *
   * @return void
   */
  public function getSize()
  {
    return $this->fieldMapping['length'];
  }

  public function getLength()
  {
    return $this->getSize();
  }

  /**
   * Check if the column definition has a certain key
   *
   * @param string $key
   * @return bool
   */
  public function hasDefinitionKey($key)
  {
    return isset($this->fieldMapping[$key]) ? true:false;
  }

  /**
   * Get the value of a column definition key
   *
   * @param string $key
   * @return array $definition
   */
  public function getDefinitionKey($key)
  {
    if ($this->hasDefinitionKey($key))
    {
      return $this->fieldMapping[$key];
    } else {
      return false;
    }
  }

  /**
   * Returns true of the column is not null and false if it is null
   *
   * @return boolean
   */
  public function isNotNull()
  {
    if (isset($this->fieldMapping['notnull']))
    {
      return $this->fieldMapping['notnull'];
    }
    if (isset($this->fieldMapping['notblank']))
    {
      return $this->fieldMapping['notblank'];
    }
    return false;
  }

  /**
   * Returns true if the column is a primary key and false if it is not
   *
   * @return void
   */
  public function isPrimaryKey()
  {
    if (isset($this->fieldMapping['id']) && $this->fieldMapping['id'])
    {
      return true;
    }
    return false;
  }

  /**
   * Returns true if this column is a foreign key and false if it is not
   *
   * @return boolean $isForeignKey
   */
  public function isForeignKey()
  {
    if (isset($this->foreignClassName))
    {
      return true;
    }

    if ($this->isPrimaryKey())
    {
      return false;
    }
    foreach ($this->metadata->associationMappings as $associationMapping)
    {
      if ($associationMapping instanceof \Doctrine\ORM\Mapping\OneToOneMapping || $associationMapping instanceof \Doctrine\ORM\Mapping\OneToManyMapping)
      {
        if (isset($associationMapping->joinColumns[0]['name']) && $this->name == $associationMapping->joinColumnFieldNames[$associationMapping->joinColumns[0]['name']])
        {
					$this->foreignClassName = $associationMapping->targetEntityName;
          return true;
        }
      }
    }
    return false;
  }

  /**
   * Get the name of the related class for this column foreign key.
   *
   * @return string $foreignClassName
   */
  public function getForeignClassName()
  {
    if ($this->isForeignKey())
    {
      return $this->foreignClassName;
    } else {
      return false;
    }
  }

  /**
   * If foreign key get the related Doctrine_Table object
   *
   * @return Doctrine_Table $table
   */
  public function getForeignMetadata()
  {
    if ($this->isForeignKey())
    {
      return $this->generator->getMetadataFor($this->foreignClassName);
    } else {
      return false;
    }
  }

  /**
   * Get the Doctrine_Table object this column belongs to
   *
   * @return Doctrine_Table $table
   */
  public function getTable()
  {
    return $this->table;
  }

  public function offsetExists($offset)
  {
    return isset($this->fieldMapping[$offset]);
  }

  public function offsetSet($offset, $value)
  {
    $this->fieldMapping[$offset] = $value;
  }

  public function offsetGet($offset)
  {
    return $this->fieldMapping[$offset];
  }

  public function offsetUnset($offset)
  {
    unset($this->fieldMapping[$offset]);
  }
}