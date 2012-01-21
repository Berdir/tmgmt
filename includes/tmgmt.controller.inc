<?php

/**
 * @file
 * Contains the controller classes.
 */

/**
 * Controller class for the job entity.
 *
 * @ingroup tmgmt_job
 */
class TMGMTJobController extends EntityAPIController {

  /**
   * Overrides EntityAPIController::save().
   */
  public function save($entity, DatabaseTransaction $transaction = NULL) {
    $entity->changed = REQUEST_TIME;
    return parent::save($entity, $transaction);
  }

}
