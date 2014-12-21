<?php

//! @file Guardian.php
//! @brief This file contains the Guardian class.
//! @details
//! @author Filippo F. Fadda


//! PitPress security namespace.
namespace PitPress\Security;


use ElephantOnCouch\Extension;;

use PitPress\Factory\UserFactory;



/**
 * @brief This class is responsible to ensure an user has the ability to perform any operation.
 * @nosubgrouping
 */
class Guardian {
  use Extension\TProperty;

  private static $initialized = FALSE;

  protected $user;


  public function __construct($config) {

    if (!self::$initialized) {
      self::$initialized = TRUE;
      $this->user = UserFactory::getFromCookie();
    }

  }


  /**
   * @brief Returns the logged in user.
   * @return IUser
   */
  public function getUser() {
    return $this->user;
  }


  /**
   * @brief Impersonates the given user.
   * @param[in] IUser $user
   */
  public function impersonate(IUser $user) {
    $this->user = $user;
  }

}