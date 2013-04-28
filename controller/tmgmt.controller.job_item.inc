<?php

/**
 * @file
 * Contains the job item entity controller class.
 */

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
