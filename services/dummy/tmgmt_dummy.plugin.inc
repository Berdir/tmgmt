<?php

/**
 * @file
 * Provides the user translator plugin controller.
 */

class TMGMTDummyTranslator extends TMGMTDefaultTranslatorPluginController {

  /**
   * It can 'translate' any job
   */
  function canTranslate(TMGMTJob $job) {
    return TRUE;
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::requestTranslation().
   */
  function requestTranslation(TMGMTJob $job) {
    $job->addTranslatedData($job->getSourceData());
    return new TMGMTTranslatorResponse(TMGMTJob::STATE_REVIEW, t('Dummy translation completed.'));
  }

}

