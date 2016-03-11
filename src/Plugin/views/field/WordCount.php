<?php

/**
 * @file
 * Contains \Drupal\tmgmt\Plugin\views\field\WordCount.
 */

namespace Drupal\tmgmt\Plugin\views\field;

use Drupal\views\ResultRow;

/**
 * Field handler which shows the word count for a job or job item.
 *
 * @ViewsField("tmgmt_wordcount")
 */
class WordCount extends StatisticsBase {

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    $entity = $values->_entity;
    if ($entity->getEntityTypeId() == 'tmgmt_job' && $entity->isContinuous()) {
      return;
    }
    return $entity->getWordCount();
  }
}
