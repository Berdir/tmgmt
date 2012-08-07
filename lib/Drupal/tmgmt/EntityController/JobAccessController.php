<?php

/**
 * @file
 * Contains \Drupal\tmgmt\EntityController\JobAccessController
 */

namespace Drupal\tmgmt\EntityController;

use Drupal\Core\Entity\EntityAccessController;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Access controller for the tmgmt entity.
 *
 * @see \Drupal\tmgmt\Plugin\Core\Entity\Job.
 */
class JobAccessController extends EntityAccessController {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, $langcode, AccountInterface $account) {
    if (user_access('administer tmgmt', $account)) {
      // Administrators can do everything.
      return TRUE;
    }

    switch ($operation) {
      case 'view':
      case 'update':
        return user_access('create translation jobs', $account) || user_access('submit translation jobs', $account) || user_access('accept translation jobs', $account);
        break;

      case 'delete':
        // Only administrators can delete jobs.
        return FALSE;
        break;

      // Custom operations.
      case 'submit':
        return user_access('submit translation jobs');
        break;

      case 'accept':
        return user_access('accept translation jobs');
        break;
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return user_access('create translation jobs', $account);
  }


}
