<?php

/**
 * @file
 * Contains Drupal\tmgmt\EntityController\JobItemStorageController.
 */

namespace Drupal\tmgmt\EntityController;

use Drupal\Core\Entity\DatabaseStorageController;
use Drupal\Core\Entity\EntityInterface;

/**
 * Controller class for the job item entity.
 *
 * @ingroup tmgmt_job
 */

/**
 * Controller class for the job item entity.
 *
 * @ingroup tmgmt_job
 */
class JobItemStorageController extends DatabaseStorageController {

  /**
   * Overrides Drupal\Core\Entity\DatabaseStorageController::preSave().
   *
   * @todo Eliminate the need to flatten and unflatten the JobItem data.
   */
  public function preSave(EntityInterface $entity) {
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
   * Overrides Drupal\Core\Entity\DatabaseStorageController::postDelete().
   */
  public function postDelete($entities) {
    // Since we are deleting one or multiple job items here we also need to
    // delete the attached messages.
    /*
    $mids = \Drupal::entityQuery('tmgmt_job_message')
      ->condition('tjiid', array_keys($entities))
      ->execute();
    if (!empty($mids)) {
      entity_delete_multiple('tmgmt_job_message', $mids);
    }
    }*/

    $trids = \Drupal::entityQuery('tmgmt_remote')
      ->condition('tjiid', array_keys($entities))
      ->execute();
    if (!empty($trids)) {
      entity_delete_multiple('tmgmt_remote', $trids);
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

  /**
   * Overrides Drupal\entity\DatabaseStorageConroller::attachLoad().
   */
  public function attachLoad(&$queried_entities, $revision_id = FALSE) {
    parent::attachLoad($queried_entities, $revision_id);
    foreach ($queried_entities as $queried_entity) {
      $queried_entity->data = unserialize($queried_entity->data);
    }
  }

}