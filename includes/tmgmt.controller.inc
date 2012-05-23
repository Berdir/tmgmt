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

  /**
   * Overrides EntityAPIController::delete().
   */
  public function delete($ids, $transaction = NULL) {
    parent::delete($ids, $transaction);
    // Since we are deleting one or multiple jobs here we also need to delete
    // the attached job items and messages.
    $query = new EntityFieldQuery();
    $result = $query->entityCondition('entity_type', 'tmgmt_job_item')
      ->propertyCondition('tjid', $ids)
      ->execute();
    if (!empty($result['tmgmt_job_item'])) {
      $controller = entity_get_controller('tmgmt_job_item');
      // We need to directly query the entity controller so we can pass on
      // the transaction object.
      $controller->delete(array_keys($result['tmgmt_job_item']), $transaction);
    }
    $query = new EntityFieldQuery();
    $result = $query->entityCondition('entity_type', 'tmgmt_message')
      ->propertyCondition('tjid', $ids)
      ->execute();
    if (!empty($result['tmgmt_message'])) {
      $controller = entity_get_controller('tmgmt_message');
      // We need to directly query the entity controller so we can pass on
      // the transaction object.
      $controller->delete(array_keys($result['tmgmt_message']), $transaction);
    }
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
   *
   * @todo Eliminate the need to flatten and unflatten the JobItem data.
   */
  public function save($entity, DatabaseTransaction $transaction = NULL) {
    $entity->changed = REQUEST_TIME;
    $count_pending = 0;
    $count_translated = 0;
    $count_approved = 0;

    $items = tmgmt_flatten_data($entity->data);
    // Consider everything approved when the job item is accepted.
    if ($entity->isAccepted()) {
      $count_approved = count(array_filter($items, '_tmgmt_filter_data'));
    }
    // Count the data item states.
    else {
      foreach ($items as &$data_item) {
        if (_tmgmt_filter_data($data_item)) {
        // Set default states if no state is set.
        if (!isset($data_item['#status'])) {
          // Translation is present.
          if (!empty($data_item['#translation'])) {
            $data_item['#status'] = TMGMT_DATA_ITEM_STATE_TRANSLATED;
          }
          // No translation present.
          else {
            $data_item['#status'] = TMGMT_DATA_ITEM_STATE_PENDING;
          }
          }
          switch ($data_item['#status']) {
            case TMGMT_DATA_ITEM_STATE_APPROVED:
              $count_approved++;
              break;
            case TMGMT_DATA_ITEM_STATE_TRANSLATED:
              $count_translated++;
              break;
            default:
              $count_pending++;
              break;
          }
        }
      }
      // Save changed data item states.
      $entity->data = tmgmt_unflatten_data($items);
    }
    // Set counts on entity.
    $entity->count_pending = $count_pending;
    $entity->count_translated = $count_translated;
    $entity->count_approved = $count_approved;
    return parent::save($entity, $transaction);
  }

  /**
   * Overrides EntityAPIController::delete().
   */
  public function delete($ids, $transaction = NULL) {
    parent::delete($ids, $transaction);
    // Since we are deleting one or multiple job items here we also need to
    // delete the attached messages.
    $query = new EntityFieldQuery();
    $result = $query->entityCondition('entity_type', 'tmgmt_message')
      ->propertyCondition('tjiid', $ids)
      ->execute();
    if (!empty($result['tmgmt_message'])) {
      $controller = entity_get_controller('tmgmt_message');
      // We need to directly query the entity controller so we can pass on
      // the transaction object.
      $controller->delete(array_keys($result['tmgmt_message']), $transaction);
    }
  }

  /**
   * Overrides EntityAPIController::invoke().
   */
  public function invoke($hook, $entity) {
    // We need to check whether the state of the job is affected by this
    // deletion.
    if ($hook == 'delete' && $job = $entity->getJob()) {
      // We only care for active jobs.
      if ($job->isActive() && tmgmt_job_check_finished($job->tjid)) {
        // Mark the job as finished.
        $job->finished();
      }
    }
    parent::invoke($hook, $entity);
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
    $cids = array();
    // We are never going to have many entities here, so we can risk a loop.
    foreach ($ids as $key => $name) {
      if (tmgmt_translator_busy($key)) {
        // The translator can't be deleted because it is currently busy. Remove
        // it from the ids so it wont get deleted in the parent implementation.
        unset($ids[$key]);
      }
      else {
        $cids[$key] = 'language:' . $key;
      }
    }
    // Clear the language cache for the deleted translators.
    cache_clear_all($cids, 'cache_tmgmt');
    parent::delete($ids, $transaction);
  }

  /**
  * Overrides EntityAPIControllerExportable::save().
  */
  public function save($entity, DatabaseTransaction $transaction = NULL) {
    $return = parent::save($entity, $transaction);
    // Clear the languages cache.
    cache_clear_all('language:' . $entity->name, 'cache_tmgmt');
    return $return;
  }
}
