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
        return new TMGMTTranslatorResponse(TMGMTJob::STATE_SUBMITTED, t('Test submit'));
        break;

      case 'es':
        foreach ($job->getItems() as $item) {
          /* @var $item TMGMTJobItem */
          $data = tmgmt_flatten_data($item->getSourceData());
          foreach ($data as $key => $data_entry) {
            // Translate into spanish!

            $data[$key]['#text'] = str_replace('n', 'ñ', $data_entry['#text']);
          }
          $item->addTranslatedData(tmgmt_unflatten_data($data));
        }
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