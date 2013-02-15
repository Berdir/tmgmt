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
    $query = new EntityFieldQuery();
    $result = $query->entityCondition('entity_type', 'tmgmt_remote')
      ->propertyCondition('tjid', $ids)
      ->execute();
    if (!empty($result['tmgmt_remote'])) {
      $controller = entity_get_controller('tmgmt_remote');
      $controller->delete(array_keys($result['tmgmt_remote']), $transaction);
    }
  }

}

/**
 * Controller class for the remote job mapping entity.
 *
 * @ingroup tmgmt_job
 */
class TMGMTRemoteController extends EntityAPIController {

  public function load($ids = array(), $conditions = array()) {
    $entities = parent::load($ids, $conditions);

    foreach ($entities as &$entity) {
      if (is_string($entity->remote_data)) {
        $entity->remote_data = unserialize($entity->remote_data);
      }
    }

    return $entities;
  }

  /**
   * Loads remote mappings based on local data.
   *
   * @param int $tjid
   *   Translation job id.
   * @param int $tjiid
   *   Translation job item id.
   * @param int $data_item_key
   *   Data item key.
   *
   * @return array
   *   Array of TMGMTRemote entities.
   */
  function loadByLocalData($tjid = NULL, $tjiid = NULL, $data_item_key = NULL) {
    $data_item_key = tmgmt_ensure_keys_string($data_item_key);

    $query = new EntityFieldQuery();
    $query->entityCondition('entity_type', 'tmgmt_remote');

    if (!empty($tjid)) {
      $query->propertyCondition('tjid', $tjid);
    }
    if (!empty($tjiid)) {
      $query->propertyCondition('tjiid', $tjiid);
    }
    if (!empty($data_item_key)) {
      $query->propertyCondition('data_item_key', $data_item_key);
    }

    $result = $query->execute();

    if (isset($result['tmgmt_remote'])) {
      return entity_load('tmgmt_remote', array_keys($result['tmgmt_remote']));
    }

    return array();
  }

  /**
   * Loads remote mapping entities based on remote identifier.
   *
   * @param int $remote_identifier_1
   * @param int $remote_identifier_2
   * @param int $remote_identifier_3
   *
   * @return array
   *   Array of TMGMTRemote entities.
   */
  function loadByRemoteIdentifier($remote_identifier_1 = NULL, $remote_identifier_2 = NULL, $remote_identifier_3 = NULL) {
    $query = new EntityFieldQuery();
    $query->entityCondition('entity_type', 'tmgmt_remote');

    if ($remote_identifier_1 !== NULL) {
      $query->propertyCondition('remote_identifier_1', $remote_identifier_1);
    }
    if ($remote_identifier_2 !== NULL) {
      $query->propertyCondition('remote_identifier_2', $remote_identifier_2);
    }
    if ($remote_identifier_3 !== NULL) {
      $query->propertyCondition('remote_identifier_3', $remote_identifier_3);
    }

    $result = $query->execute();

    if (isset($result['tmgmt_remote'])) {
      return entity_load('tmgmt_remote', array_keys($result['tmgmt_remote']));
    }

    return array();
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

    // Consider everything accepted when the job item is accepted.
    if ($entity->isAccepted()) {
      $entity->count_pending = 0;
      $entity->count_translated = 0;
      $entity->count_reviewed = 0;
      $entity->count_accepted = count(array_filter(tmgmt_flatten_data($entity->data), '_tmgmt_filter_data'));
    }
    // Count the data item states.
    else {
      // Reset counter values.
      $entity->count_pending = 0;
      $entity->count_translated = 0;
      $entity->count_reviewed = 0;
      $entity->count_accepted = 0;
      $entity->word_count = 0;
      $this->count($entity->data, $entity);
    }
    return parent::save($entity, $transaction);
  }

  /**
   * Parse all data items recursively and sums up the counters for
   * accepted, translated and pending items.
   *
   * @param $item
   *   The current data item.
   * @param $entity
   *   The job item the count should be calculated.
   */
  protected function count(&$item, $entity) {
    if (!empty($item['#text'])) {
      if (_tmgmt_filter_data($item)) {

        // Count words of the data item.
        $entity->word_count += tmgmt_word_count($item['#text']);

        // Set default states if no state is set.
        if (!isset($item['#status'])) {
          // Translation is present.
          if (!empty($item['#translation'])) {
            $item['#status'] = TMGMT_DATA_ITEM_STATE_TRANSLATED;
          }
          // No translation present.
          else {
            $item['#status'] = TMGMT_DATA_ITEM_STATE_PENDING;
          }
        }
        switch ($item['#status']) {
          case TMGMT_DATA_ITEM_STATE_REVIEWED:
            $entity->count_reviewed++;
            break;
          case TMGMT_DATA_ITEM_STATE_TRANSLATED:
            $entity->count_translated++;
            break;
          default:
            $entity->count_pending++;
            break;
        }
      }
    }
    else {
      foreach (element_children($item) as $key) {
        $this->count($item[$key], $entity);
      }
    }
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

    $query = new EntityFieldQuery();
    $result = $query->entityCondition('entity_type', 'tmgmt_remote')
        ->propertyCondition('tjiid', $ids)
        ->execute();
    if (!empty($result['tmgmt_remote'])) {
      $controller = entity_get_controller('tmgmt_remote');
      $controller->delete(array_keys($result['tmgmt_remote']), $transaction);
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
