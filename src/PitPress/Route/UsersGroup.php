<?php

//! @file UsersGroup.php
//! @brief Group of Users routes.
//! @details
//! @author Filippo F. Fadda


namespace PitPress\Route;


use Phalcon\Mvc\Router\Group;


//! @brief Group of users' routes.
//! @nosubgrouping
class UsersGroup extends Group {


  public function initialize() {
    // Sets the default controller for the following routes.
    $this->setPaths(
      [
        'namespace' => 'PitPress\Controller',
        'controller' => 'users'
      ]);

    // All the routes start with /utenti.
    $this->setPrefix('/utenti');

    $this->addGet('/', ['action' => 'reputation']);
    $this->addGet('/reputazione/{period}', ['action' => 'reputation']);
    $this->addGet('/nuovi/', ['action' => 'newest']);
    $this->addGet('/votanti/{period}', ['action' => 'voters']);
    $this->addGet('/editori/{period}', ['action' => 'editors']);
    $this->addGet('/reporters/{period}', ['action' => 'reporters']);
    $this->addGet('/bloggers/{period}', ['action' => 'bloggers']);
    $this->addGet('/moderatori/', ['action' => 'moderators']);
    $this->addGet('/privilegi/', ['action' => 'privileges']);
  }

}