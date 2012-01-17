<?php

/**
 * @file
 * Contains the controller classes.
 */

/**
 * Controller class for the tmgmt_translator entity.
 */
class TMGMTTranslatorController extends EntityAPIControllerExportable {

}

/**
 * Controller class for the tmgmt_job entity.
 */
class TMGMTJobController extends EntityAPIController {

  public function save($entity, DatabaseTransaction $transaction = NULL) {

    if (empty($entity->tjid)) {
      $entity->created = REQUEST_TIME;
    }

    $entity->changed = REQUEST_TIME;

    if (!isset($entity->state)) {
      $entity->state = TMGMTJob::STATE_NEW;
    }

    return parent::save($entity, $transaction);
  }

}

/**
 * Controller class for the tmgmt_map entity.
 */
class TMGMTMapController extends EntityAPIController {

}
