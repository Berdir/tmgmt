<?php

/**
 * @file
 * Contains \Drupal\tmgmt\Plugin\views\field\JobItemType.
 */

namespace Drupal\tmgmt\Plugin\views\field;

use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Field handler which shows the operations for a job.
 *
 * @ViewsField("tmgmt_job_item_type")
 */
class JobItemType extends FieldPluginBase {

  function render(ResultRow $values) {
    if ($entity = $values->_entity) {
      return $entity->getSourceType();
    }
  }

}
