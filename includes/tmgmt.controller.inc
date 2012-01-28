<?php

/**
 * @file
 * Contains the controller classes.
 */

/**
 * Controller class for the job entity.
 *
 * @ingroup tmgmt_job
 */
class TMGMTJobController extends EntityAPIController {

  /**
   * Overrides EntityAPIController::save().
   */
  public function save($entity, DatabaseTransaction $transaction = NULL) {
    $entity->changed = REQUEST_TIME;
    return parent::save($entity, $transaction);
  }

}

/**
 * Controller class for the job item entity.
 *
 * @ingroup tmgmt_job
 */
class TMGMTJobItemController extends EntityAPIController {

  /**
   * Overrides EntityAPIController::save().
   */
  public function save($entity, DatabaseTransaction $transaction = NULL) {
    $entity->changed = REQUEST_TIME;
    return parent::save($entity, $transaction);
  }

}

/**
 * Controller class for the job entity.
 *
 * @ingroup tmgmt_translator
 */
class TMGMTTranslatorController extends EntityAPIControllerExportable {

  /**
   * Overrides EntityAPIControllerExportable::buildQuery().
   */
  protected function buildQuery($ids, $conditions = array(), $revision_id = FALSE) {
    $query = parent::buildQuery($ids, $conditions, $revision_id);
    if ($plugins = tmgmt_translator_plugin_info()) {
      $query->condition('plugin', array_keys($plugins));
    }
    else {
      // Don't return any translators if no plugin exists.
      $query->where('1 = 0');
    }
    // Sort by the weight of the translator.
    $query->orderBy('weight');
    return $query;
  }

  /**
   * Overrides EntityAPIControllerExportable::delete().
   */
  public function delete($ids, DatabaseTransaction $transaction = NULL) {
    // We are never going to have many entities here, so we can risk a loop.
    foreach ($ids as $key => $name) {
      if (tmgmt_translator_busy($name)) {
        // The translator can't be deleted because it is currently busy. Remove
        // it from the ids so it wont get deleted in the parent implementation.
        unset($ids[$key]);
      }
    }
    parent::delete($ids, $transaction);
  }

  /**
  * Overrides EntityAPIControllerExportable::save().
  */
  public function save($entity, DatabaseTransaction $transaction = NULL) {
    $return = parent::save($entity, $transaction);
    // Clear the languages cache.
    cache_clear_all('tmgmt_translator_languages', 'cache');
    return $return;
  }
}
