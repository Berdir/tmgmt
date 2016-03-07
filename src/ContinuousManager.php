<?php

/**
 * @file
 * Contains \Drupal\tmgmt\ContinuousManager.
 */

namespace Drupal\tmgmt;

use Drupal\Core\Config\ConfigFactoryInterface;
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
   * The source plugin manager.
   *
   * @var \Drupal\tmgmt\SourceManager
   */
  protected $sourcePluginManager;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Constructs a new ContinuousManager.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\tmgmt\SourceManager $source_plugin_manager
   *   The source plugin manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, SourceManager $source_plugin_manager, ConfigFactoryInterface $config_factory) {
    $this->entityTypeManager = $entity_type_manager;
    $this->sourcePluginManager = $source_plugin_manager;
    $this->configFactory = $config_factory;
  }

  /**
   * Checks if there is any continuous job.
   *
   * @return bool
   *   Returns TRUE if there is continuous job, FALSE otherwise.
   */
  public function hasContinuousJobs() {
    $id = $this->entityTypeManager->getStorage('tmgmt_job')->getQuery()
      ->condition('job_type', Job::TYPE_CONTINUOUS)
      ->range(0, 1)
      ->execute();
    return !empty($id);
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
   * Creates job item and submits according to the configured settings.
   *
   * The job item will only be created if the given source plugin for the job is
   * configured to accept this source.
   *
   * The job item will be immediately submitted to the translator unless
   * this happens on cron runs.
   *
   * @param \Drupal\tmgmt\Entity\Job $job
   *   Continuous job.
   * @param string $plugin
   *   The plugin name.
   * @param string $item_type
   *   The source item type.
   * @param string $item_id
   *   The source item id.
   *
   * @return \Drupal\tmgmt\Entity\JobItem
   *   Continuous job item.
   */
  public function addItem(Job $job, $plugin, $item_type, $item_id) {
    // Check if a job item should be created.
    $most_recent_job_item = $job->getMostRecentItem($plugin, $item_type, $item_id);
    $should_create_item = $this->sourcePluginManager->createInstance($plugin)->shouldCreateContinuousItem($job, $plugin, $item_type, $item_id);
    if ($should_create_item) {
      if ($most_recent_job_item) {
        // If the most recent job item is not yet submitted update its data.
        if ($most_recent_job_item->isInactive()) {
          $most_recent_job_item->resetData();
          $most_recent_job_item->addMessage('Updated source data.');
          return NULL;
        }
        // If the most recent job item is active do nothing.
        elseif (!$most_recent_job_item->isAborted() && !$most_recent_job_item->isAccepted()) {
          $most_recent_job_item->addMessage('Source was updated, changes were ignored as job item is still active.');
          return NULL;
        }
      }
      // If there are no job items or it's finished/aborted create new one.
      $job_item = $job->addItem($plugin, $item_type, $item_id);
      $job_item->addMessage('Continuous job item created');

      // Only submit the item if cron submission is disabled.
      if (!$this->configFactory->get('tmgmt.settings')->get('submit_job_item_on_cron')) {
        $translator = $job->getTranslatorPlugin();
        $translator->requestJobItemsTranslation([$job_item]);
      }
      return $job_item;
    }
    return NULL;
  }

}
