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
    // The dummy translation prefixes strings with the target language.
    $data = tmgmt_flatten_data($job->getSourceData());
    foreach ($data as $key => $value) {
      $data[$key]['#text'] = $job->target_language . '_' . $value['#text'];
    }
    $job->addTranslatedData(tmgmt_unflatten_data($data));

    // Do something different based on the target language.
    switch ($job->target_language) {
      case 'de':
        return new TMGMTTranslatorResponse(TMGMTJob::STATE_SUBMITTED, t('Test submit'));
        break;

      case 'es':
        return new TMGMTTranslatorResponse(TMGMTJob::STATE_REVIEW, t('Test translation created'));
        break;

      case 'it':
        return new TMGMTTranslatorResponse(TMGMTJob::STATE_REJECTED, t('Translation into italian not supported'));
        break;

      case 'zh-hans':
        // Target not reachable.
        return new TMGMTTranslatorResponse(NULL, t('Service not reachable'));
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
