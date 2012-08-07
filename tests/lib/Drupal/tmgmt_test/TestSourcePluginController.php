<?php

/**
 * @file
 * Contains Drupal\tmgmt_test\TestSourcePluginController.
 */

namespace Drupal\tmgmt_test;

use Drupal\tmgmt\DefaultSourcePluginController;
use Drupal\tmgmt\Plugin\Core\Entity\JobItem;

/**
 * Test source plugin implementation.
 */
class TestSourcePluginController extends DefaultSourcePluginController {

  /**
   * Overrides Drupal\tmgmt\DefaultSourcePluginController::getLabel().
   */
  public function getLabel(JobItem $job_item) {
    return $this->pluginType . ':' . $job_item->item_type . ':' . $job_item->item_id;
  }

  /**
   * Implements Drupal\tmgmt\SourcePluginControllerInterface::getData().
   */
  public function getData(JobItem $job_item) {
    return array(
      'dummy' => array(
        'deep_nesting' => array(
          '#text' => 'Text for job item with type ' . $job_item->item_type . ' and id ' . $job_item->item_id . '.',
          '#label' => 'Label for job item with type ' . $job_item->item_type . ' and id ' . $job_item->item_id . '.',
        ),
      ),
    );
  }

  /**
   * Implements Drupal\tmgmt\SourcePluginControllerInterface::saveTranslation().
   */
  public function saveTranslation(JobItem $job_item) {
    // Set a variable that can be checked later for a given job item.
    state()->set('tmgmt_test_saved_translation_' . $job_item->item_type . '_' . $job_item->item_id, TRUE);
    $job_item->accepted();
  }
}