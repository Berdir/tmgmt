<?php

/**
 * @file
 * Contains \Drupal\tmgmt\Entity\Controller\MessageStorageController.
 */

namespace Drupal\tmgmt\Entity\Controller;

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
class MessageStorageController extends DatabaseStorageController {

  /**
   * Overrides Drupal\entity\DatabaseStorageConroller::attachLoad().
   */
  public function attachLoad(&$queried_entities, $revision_id = FALSE) {
    parent::attachLoad($queried_entities, $revision_id);
    foreach ($queried_entities as $queried_entity) {
      $queried_entity->variables = unserialize($queried_entity->variables);
    }
  }

}
