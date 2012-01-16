<?php

/**
 * @file
 * Contains the controller classes.
 */

/**
 * Controller class for the t9n_mgmt_translator entity.
 */
class TranslationManagementTranslatorController extends EntityAPIControllerExportable {

}

/**
 * Controller class for the t9n_mgmt_job entity.
 */
class TranslationManagementJobController extends EntityAPIController {

  public function save($entity, DatabaseTransaction $transaction = NULL) {

    if (empty($entity->tjid)) {
      $entity->created = REQUEST_TIME;
    }

    $entity->changed = REQUEST_TIME;

    if (!isset($entity->state)) {
      $entity->state = TranslationManagementJob::STATE_NEW;
    }

    return parent::save($entity, $transaction);
  }

}

/**
 * Controller class for the t9n_mgmt_map entity.
 */
class TranslationManagementMapController extends EntityAPIController {

}