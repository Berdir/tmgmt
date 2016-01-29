<?php

/**
 * @file
 * Contains \Drupal\tmgmt\Plugin\views\field\EntityLabel.
 */

namespace Drupal\tmgmt\Plugin\views\field;

use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Field handler which shows the label for a job or job item.
 *
 * @ViewsField("tmgmt_entity_label")
 */
class EntityLabel extends FieldPluginBase {

  function render(ResultRow $values) {
    if ($entity = $values->_entity) {
      return $entity->label();
    }
  }

}
