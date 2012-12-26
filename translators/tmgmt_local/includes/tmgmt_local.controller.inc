<?php

/**
 * @file
 * UI controller classes.
 */

/**
 * Controller class for the local task entity.
 *
 * @ingroup tmgmt_local_task
 */
class TMGMTLocalTaskController extends EntityAPIController {

  /**
   * Overrides EntityAPIController::save().
   */
  public function save($entity, DatabaseTransaction $transaction = NULL) {
    $entity->changed = REQUEST_TIME;
    return parent::save($entity, $transaction);
  }

  /**
   * Overrides EntityAPIController::delete().
   */
  public function delete($ids, $transaction = NULL) {
    parent::delete($ids, $transaction);
    // @todo - we should do what here with assigned job items and related job?
  }

}
