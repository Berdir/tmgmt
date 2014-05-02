<?php

/**
 * @file
 * Contains Drupal\tmgmt\Entity\Controller\JobStorage.
 */

namespace Drupal\tmgmt\Entity\Controller;

use Drupal\Core\Entity\EntityDatabaseStorage;

/**
 * Controller class for the job entity.
 *
 * @ingroup tmgmt_job
 */
class JobStorage extends EntityDatabaseStorage {

  /**
   * {@inheritdoc}
   */
  public function attachLoad(&$queried_entities, $revision_id = FALSE) {
    parent::attachLoad($queried_entities, $revision_id);
    foreach ($queried_entities as $queried_entity) {
      $queried_entity->settings = unserialize($queried_entity->settings);
    }
  }

}
