<?php

//! @file router.php
//! @brief Setting up the router.
//! @details
//! @author Filippo F. Fadda


use Phalcon\Mvc\Router;

use PitPress\Route;


// Creates a router instance and return it.
$di->setShared('router',
  function () {
    $router = new Router();

    //$router->setDefaultController("index");
    //$router->setDefaultAction("recents");

    $router->add('/',
      [
        'controller' => 'index',
        'action' => 'index'
      ]
    );

    $router->mount(new Route\IndexGroup());
    $router->mount(new Route\AuthGroup());
    $router->mount(new Route\BlogGroup());
    $router->mount(new Route\QuestionsGroup());
    $router->mount(new Route\LinksGroup());
    $router->mount(new Route\TagsGroup());
    $router->mount(new Route\BadgesGroup());
    $router->mount(new Route\UsersGroup());

    return $router;
  }
);