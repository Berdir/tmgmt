<?php

/**
 * @file
 * Contains \Drupal\tmgmt\Entity\Controller\JobItemAccessControlHandler.
 */

namespace Drupal\tmgmt\Entity\Controller;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Access control handler for the job item entity.
 *
 * @see \Drupal\tmgmt\Plugin\Core\Entity\Job.
 */
class JobItemAccessControlHandler extends JobAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, $langcode, AccountInterface $account) {
    if ($entity->getJob()) {
      return $entity->getJob()->access($operation, $account, TRUE);
    }
  }

}
