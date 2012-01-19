<?php

/**
 * @file
 * Provides the user translator plugin controller.
 */

class TMGMTTestTranslatorPluginController extends TMGMTDefaultTranslatorPluginController {

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::requestTranslation().
   */
  function requestTranslation(TMGMTJob $job) {
    // Do something different based on the target language.
    switch ($job->target_language) {
      case 'de':
        $job->submitted('Test submit');
        break;

      case 'es':
        $data = tmgmt_flatten_data($job->getSourceData());
        foreach ($data as $key => $value) {
          // Translate into spanish!
          $data[$key]['#text'] = str_replace('n', 'ñ', $value['#text']);
        }
        $job->addTranslatedData(tmgmt_unflatten_data($data));
        $job->needsReview('Test translation created');
        break;

      case 'it':
        $job->rejected(t('Translation into italian not supported'));
        break;

      case 'zh-hans':
        // Target not reachable.
        return $job->addMessage('Service not reachable');
        break;
    }
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::getSupportedTargetLanguages().
   */
  public function getSupportedTargetLanguages($source_language) {
    return drupal_map_assoc(array('de', 'es', 'it', 'zh-zans'));
  }
}

class TMGMTTestSourcePluginController extends TMGMTDefaultSourcePluginController {

  public function getData(TMGMTJobItem $job_item) {
    return array(
      'dummy' => array(
        'deep_nesting' => array(
          '#text' => 'Text for job item with type ' . $job_item->item_type . ' and id ' . $job_item->item_id . '.',
        ),
      ),
    );
  }

  public function saveTranslation(TMGMTJobItem $job_item) {
    // Set a variable that can be checked later for a given job item.
    variable_set('tmgmt_test_saved_translation_' . $job_item->item_type . '_' . $job_item->item_id, TRUE);
  }
}
