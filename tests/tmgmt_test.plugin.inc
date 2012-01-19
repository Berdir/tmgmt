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

    // Do something different based on the action, if defined.
    $action = isset($job->translator_context['action']) ? $job->translator_context['action'] : '';
    switch ($action) {
      case 'submit':
        $job->submitted('Test submit');
        return;
        break;

      case 'reject':
        $job->rejected(t('Translation into italian not supported'));
        return;
        break;

      case 'fail':
        // Target not reachable.
        $job->addMessage('Service not reachable');
        return;
        break;

      case 'translate':
      default:
        // The dummy translation prefixes strings with the target language.
        $data = tmgmt_flatten_data($job->getSourceData());
        foreach ($data as $key => $value) {
          $data[$key]['#text'] = $job->target_language . '_' . $value['#text'];
        }
        $job->addTranslatedData(tmgmt_unflatten_data($data));
        $job->needsReview('Test translation created');
        break;
    }
    return TRUE;
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
