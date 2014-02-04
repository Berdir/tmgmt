<?php

/**
 * @file
 * Contains the test source plugin.
 */

class TMGMTTestSourcePluginController extends TMGMTDefaultSourcePluginController {

  /**
   * {@inheritdoc}
   */
  public function getUri(TMGMTJobItem $job_item) {
    // Provide logic which allows to test for source which is either accessible
    // or not accessible to anonymous user. This is may then be used to test if
    // the source url is attached to the job comment sent to a translation
    // service.
    $path = 'node';
    if ($job_item->item_type == 'test_not_accessible') {
      $path = 'admin';
    }
    return array('path' => $path, 'options' => array());
  }

  /**
   * {@inheritdoc}
   */
  public function getLabel(TMGMTJobItem $job_item) {
    $label = $this->pluginType . ':' . $job_item->item_type . ':' . $job_item->item_id;

    // We need to test if job and job item labels get properly truncated,
    // therefore in case the job item type is "test_with_long_label" we append
    // further text to the existing label.
    if ($job_item->item_type == 'test_with_long_label') {
      $label .= 'Some very long and boring label that definitely exceeds hundred and twenty eight characters which is the maximum character count for the job item label.';
    }

    return $label;
  }

  /**
   * {@inheritdoc}
   */
  public function getData(TMGMTJobItem $job_item) {
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
   * {@inheritdoc}
   */
  public function saveTranslation(TMGMTJobItem $job_item) {
    // Set a variable that can be checked later for a given job item.
    variable_set('tmgmt_test_saved_translation_' . $job_item->item_type . '_' . $job_item->item_id, TRUE);
    $job_item->accepted();
  }
}
