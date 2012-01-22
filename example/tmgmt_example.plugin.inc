<?php

/**
 * @file
 * Provides the user translator plugin controller.
 */

class TMGMTExampleTranslatorPluginController extends TMGMTDefaultTranslatorPluginController {

  /**
   * It can 'translate' any job
   */
  public function canTranslate(TMGMTJob $job) {
    return TRUE;
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::defaultJobSettings().
   */
  public function defaultJobSettings() {
    return array(
      // The 'First option' is the default.
      'sample_radios' => 1,
      // Default string for the textfield.
      'sample_textfield' => t('This is a example default string.'),
    );
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::defaultTranslatorSettings().
   */
  public function defaultTranslatorSettings() {
    return array(
      // 'Option one' and 'Option three' are checked by default.
      'sample_checkboxes' => array(1, 3),
      // Default string for the textfield.
      'sample_textfield' => t('This is a example default string.'),
    );
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::requestTranslation().
   */
  public function requestTranslation(TMGMTJob $job) {
    $wrapper = entity_metadata_wrapper('tmgmt_job', $job);
    // The example translation prefixes strings with the target language.
    $data = tmgmt_flatten_data($job->getSourceData());
    foreach ($data as $key => $value) {
      $data[$key]['#text'] = $wrapper->target_language->label() . ': ' . $value['#text'];
    }
    $job->addTranslatedData(tmgmt_unflatten_data($data));
    $job->needsReview('Example translation from @source_language to @target_language completed.', array('@source_language' => $wrapper->source_language->label(), '@target_language' => $wrapper->target_language->label()));
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::jobSettingsForm().
   */
  public function jobSettingsForm($form, &$form_state, TMGMTJob $job) {
    $form['sample_radios'] = array(
      '#type' => 'radios',
      '#title' => t('These are just some sample radios.'),
      '#options' => array(
        1 => t('First option'),
        2 => t('Second option'),
      ),
    );
    $form['sample_textfield'] = array(
      '#type' => 'textfield',
      '#title' => t('Sample comment'),
      '#default_value' => 'Your translation service sucks.',
      '#description' => t("Please don't yell at us when sending us a comment."),
      '#default_value' => isset($form_state['values']['settings']['sample_checkboxes']) ? $form_state['values']['settings']['sample_checkboxes'] : $job->getSetting('sample_checkboxes'),
    );
    return $form;
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::settingsForm().
   */
  public function translatorSettingsForm($form, &$form_state, TMGMTTranslator $translator) {
    $form['sample_textfield'] = array(
      '#type' => 'textfield',
      '#title' => t('Sample API key'),
      '#default_value' => isset($form_state['values']['settings']['sample_textfield']) ? $form_state['values']['settings']['sample_textfield'] : $translator->getSetting('sample_textfield'),
      '#description' => t('Please provide the API key.'),
    );
    $form['sample_checkboxes'] = array(
      '#type' => 'checkboxes',
      '#title' => t('These are just some sample checkboxes.'),
      '#default_value' => isset($form_state['values']['settings']['sample_checkboxes']) ? $form_state['values']['settings']['sample_checkboxes'] : $translator->getSetting('sample_checkboxes'),
      '#options' => array(
        1 => t('Option one'),
        2 => t('Option two'),
        3 => t('Option three'),
      ),
    );
    return $form;
  }

}

class TMGMTExampleSourcePluginController extends TMGMTDefaultSourcePluginController {

  public function getData(TMGMTJobItem $job_item) {

  }

  public function saveTranslation(TMGMTJobItem $job_item) {

  }

}
