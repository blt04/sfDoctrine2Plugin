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
 * sfValidatorDoctrineChoice validates that the value is one of the rows of a table.
 *
 * @package    symfony
 * @subpackage doctrine
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Jonathan H. Wage <jonwage@gmail.com>
 * @version    SVN: $Id: sfValidatorDoctrineChoice.class.php 8804 2008-05-06 12:11:10Z fabien $
 */
class sfValidatorDoctrineChoice extends sfValidatorBase
{
	protected $em;

  public function __construct(\Doctrine\ORM\EntityManager $em, $options = array(), $messages = array())
  {
		$this->em = $em;
    parent::__construct($options, $messages);
  }

  /**
   * Configures the current validator.
   *
   * Available options:
   *
   *  * model:      The model class (required)
   *  * query:      A query to use when retrieving objects
   *  * column:     The column name (null by default which means we use the primary key)
   *                must be in field name format
   *  * multiple:   true if the select tag must allow multiple selections
   *  * min:        The minimum number of values that need to be selected (this option is only active if multiple is true)
   *  * max:        The maximum number of values that need to be selected (this option is only active if multiple is true)
   *
   * @see sfValidatorBase
   */
  protected function configure($options = array(), $messages = array())
  {
    $this->addRequiredOption('model');
    $this->addOption('query', null);
    $this->addOption('column', null);
    $this->addOption('multiple', false);
    $this->addOption('min');
    $this->addOption('max');

    $this->addMessage('min', 'At least %min% values must be selected (%count% values selected).');
    $this->addMessage('max', 'At most %max% values must be selected (%count% values selected).');
  }

  /**
   * @see sfValidatorBase
   */
  protected function doClean($value)
  {
    if ($this->getOption('multiple'))
    {
      if (!is_array($value))
      {
        $value = array($value);
      }

      if (isset($value[0]) && !$value[0])
      {
        unset($value[0]);
      }

      $count = count($value);

      if ($this->hasOption('min') && $count < $this->getOption('min'))
      {
        throw new sfValidatorError($this, 'min', array('count' => $count, 'min' => $this->getOption('min')));
      }

      if ($this->hasOption('max') && $count > $this->getOption('max'))
      {
        throw new sfValidatorError($this, 'max', array('count' => $count, 'max' => $this->getOption('max')));
      }

      if (!$qb = $this->getOption('query'))
      {
        $qb = $this->em->createQueryBuilder()->select('a')->from($this->getOption('model'), 'a');
      }
      $qb->andWhere($qb->expr()->in('a.'.$this->getColumn(), $value));
			$query = $qb->getQuery();
			$results = $query->execute();
      if (count($results) != count($value))
      {
        throw new sfValidatorError($this, 'invalid', array('value' => $value));
      }
    }
    else
    {
      if (!$qb = $this->getOption('query'))
      {
        $qb = $this->em->createQueryBuilder()->select('a')->from($this->getOption('model'), 'a');
      }
      $qb->andWhere('a.'.$this->getColumn()." = '".$value."'");
			$query = $qb->getQuery();
			$results = $query->execute();
      if (!count($results))
      {
        throw new sfValidatorError($this, 'invalid', array('value' => $value));
      }
    }

    return $value;
  }

  /**
   * Returns the column to use for comparison.
   *
   * The primary key is used by default.
   *
   * @return string The column name
   */
  protected function getColumn()
  {
    if ($this->getOption('column'))
    {
      return $this->getOption('column');
    }

    return 'id';
  }
}