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
    // Filter by available translator plugins.
    $plugins = tmgmt_translator_plugin_info();
    $query->condition('translator_plugin', array_keys($plugins));
    // Sort by the weight of the translator.
    $query->orderBy('weight');
    return $query;
  }
}
