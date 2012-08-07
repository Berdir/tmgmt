<?php

/**
 * @file
 * Contains Drupal\tmgmt\Entity\Controller\JobStorageController.
 */

namespace Drupal\tmgmt\Entity\Controller;

use Drupal\Core\Entity\DatabaseStorageController;
use Drupal\Core\Entity\EntityInterface;

/**
 * Controller class for the job entity.
 *
 * @ingroup tmgmt_job
 */
class JobStorageController extends DatabaseStorageController {

  /**
   * Overrides Drupal\entity\DatabaseStorageConroller::attachLoad().
   */
  public function attachLoad(&$queried_entities, $revision_id = FALSE) {
    parent::attachLoad($queried_entities, $revision_id);
    foreach ($queried_entities as $queried_entity) {
      $queried_entity->settings = unserialize($queried_entity->settings);
    }
  }

}
