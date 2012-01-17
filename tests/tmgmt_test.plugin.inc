<?php

/**
 * @file
 * Provides the user translator plugin controller.
 */

class TMGMTTestTranslatorPluginController extends TMGMTDefaultTranslatorPluginController {

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::submit().
   */
  function submit(TMGMTJob $job) {
    // Do something different based on the target language.
    switch ($job->target_language) {
      case 'de':
        $job->state = TMGMTJob::STATE_SUBMITTED;

        break;

      case 'es':
        $job->state = TMGMTJob::STATE_REVIEW;

        foreach ($job->getItems() as $item) {
          /* @var $item TMGMTJobItem */
          foreach ($item->getFlattenedSourceData() as $key => $data_entry) {
            // Translate into spanish!
            $item->updateTranslatedData($key, str_replace('n', 'ñ', $data_entry['#text']));
          }
        }
        break;

      case 'it':
        $job->state = TMGMTJob::STATE_REJECTED;
        $job->response = 'Translation into italian not supported';
        break;

      case 'zh-hans':
        // Target not reachable.
        return FALSE;
        break;
    }

    // @todo: Who nees to save the job?
    $job->save();
  }
}

class TMGMTTestSourcePluginController extends TMGMTDefaultSourcePluginController {

}
