<?php

/**
 * @file
 * Contains \Drupal\tmgmt\Plugin\views\field\WordCount.
 */

namespace Drupal\tmgmt_local\Plugin\views\field;

use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Field handler which shows the word count for a job or job item.
 *
 * @ViewsField("tmgmt_local_wordcount")
 */
class WordCount extends FieldPluginBase {

  /**
   * {@inheritdoc}
   *
   * Prefetch statistics for all jobs.
   */
  public function preRender(&$values) {
    parent::preRender($values);

    // In case of jobs, pre-fetch the statistics in a single query and add them
    // to the static cache.
    if ($this->getEntityType() == 'tmgmt_task') {
      $tjids = array();
      foreach ($values as $value) {
        $tjids[] = $value->tjid;
      }
      tmgmt_local_task_statistics_load($tjids);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    /** @var \Drupal\tmgmt_local\LocalTaskInterface $entity */
    $entity = $values->_entity;
    return $entity->getWordCount();
  }

}
