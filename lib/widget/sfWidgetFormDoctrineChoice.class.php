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
 * sfWidgetFormDoctrineChoice represents a choice widget for a model.
 *
 * @package    symfony
 * @subpackage doctrine
 * @author     Fabien Potencier <fabien.potencier@symfony-project.com>
 * @author     Jonathan H. Wage <jonwage@gmail.com>
 * @version    SVN: $Id: sfWidgetFormDoctrineChoice.class.php 11540 2008-09-14 15:23:55Z fabien $
 */
class sfWidgetFormDoctrineChoice extends sfWidgetFormChoice
{
  protected $em;

  /**
   * @see sfWidget
   */
  public function __construct(\Doctrine\ORM\EntityManager $em, $options = array(), $attributes = array())
  {
    $this->em = $em;
    $options['choices'] = array();

    parent::__construct($options, $attributes);
  }

  /**
   * Constructor.
   *
   * Available options:
   *
   *  * model:             The model class (required)
   *  * add_empty:         Whether to add a first empty value or not (false by default)
   *                       If the option is not a Boolean, the value will be used as the text value
   *  * method:            The method to use to display object values (__toString by default)
   *  * key_method:        The method to use to display the object keys (getPrimaryKey by default)
   *  * order_by:          An array composed of two fields:
   *                        * The column to order by the results (must be in the PhpName format)
   *                        * asc or desc
   *  * query:             A query to use when retrieving objects
   *  * multiple:          true if the select tag must allow multiple selections
   *  * repository_method: A method to return either a query, collection or single object
   *
   * @see sfWidgetFormSelect
   */
  protected function configure($options = array(), $attributes = array())
  {
    $this->addRequiredOption('model');
    $this->addOption('add_empty', false);
    $this->addOption('method', '__toString');
    $this->addOption('key_method', 'getIdentifier');
    $this->addOption('order_by', null);
    $this->addOption('query', null);
    $this->addOption('multiple', false);
    $this->addOption('repository_method', null);

    parent::configure($options, $attributes);
  }

  /**
   * Returns the choices associated to the model.
   *
   * @return array An array of choices
   */
  public function getChoices()
  {
    $choices = array();
    if (false !== $this->getOption('add_empty'))
    {
      $choices[''] = true === $this->getOption('add_empty') ? '' : $this->getOption('add_empty');
    }

    if (null === $this->getOption('repository_method'))
    {
      $qb = null === $this->getOption('query') ? $this->em->createQueryBuilder()->select('a')->from($this->getOption('model'), 'a') : $this->getOption('query');
      if ($order = $this->getOption('order_by'))
      {
        $qb->addOrderBy($order[0], $order[1]);
      }
      $query = $qb->getQuery();
      $objects = $query->execute();
    }
    else
    {
      $repositoryMethod = $this->getOption('repository_method');
      $results = $this->em->getRepository($this->getOption('model'))->$repositoryMethod();

      if ($results instanceof \Doctrine\ORM\QueryBuilder)
      {
        $qb = $results;
        $query = $qb->getQuery();
        $objects = $query->execute();
      }
      else if ($results instanceof \Doctrine\ORM\Query)
      {
        $objects = $query->execute();
      }
      else if ($results)
      {
        $objects = $results;
      }
      else
      {
        $objects = array();
      }
    }

    $method = $this->getOption('method');
    $keyMethod = $this->getOption('key_method');

    foreach ($objects as $object)
    {
      $choices[$object->$keyMethod()] = $object->$method();
    }

    return $choices;
  }
}