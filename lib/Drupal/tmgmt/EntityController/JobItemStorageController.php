<?php

/**
 * @file
 * Contains Drupal\tmgmt\EntityController\JobItemStorageController.
 */

namespace Drupal\tmgmt\EntityController;

use Drupal\Core\Entity\DatabaseStorageController;
use Drupal\Core\Entity\EntityInterface;

/**
 * Controller class for the job item entity.
 *
 * @ingroup tmgmt_job
 */

/**
 * Controller class for the job item entity.
 *
 * @ingroup tmgmt_job
 */
class JobItemStorageController extends DatabaseStorageController {

  /**
   * Overrides EntityAPIController::invoke().
   */
  public function invoke($hook, $entity) {
    // We need to check whether the state of the job is affected by this
    // deletion.
    if ($hook == 'delete' && $job = $entity->getJob()) {
      // We only care for active jobs.
      if ($job->isActive() && tmgmt_job_check_finished($job->tjid)) {
        // Mark the job as finished.
        $job->finished();
      }
    }
    parent::invoke($hook, $entity);
  }

  /**
   * Overrides Drupal\entity\DatabaseStorageConroller::attachLoad().
   */
  public function attachLoad(&$queried_entities, $revision_id = FALSE) {
    parent::attachLoad($queried_entities, $revision_id);
    foreach ($queried_entities as $queried_entity) {
      $queried_entity->data = unserialize($queried_entity->data);
    }
  }

}
