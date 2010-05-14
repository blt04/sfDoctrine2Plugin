<?php

namespace Entities;

use sfDoctrineActiveEntity;

class Group extends sfDoctrineActiveEntity
{
  protected $id;
  protected $name;
  protected $users;
  /**
   * Set id
   */
  public function setId($value)
  {
    $this->id = $value;
  }

  /**
   * Get id
   */
  public function getId()
  {
    return $this->id;
  }

  /**
   * Set name
   */
  public function setName($value)
  {
    $this->name = $value;
  }

  /**
   * Get name
   */
  public function getName()
  {
    return $this->name;
  }

  /**
   * Add users
   */
  public function addUsers($value)
  {
    $this->users[] = $value;
  }

  /**
   * Get users
   */
  public function getUsers()
  {
    return $this->users;
  }
}