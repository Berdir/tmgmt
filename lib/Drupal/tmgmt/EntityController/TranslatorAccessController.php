<?php

/**
 * @file
 * Contains \Drupal\tmgmt\EntityController\TranslatorAccessController
 */

namespace Drupal\tmgmt\EntityController;

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
    if (!$entity->getController()) {
      return FALSE;
    }
    return user_access('administer tmgmt', $account);
  }

}
