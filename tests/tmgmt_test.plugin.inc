<?php

/**
 * @file
 * Provides the user translator plugin controller.
 */

class TMGMTTestTranslatorPluginController extends TMGMTDefaultTranslatorPluginController {

  /**
   * Overrides TMGMTDefaultTranslatorPluginController::pluginSettingsForm().
   */
  public function pluginSettingsForm($form, &$form_state, TMGMTTranslator $translator, $busy = FALSE) {
    $form['expose_settings'] = array(
      '#type' => 'checkbox',
      '#title' => t('Display settings'),
      '#default_value' => TRUE,
    );
    return parent::pluginSettingsForm($form, $form_state, $translator);
  }

  /**
   * Overrides TMGMTDefaultTranslatorPluginController::defaultSettings().
   */
  public function defaultSettings() {
    return array(
      'expose_settings' => TRUE,
    );
  }

  /**
   * Overrides TMGMTDefaultTranslatorPluginController::checkoutSettingsForm().
   */
  public function checkoutSettingsForm($form, &$form_state, TMGMTJob $job) {
    if ($job->getTranslator()->getSetting('expose_settings')) {
      $settings['action'] = array(
        '#type' => 'select',
        '#title' => t('Action'),
        '#options' => array(
          'translate' => t('Translate'),
          'submit' => t('Submit'),
          'reject' => t('Reject'),
          'fail' => t('Fail'),
          'not_translatable' => t('Not translatable'),
        ),
      );
      return $settings;
    }
    return parent::checkoutSettingsForm($form, $form_state, $job);
  }

  /**
   * Overrides TMGMTDefaultTranslatorPluginController::hasCheckoutSettings().
   */
  public function hasCheckoutSettings(TMGMTJob $job) {
    return $job->getTranslator()->getSetting('expose_settings');
  }

  /**
   * Overrides TMGMTDefaultTranslatorPluginController::requestTranslation().
   */
  function requestTranslation(TMGMTJob $job) {
    // Add a debug message.
    $job->addMessage('Test translator called', array(), 'debug');

    // Do something different based on the action, if defined.
    $action = isset($job->settings['action']) ? $job->settings['action'] : '';
    switch ($action) {
      case 'submit':
        $job->submitted('Test submit');
        return;
        break;

      case 'reject':
        $job->rejected('This is not supported');
        return;
        break;

      case 'fail':
        // Target not reachable.
        $job->addMessage('Service not reachable', array(), 'error');
        return;
        break;

      case 'translate':
      default:
        // The dummy translation prefixes strings with the target language.
        $data = tmgmt_flatten_data($job->getSourceData());
        foreach ($data as $key => $value) {
          $data[$key]['#text'] = $job->target_language . '_' . $value['#text'];
        }
        $job->addTranslatedData(tmgmt_unflatten_data($data));
        $job->needsReview('Test translation created');
        break;
    }
    return TRUE;
  }

  /**
   * Overrides TMGMTDefaultTranslatorPluginController::canTranslate().
   */
  function canTranslate(TMGMTJob $job) {
    if (isset($job->settings['action']) && $job->settings['action'] == 'not_translatable') {
      return FALSE;
    }
    return parent::canTranslate($job);
  }

  /**
   * Overrides TMGMTDefaultTranslatorPluginController::getSupportedLanguages().
   */
  public function getSupportedLanguages(TMGMTTranslator $translator) {
    return drupal_map_assoc(array('en', 'de', 'es', 'it', 'zh-hans'));
  }

}

class TMGMTTestSourcePluginController extends TMGMTDefaultSourcePluginController {

  /**
   * Overrides TMGMTDefaultSourcePluginController::getLabel().
   */
  public function getLabel(TMGMTJobItem $job_item) {
    return $this->pluginType . ':' . $job_item->item_type . ':' . $job_item->item_id;
  }

  /**
   * Implements TMGMTSourcePluginControllerInterface::getData().
   */
  public function getData(TMGMTJobItem $job_item) {
    return array(
      'dummy' => array(
        'deep_nesting' => array(
          '#text' => 'Text for job item with type ' . $job_item->item_type . ' and id ' . $job_item->item_id . '.',
        ),
      ),
    );
  }

  /**
   * Implements TMGMTSourcePluginControllerInterface::saveTranslation().
   */
  public function saveTranslation(TMGMTJobItem $job_item) {
    // Set a variable that can be checked later for a given job item.
    variable_set('tmgmt_test_saved_translation_' . $job_item->item_type . '_' . $job_item->item_id, TRUE);
  }
}
