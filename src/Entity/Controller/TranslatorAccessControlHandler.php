<?php

/**
 * @file
 * Contains \Drupal\tmgmt\Entity\Controller\TranslatorAccessControlHandler
 */

namespace Drupal\tmgmt\Entity\Controller;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Access control handler for the translator entity.
 *
 * @see \Drupal\tmgmt\Plugin\Core\Entity\Translator.
 *
 * @ingroup tmgmt_translator
 */
class TranslatorAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, $langcode, AccountInterface $account) {
    if (!$entity->getPlugin() && $operation != 'delete') {
      return AccessResult::forbidden();
    }
    return AccessResult::allowedIfHasPermission($account, 'administer tmgmt');
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'administer tmgmt');
  }


}
