<?php

/**
 * @file
 * Contains \Drupal\tmgmt_local\Entity\Controller\LocalTaskItemAccessController.
 */

namespace Drupal\tmgmt_local\Entity\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Access control handler for the job entity.
 *
 * @see \Drupal\tmgmt\Plugin\Core\Entity\Job.
 */
class LocalTaskItemAccessController extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    if ($account->hasPermission('administer tmgmt')) {
      // Administrators can do everything.
      return AccessResult::allowed()->cachePerPermissions();
    }

    switch ($operation) {
      case 'view':
      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'provide translation services')->orIf(AccessResult::allowedIfHasPermission($account, 'administer translation tasks'));

      case 'delete':
        // Only administrators can delete jobs.
        return AccessResult::allowedIfHasPermission($account, 'administer translation tasks');

      // Custom operations.
      case 'submit':
        return AccessResult::allowedIfHasPermission($account, 'administer translation tasks');

      case 'accept':
        return AccessResult::allowedIfHasPermission($account, 'administer translation tasks');

      case 'abort':
      case 'resubmit':
        return AccessResult::allowedIfHasPermission($account, 'administer translation tasks');
    }
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'administer translation tasks')->orIf(AccessResult::allowedIfHasPermission($account, 'administer tmgmt'));
  }

}
