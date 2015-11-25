<?php
/**
 * @file
 * Contains \Drupal\tmgmt_local\Entity\ViewsData\LocalTaskViewsData.
 */

namespace Drupal\tmgmt_local\Entity\ViewsData;

use Drupal\views\EntityViewsData;

/**
 * Provides the views data for the local task entity type.
 */
class LocalTaskViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();

    $data['tmgmt_local_task']['operations'] = array(
    'title' => t('Operations'),
      'help' => t('Displays a list of operations which are available for a task.'),
      'real field' => 'tltid',
      'field' => array(
      'handler' => 'tmgmt_local_task_handler_field_operations',
    ),
    );
    $data['tmgmt_local_task']['progress'] = array(
      'title' => t('Progress'),
      'help' => t('Displays the progress of a job.'),
      'real field' => 'tltid',
      'field' => array(
        'handler' => 'tmgmt_local_task_handler_field_progress',
      ),
    );
    $data['tmgmt_local_task']['word_count'] = array(
      'title' => t('Word count'),
      'help' => t('Displays the word count of a job.'),
      'real field' => 'tltid',
      'field' => array(
        'handler' => 'tmgmt_local_task_handler_field_wordcount',
      ),
    );
    $data['tmgmt_local_task']['item_count'] = array(
      'title' => t('Job item count'),
      'help' => t('Show the amount of job items per task (per job item status)'),
      'real field' => 'tltid',
      'field' => array(
        'handler' => 'tmgmt_local_task_handler_field_job_item_count',
      ),
    );
    $data['tmgmt_job']['eligible'] = array(
      'title' => t('Eligible'),
      'help' => t('Limit translation tasks to those that the user can translate'),
      'real field' => 'tltid',
      'filter' => array(
        'options callback' => 'local_task_eligible',
      ),
    );
    // Manager handlers.
    $data['tmgmt_job']['task'] = array(
      'title' => t('Translation task'),
      'help' => t('Get the translation task of the job'),
      'relationship' => array(
        'base' => 'tmgmt_local_task',
        'base field' => 'tjid',
        'real field' => 'tjid',
        'label' => t('Job'),
      ),
    );

    return $data;
  }

}
