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
    return array(
      '#text' => i18n_string_get_source($job_item->item_id),
    );
  }

  public function saveTranslation(TMGMTJobItem $job_item) {
    $job = tmgmt_job_load($job_item->tjid);
    $langcode = $job->target_language;

    i18n_string_translation_update($job_item->item_id, $job_item->translated_data, $langcode);
  }
}
