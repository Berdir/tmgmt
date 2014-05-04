<?php

/**
 * @file
 * Contains \Drupal\tmgmt\Entity\Controller\MessageStorage.
 */

namespace Drupal\tmgmt\Entity\Controller;

use Drupal\Core\Entity\EntityDatabaseStorage;

/**
 * Controller class for the job item entity.
 *
 * @ingroup tmgmt_job
 */
class MessageStorage extends EntityDatabaseStorage {

  /**
   * {@inheritdoc}
   */
  public function attachLoad(&$queried_entities, $revision_id = FALSE) {
    parent::attachLoad($queried_entities, $revision_id);
    foreach ($queried_entities as $queried_entity) {
      $queried_entity->variables = unserialize($queried_entity->variables);
    }
  }

}
