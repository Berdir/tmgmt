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
    foreach ($job->getItems() as $item) {
      // Use original data as translated data.
      $data = $item->getSourceData();
      $item->addTranslatedData($data);
    }
    $job->save();
    return new TMGMTTranslatorResponse(TMGMTJob::STATE_REVIEW, t('Dummy translation completed.'));
  }

}

