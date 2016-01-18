<?php

/**
 * @file
 * Contains \Drupal\tmgmt_local\Plugin\views\field\TaskItemStatus.
 */

namespace Drupal\tmgmt_local\Plugin\views\field;

use Drupal\tmgmt_local\Entity\LocalTaskItem;
use Drupal\views\Plugin\views\field\NumericField;
use Drupal\views\ResultRow;

/**
 * Field handler which shows the link for translating translation task items.
 *
 * @ViewsField("tmgmt_local_task_item_status")
 */
class TaskItemStatus extends NumericField {

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    $value = parent::render($values);

    $element = [
      '#type' => 'item',
      '#markup' => LocalTaskItem::getStatuses()[$value],
    ];
    return \Drupal::service('renderer')->render($element);
  }

}
