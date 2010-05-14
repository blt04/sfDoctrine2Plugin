<?php

namespace Entities;

use sfDoctrineActiveEntity;

class Profile extends sfDoctrineActiveEntity
{
  protected $id;
  protected $firstName;
  protected $lastName;
  protected $userId;
  protected $user;

  public function getName()
  {
    return $this->firstName.' '.$this->lastName;
  }
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
   * Set firstName
   */
  public function setFirstName($value)
  {
    $this->firstName = $value;
  }

  /**
   * Get firstName
   */
  public function getFirstName()
  {
    return $this->firstName;
  }

  /**
   * Set lastName
   */
  public function setLastName($value)
  {
    $this->lastName = $value;
  }

  /**
   * Get lastName
   */
  public function getLastName()
  {
    return $this->lastName;
  }

  /**
   * Set userId
   */
  public function setUserId($value)
  {
    $this->userId = $value;
  }

  /**
   * Get userId
   */
  public function getUserId()
  {
    return $this->userId;
  }

  /**
   * Set user
   */
  public function setUser($value)
  {
    $this->user = $value;
  }

  /**
   * Get user
   */
  public function getUser()
  {
    return $this->user;
  }
  /**
   * @Column(name="test", type="string", length=255)
   */
  private $test;

  /**
   * Set test
   */
  public function setTest($value)
  {
    $this->test = $value;
  }

  /**
   * Get test
   */
  public function getTest()
  {
    return $this->test;
  }
}