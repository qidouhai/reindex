<?php

/**
 * @file Pundit.php
 * @brief This file contains the Pundit class.
 * @details
 * @author Filippo F. Fadda
 */


namespace PitPress\Model\Badge\Reply;


use PitPress\Model\Badge\Badge;
use PitPress\Enum\Metal;


/**
 * @brief Left 10 comments with score of 5 or more.
 * @details Awarded once.
 */
class Pundit extends Badge {


  /**
   * @copydoc Badge::getName()
   */
  public function getName() {
    return "Dotto";
  }


  /**
   * @copydoc Badge::getBrief()
   */
  public function getBrief() {
    return <<<'DESC'
Hai lasciato 10 commenti con almeno 5 punti ciascuno. Assegnato una sola volta.
DESC;
  }


  /**
   * @copydoc Badge::getMetal()
   */
  public function getMetal() {
    return Metal::SILVER;
  }


  /**
   * @copydoc Badge::getMessages()
   */
  public function getMessages() {
    return ['vote'];
  }


  /**
   * @copydoc Badge::update()
   * @todo Implements the `update()` method.
   */
  public function update() {

  }


  /**
   * @copydoc Badge::exist()
   */
  public function exist() {

  }


  /**
   * @copydoc Badge::deserve()
   */
  public function deserve() {

  }


  /**
   * @copydoc Badge::award()
   */
  public function award() {

  }


  /**
   * @copydoc Badge::withdrawn()
   */
  public function withdrawn() {

  }

} 