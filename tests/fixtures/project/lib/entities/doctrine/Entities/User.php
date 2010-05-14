<?php

namespace Entities;

use sfDoctrineActiveEntity;

class User extends sfDoctrineActiveEntity
{
  protected $id;
  protected $isActive;
  protected $username;
  protected $password;
  protected $version;
  protected $profile;
  protected $groups;
  protected $myFriends;
  protected $friendsWithMe;

  public function setPassword($password)
  {
    $this->password = md5($password);
  }

	public function __toString()
	{
		return $this->get('username');
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
   * Set isActive
   */
  public function setIsActive($value)
  {
    $this->isActive = $value;
  }

  /**
   * Get isActive
   */
  public function getIsActive()
  {
    return $this->isActive;
  }

  /**
   * Set username
   */
  public function setUsername($value)
  {
    $this->username = $value;
  }

  /**
   * Get username
   */
  public function getUsername()
  {
    return $this->username;
  }

  /**
   * Get password
   */
  public function getPassword()
  {
    return $this->password;
  }

  /**
   * Set version
   */
  public function setVersion($value)
  {
    $this->version = $value;
  }

  /**
   * Get version
   */
  public function getVersion()
  {
    return $this->version;
  }

  /**
   * Set profile
   */
  public function setProfile($value)
  {
    $this->profile = $value;
  }

  /**
   * Get profile
   */
  public function getProfile()
  {
    return $this->profile;
  }

  /**
   * Add groups
   */
  public function addGroups($value)
  {
    $this->groups[] = $value;
  }

  /**
   * Get groups
   */
  public function getGroups()
  {
    return $this->groups;
  }

  /**
   * Add friendsWithMe
   */
  public function addFriendsWithMe($value)
  {
    $this->friendsWithMe[] = $value;
  }

  /**
   * Get friendsWithMe
   */
  public function getFriendsWithMe()
  {
    return $this->friendsWithMe;
  }

  /**
   * Add myFriends
   */
  public function addMyFriends($value)
  {
    $this->myFriends[] = $value;
  }

  /**
   * Get myFriends
   */
  public function getMyFriends()
  {
    return $this->myFriends;
  }
}