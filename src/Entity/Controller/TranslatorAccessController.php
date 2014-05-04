<?php

/**
 * @file
 * Contains \Drupal\tmgmt\Entity\Controller\TranslatorAccessController
 */

namespace Drupal\tmgmt\Entity\Controller;

use Drupal\Core\Entity\EntityAccessController;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;

/**
 * Access controller for the translator entity.
 *
 * @see \Drupal\tmgmt\Plugin\Core\Entity\Translator.
 *
 * @ingroup tmgmt_translator
 */
class TranslatorAccessController extends EntityAccessController {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, $langcode, AccountInterface $account) {
    if (!$entity->getController() && $operation != 'delete') {
      return FALSE;
    }
    return $account->hasPermission('administer tmgmt');
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return $account->hasPermission('administer tmgmt');
  }


}
