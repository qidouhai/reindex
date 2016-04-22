<?php

/**
 * @file MoveRevisionToTrashPermission.php
 * @brief This file contains the MoveRevisionToTrashPermission class.
 * @details
 * @author Filippo F. Fadda
 */


namespace ReIndex\Security\Role\ModeratorRole;


use ReIndex\Security\Role\ReviewerRole\ApproveRevisionPermission as Superclass;


/**
 * @copydoc ReviewerRole::ApproveRevisionPermission
 */
class MoveRevisionToTrashPermission extends Superclass {


  public function check() {
    if ($this->user->isModerator() && ($this->isCreated() or $this->isDraft() or $this->isSubmittedForPeerReview()))
      return TRUE;
    else
      return FALSE;
  }

}