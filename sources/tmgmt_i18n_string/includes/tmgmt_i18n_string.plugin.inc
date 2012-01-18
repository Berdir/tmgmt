<?php

/**
 * @file
 * Provides the i18n string source controller.
 */

/**
 * @ingroup source
 */
class TMGMTI18nStringSourcePluginController extends TMGMTDefaultSourcePluginController {

  public function getData(TMGMTJobItem $job_item) {
    // TODO: find a way to get the default string.
    $string = 'TODO';

    return array(
      '#text' => $string,
    );
  }

  public function saveTranslation(TMGMTJobItem $job_item) {
    $job = tmgmt_job_load($job_item->tjid);
    $langcode = $job->target_language;

    i18n_string_translation_update($job_item->item_id, $job_item->translated_data, $langcode);
  }
}
