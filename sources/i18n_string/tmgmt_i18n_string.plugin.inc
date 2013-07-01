<?php

/**
 * @file
 * Provides the i18n string source controller.
 */

/**
 * Translation plugin controller for i18n strings.
 */
class TMGMTI18nStringSourcePluginController extends TMGMTDefaultSourcePluginController {

  /**
   * Overrides TMGMTDefaultSourcePluginController::getData().
   */
  public function getData(TMGMTJobItem $job_item) {
    $i18n_object = $this->getI18nObjectWrapper($job_item);
    $structure = array('#label' => t('i18n Strings: @title', array('@title' => $i18n_object->get_title())));
    if ($i18n_object instanceof i18n_string_object_wrapper) {
      $i18n_strings = $i18n_object->get_strings();
      foreach ($i18n_strings as $string_id => $string) {
        $structure[$string_id] = array(
          '#label' => $string->title,
          '#text' => $string->string,
          '#translate' => TRUE
        );
      }
    }
    return $structure;
  }

  /**
   * Overrides TMGMTDefaultSourcePluginController::saveTranslation().
   */
  public function saveTranslation(TMGMTJobItem $job_item) {
    $job = tmgmt_job_load($job_item->tjid);
    $data = array_filter(tmgmt_flatten_data($job_item->getData()), '_tmgmt_filter_data');
    foreach ($data as $i18n_string => $item) {
      if (isset($item['#translation']['#text'])) {
        i18n_string_translation_update($i18n_string, $item['#translation']['#text'], $job->target_language);
      }
    }

    // We just saved the translation, set the state of the job item to
    // 'finished'.
    $job_item->accepted();
  }

  /**
   * Implements TMGMTSourcePluginControllerInterface::getLabel().
   */
  public function getLabel(TMGMTJobItem $job_item) {
    if ($i18n_object = $this->getI18nObjectWrapper($job_item)) {
      return strip_tags(drupal_substr($i18n_object->get_title(), 0, 64)) . ' (' . $job_item->item_id . ')';
    }
    return parent::getLabel($job_item);
  }

  /**
   * Implements TMGMTSourcePluginControllerInterface::getUri().
   */
  public function getUri(TMGMTJobItem $job_item) {
    if ($wrapper = $this->getI18nObjectWrapper($job_item)) {
      return array(
        'path' => $wrapper->get_path(),
        'options' => array(),
      );
    }
  }

  /**
   * Implements TMGMTEntitySourcePluginControllerInterface::getType().
   */
  public function getType(TMGMTJobItem $job_item) {
    if ($label = $this->getItemTypeLabel($job_item->item_type)) {
      return $label;
    }
    return parent::getType($job_item);
  }


  /**
   * Helper function to get i18n_object_wrapper for given job item.
   *
   * @param TMGMTJobItem $job_item
   *
   * @return i18n_string_object_wrapper
   */
  protected function getI18nObjectWrapper(TMGMTJobItem $job_item) {
    $identifiers = $this->getI18nObjectIdentifiers($job_item);
    return i18n_get_object($job_item->item_type, $identifiers['id']);
  }

  /**
   * Parses TMGMTJobItem::item_id for i18n string identifiers.
   *
   * @param TMGMTJobItem $job_item
   *
   * @return array
   *   Associative array of identifiers:
   *   - textgroup
   *   - type
   *   - id
   */
  protected function getI18nObjectIdentifiers(TMGMTJobItem $job_item) {
    $identifiers = explode(':', $job_item->item_id);
    return array(
      'textgroup' => array_shift($identifiers),
      'type' => array_shift($identifiers),
      'id' => (count($identifiers) == 1) ? reset($identifiers) : $identifiers,
    );
  }

}
