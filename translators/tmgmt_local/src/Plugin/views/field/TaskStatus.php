<?php

/**
 * @file
 * Contains \Drupal\tmgmt_local\Plugin\views\field\TaskStatus.
 */

namespace Drupal\tmgmt_local\Plugin\views\field;

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
      '#markup' => tmgmt_local_task_statuses()[$value],
    ];
    return \Drupal::service('renderer')->render($element);
  }

}
