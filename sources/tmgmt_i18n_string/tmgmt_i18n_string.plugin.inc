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
    $i18n_object = explode(':', $job_item->item_id);
    $i18n_object_type = array_shift($i18n_object);
    $i18n_object_id = (count($i18n_object) == 1) ? reset($i18n_object) : $i18n_object;
    $i18n_strings = i18n_object($i18n_object_type, $i18n_object_id)->get_strings();

    $structure  = array();
    $structure['#label'] = 'i18n Strings: ' . $i18n_object_type;
    foreach ($i18n_strings as $string_id => $string) {
      $structure[$string_id] = array(
        '#label' => $string->title,
        '#text' => $string->string,
        '#translate' => TRUE
      );
    }

    return $structure;
  }

  /**
   * Save translation strings for $job_item.
   *
   * @param TMGMTJobItem $job_item
   */
  public function saveTranslation(TMGMTJobItem $job_item) {
    $job = tmgmt_job_load($job_item->tjid);
    $langcode = $job->target_language;

    foreach ($job_item->translated_data as $i18n_string => $translated_data) {
      i18n_string_translation_update($i18n_string, $translated_data['#text'], $langcode);
    }
  }
}
