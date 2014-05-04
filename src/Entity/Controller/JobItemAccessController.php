<?php

/**
 * @file
 * Contains \Drupal\tmgmt\Entity\Controller\JobItemAccessController.
 */

namespace Drupal\tmgmt\Entity\Controller;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Access controller for the tmgmt entity.
 *
 * @see \Drupal\tmgmt\Plugin\Core\Entity\Job.
 */
class JobItemAccessController extends JobAccessController {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, $langcode, AccountInterface $account) {
    if ($entity->getJob()) {
      return $entity->getJob()->access($operation, $account);
    }
  }

}
