<?php

/**
 * @file
 * Contains Drupal\tmgmt\EntityController\JobStorageController.
 */

namespace Drupal\tmgmt\EntityController;

use Drupal\Core\Entity\DatabaseStorageController;
use Drupal\Core\Entity\EntityInterface;

/**
 * Controller class for the job entity.
 *
 * @ingroup tmgmt_job
 */
class JobStorageController extends DatabaseStorageController {

  /**
   * Overrides Drupal\Core\Entity\DatabaseStorageController::preSave().
   */
  public function preSave(EntityInterface $entity) {
    $entity->changed = REQUEST_TIME;
  }

  /**
   * Overrides Drupal\Core\Entity\DatabaseStorageController::postDelete().
   */
  public function postDelete($entities) {
    // Since we are deleting one or multiple jobs here we also need to delete
    // the attached job items and messages.
    $tjiids = \Drupal::entityQuery('tmgmt_job_item')
      ->condition('tjid', array_keys($entities))
      ->execute();
    if (!empty($tjiids)) {
      entity_delete_multiple('tmgmt_job_item', $tjiids);
    }
    /*
    $mids = \Drupal::entityQuery('tmgmt_job_message')
      ->condition('tjid', array_keys($entities))
      ->execute();
    if (!empty($mids)) {
      entity_delete_multiple('tmgmt_job_message', $mids);
    }
    }*/

    $trids = \Drupal::entityQuery('tmgmt_remote')
      ->condition('tjid', array_keys($entities))
      ->execute();
    if (!empty($trids)) {
      entity_delete_multiple('tmgmt_remote', $trids);
    }
  }

}