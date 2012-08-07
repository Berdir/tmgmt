<?php

/**
 * @file
 * Contains Drupal\tmgmt\EntityController\TranslatorStorageController.
 */

namespace Drupal\tmgmt\EntityController;

use Drupal\Core\Config\Entity\ConfigStorageController;
use Drupal\Core\Entity\EntityInterface;

/**
 * Controller class for the translator entity.
 *
 * @ingroup tmgmt_translator
 */
class TranslatorStorageController extends ConfigStorageController {

  /**
   * Overrides Drupal\entity\DatabaseStorageController::buildQuery().
   *
   * @todo: Convert.
   */
  protected function buildQuery($ids, $revision_id = FALSE) {
    $result = parent::buildQuery($ids, $revision_id);
    /*if ($plugins = tmgmt_translator_plugin_info()) {
      $query->condition('plugin', array_keys($plugins));
    }
    else {
      // Don't return any translators if no plugin exists.
      $query->where('1 = 0');
    }
    // Sort by the weight of the translator.
    $query->orderBy('weight');*/
    return $result;
  }

  /**
   * Overrides Drupal\Core\Config\Entity\ConfigStorageController::delete().
   */
  public function delete(array $entities) {
    // We are never going to have many entities here, so we can risk a loop.
    foreach ($entities as $key => $name) {
      if (tmgmt_translator_busy($key)) {
        // The translator can't be deleted because it is currently busy. Remove
        // it from the ids so it wont get deleted in the parent implementation.
        unset($entities[$key]);
      }
      else {
        // Clear the language cache for the deleted translators.
        cache('tmgmt')->delete('languages:' . $key);
      }
    }
    parent::delete($entities);
  }

  /**
   * Overrides Drupal\entity\DatabaseStorageController::postSave().
   */
  public function postSave(EntityInterface $entity, $update) {
    // Clear the languages cache.
    cache('tmgmt')->delete('languages:' . $entity->name);
  }
}
