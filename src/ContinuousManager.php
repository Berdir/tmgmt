<?php

/**
 * @file
 * Contains \Drupal\tmgmt\ContinuousManager.
 */

namespace Drupal\tmgmt;

use Drupal\tmgmt\Entity\Job;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * A service manager for continuous jobs.
 */
class ContinuousManager {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new ContinuousManager.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Returns all continuous jobs with the given language.
   *
   * @param string $source_langcode
   *   Source language.
   *
   * @return array
   *   Array of continuous jobs.
   */
  public function getContinuousJobs($source_langcode) {
    $jobs = array();
    $ids = $this->entityTypeManager->getStorage('tmgmt_job')->getQuery()
      ->condition('source_language', $source_langcode)
      ->condition('job_type', Job::TYPE_CONTINUOUS)
      ->execute();
    if (!empty($ids)) {
      $jobs = Job::loadMultiple($ids);
    }
    return $jobs;
  }

  /**
   * Creates job item and submits it to the translator of the given job.
   *
   * @param \Drupal\tmgmt\Entity\Job $job
   *   Continuous job.
   * @param string $plugin
   *   The plugin name.
   * @param string $item_type
   *   The source item type.
   * @param string $item_id
   *   The source item id.
   */
  public function addItem(Job $job, $plugin, $item_type, $item_id) {
    $job_item = $job->addItem($plugin, $item_type, $item_id);
    $translator = $job->getTranslatorPlugin();
    $translator->requestJobItemsTranslation([$job_item]);
  }

}
