<?php

/**
 * @file
 * Contains \Drupal\tmgmt_local\Entity\Controller\LocalTaskAccessControlHandler.
 */

namespace Drupal\tmgmt_local\Entity\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Access control handler for the task entity.
 *
 * @see \Drupal\tmgmt_local\Entity\LocalTask.
 */
class LocalTaskAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    if ($operation == 'delete') {
      return AccessResult::forbidden();
    }
    if ($account->hasPermission('administer tmgmt') || $account->hasPermission('administer translation tasks')) {
      // Administrators can do everything.
      return AccessResult::allowed()->cachePerPermissions();
    }

    switch ($operation) {
      case 'view':
      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'provide translation services');

      // Custom operations.
      case 'unassign':
        return AccessResult::allowedIf(!empty($entity->tuid) && $entity->tuid == $account->id() && $account->hasPermission('provide translation services'));
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
