<?php

/**
 * @file
 * Provides the user translator plugin controller.
 */

class TMGMTLocalTranslatorPluginController extends TMGMTDefaultTranslatorPluginController {

  /**
   * It can 'translate' any job
   */
  function canTranslate(TMGMTJob $job) {
    // @TODO Performance wise, we should go through tmgmt which could have it cached.
    $supported = $this->getSupportedTargetLanguages(NULL, $job->source_language);
    return isset($supported[$job->target_language]);
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::requestTranslation().
   */
  function requestTranslation(TMGMTJob $job) {
    //$job->addTranslatedData($job->getSourceData());
    $job->prepared('Job queued for local translation.');
  }

  /**
   * Returns all languages that are supported by this service.
   *
   * @param TMGMTTranslator $translator
   *   The translator entity.
   *
   * @return array
   *   An array of languages in ISO format.
   */
  public function getSupportedLanguages(TMGMTTranslator $translator) {
    $query = db_select('tmgmt_local_user', 't')
      ->fields('t', array('language'))
      ->condition('t.type', 'source')
      ->distinct();
    $list = $query->execute()->fetchCol();
    return array_flip($list);    
  }
  /**
   * Implements TMGMTTranslatorPluginControllerInterface::requestTranslation().
   */
  function getSupportedTargetLanguages(TMGMTTranslator $translator, $source_language) {
    $query = db_select('tmgmt_local_user', 'target')
      ->fields('target', array('language'))
      ->distinct();
    $query->join('tmgmt_local_user', 'source', 'target.uid = source.uid');
    $query->condition('source.language', $source_language);
    $list = $query->execute()->fetchCol();
    return array_flip($list);
  }

}

