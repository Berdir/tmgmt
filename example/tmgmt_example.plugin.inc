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
    $form['sample_checkboxes'] = array(
      '#type' => 'checkboxes',
      '#title' => t('These are just some sample checkboxes.'),
      '#options' => array(
        1 => t('Option one'),
        2 => t('Option two'),
        3 => t('Option three'),
      ),
    );
    $form['sample_textfield'] = array(
      '#type' => 'textfield',
      '#title' => t('Sample comment'),
      '#default_value' => 'Your translation service sucks.',
      '#description' => t("Please don't yell at us when sending us a comment."),
    );
    return $form;
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::settingsForm().
   */
  public function settingsForm($form, &$form_state, TMGMTTranslator $translator) {
    $form['sample_textfield'] = array(
      '#type' => 'textfield',
      '#title' => t('Sample API key'),
      '#default_value' => 'Your translation service sucks.',
      '#description' => t('Please provide the API key.'),
    );
    $form['sample_checkboxes'] = array(
      '#type' => 'checkboxes',
      '#title' => t('These are just some sample checkboxes.'),
      '#default_value' => isset($form_state['values']['settings']['sample_checkboxes']) ? $form_state['values']['settings']['sample_checkboxes'] : $translator->getSetting('sample_checkboxes', array()),
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
