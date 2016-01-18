<?php

/**
 * @file
 * Contains \Drupal\tmgmt_local\Plugin\views\field\TaskStatus.
 */

namespace Drupal\tmgmt_local\Plugin\views\field;

use Drupal\tmgmt_local\Entity\LocalTask;
use Drupal\views\Plugin\views\field\NumericField;
use Drupal\views\ResultRow;

/**
 * Field handler which shows the link for translating translation task.
 *
 * @ViewsField("tmgmt_local_task_status")
 */
class TaskStatus extends NumericField {

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    $value = parent::render($values);

    $element = [
      '#type' => 'item',
      '#markup' => LocalTask::getStatuses()[$value],
    ];
    return \Drupal::service('renderer')->render($element);
  }

}
