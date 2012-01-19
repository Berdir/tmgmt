<?php

/**
 * @file
 * Provides the user translator plugin controller.
 */

class TMGMTLocalTranslator extends TMGMTDefaultTranslatorPluginController {

  /**
   * It can 'translate' any job
   */
  function canTranslate(TMGMTJob $job) {
    // @TODO Check whether we actually have a user that can handle this translation.
    return TRUE;
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::requestTranslation().
   */
  function requestTranslation(TMGMTJob $job) {
    $job->addTranslatedData($job->getSourceData());
    return new TMGMTTranslatorResponse(TMGMTJob::STATE_ACCEPTED, t('Job queued for local translation.'));
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::requestTranslation().
   */
  function getSupportedTargetLanguages($source_language) {
    $languages = entity_metadata_language_list();
    unset($languages[LANGUAGE_NONE], $languages[$source_language]);
    return drupal_map_assoc(array_keys($languages));
  }

}

