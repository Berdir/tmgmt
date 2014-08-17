<?php

/**
 * @file
 * Contains \Drupal\tmgmt\Entity\Controller\JobAccessControlHandler.
 */

namespace Drupal\tmgmt\Entity\Controller;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Access control handler for the job entity.
 *
 * @see \Drupal\tmgmt\Plugin\Core\Entity\Job.
 */
class JobAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, $langcode, AccountInterface $account) {
    if ($account->hasPermission('administer tmgmt')) {
      // Administrators can do everything.
      return TRUE;
    }

    switch ($operation) {
      case 'view':
      case 'update':
        return $account->hasPermission('create translation jobs') || $account->hasPermission('submit translation jobs') || $account->hasPermission('accept translation jobs');
        break;

      case 'delete':
        // Only administrators can delete jobs.
        return FALSE;
        break;

      // Custom operations.
      case 'submit':
        return $account->hasPermission('submit translation jobs');
        break;

      case 'accept':
        return $account->hasPermission('accept translation jobs');
        break;

      case 'abort':
      case 'resubmit':
        return $account->hasPermission('submit translation jobs');
        break;
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return $account->hasPermission('administer tmgmt') || $account->hasPermission('create translation jobs');
  }


}
