<?php

/**
 * @file
 * Contains Drupal\tmgmt\Entity\Controller\JobItemStorageController.
 */

namespace Drupal\tmgmt\Entity\Controller;

use Drupal\Core\Entity\DatabaseStorageController;
use Drupal\Core\Entity\EntityInterface;

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

}
