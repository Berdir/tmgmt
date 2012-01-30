<?php

/**
 * @file
 * Provides the i18n string source controller.
 */

class TMGMTI18nStringSourcePluginController extends TMGMTDefaultSourcePluginController {


  public function getData(TMGMTJobItem $job_item) {
    // @todo Why doesn't this use the item_type?
    $i18n_object = explode(':', $job_item->item_id);
    $i18n_object_type = array_shift($i18n_object);
    $i18n_object_id = (count($i18n_object) == 1) ? reset($i18n_object) : $i18n_object;
    $i18n_strings = i18n_object($i18n_object_type, $i18n_object_id)->get_strings();
    $structure = array('#label' => 'i18n Strings: ' . $i18n_object_type);
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
   * Save translation strings for a job item.
   *
   * @param TMGMTJobItem $item
   */
  public function saveTranslation(TMGMTJobItem $job_item) {
    $job = tmgmt_job_load($job_item->tjid);
    foreach ($job_item->translation as $i18n_string => $translation) {
      i18n_string_translation_update($i18n_string, $translation['#text'], $job->target_language);
    }
    return TRUE;
  }

  public function hook_menu() {
    $items = parent::hook_menu();

    $items['admin/tmgmt/source/i18n-string'] = array(
      'title' => 'i18n String Source',
      'page callback' => 'tmgmt_i18n_string_source_tab_page_default',
      'access arguments' => array('access tmgmt_ui'),
      'file' => 'tmgmt_i18n_string.admin.inc',
      'file path' => drupal_get_path('module', 'tmgmt_i18n_string'),
      'type' => MENU_LOCAL_TASK,
    );

    $i18n_object_info = i18n_object_info();
    foreach($i18n_object_info as $object_type => $definition) {
      if (isset($definition['string translation']['textgroup']) && isset($definition['string translation']['type'])) {
        $items['admin/tmgmt/source/i18n-string/' . $object_type] = array(
          'title' => $definition['title'],
          'access arguments' => array('access tmgmt_ui'),
          'page callback' => 'tmgmt_i18n_string_source_tab_page',
          'page arguments' => array($object_type),
          'file' => 'tmgmt_i18n_string.admin.inc',
          'file path' => drupal_get_path('module', 'tmgmt_i18n_string'),
          'type' => MENU_LOCAL_TASK,
        );
      }
    }

    return $items;
  }
}
