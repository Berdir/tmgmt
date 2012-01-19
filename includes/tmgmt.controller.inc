<?php

/**
 * @file
 * Contains the controller classes.
 */

/**
 * Controller class for the tmgmt_job entity.
 *
 * @ingroup job
 */
class TMGMTJobController extends EntityAPIController {

  public function save($entity, DatabaseTransaction $transaction = NULL) {

    if (empty($entity->tjid)) {
      $entity->created = REQUEST_TIME;
    }

    $entity->changed = REQUEST_TIME;

    if (!isset($entity->state)) {
      $entity->state = TMGMT_JOB_STATE_NEW;
    }

    return parent::save($entity, $transaction);
  }
}
