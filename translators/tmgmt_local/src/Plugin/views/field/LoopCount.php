<?php

/**
 * @file
 * Contains \Drupal\tmgmt\Plugin\views\field\LoopCount.
 */

namespace Drupal\tmgmt\Plugin\views\field;

use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Field handler which shows the word count for a job or job item.
 *
 * @ViewsField("tmgmt_local_loopcount")
 */
class LoopCount extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  function render(ResultRow $values) {
    $entity = $values->_entity;
    return $entity->getLoopCount();
  }
}
