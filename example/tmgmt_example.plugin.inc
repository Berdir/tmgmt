<?php

/**
 * @file
 * Provides the user translator plugin controller.
 */

class TMGMTExampleTranslatorPluginController extends TMGMTDefaultTranslatorPluginController {

  /**
   * Overrides
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
    $job->needsReview('Example translation from @source to @target completed.', array('@source' => $wrapper->source_language->label(), '@target' => $wrapper->target_language->label()));
  }

  /**
   * Overrides TMGMTDefaultTranslatorPluginController::checkoutSettingsForm().
   */
  public function checkoutSettingsForm($form, &$form_state, TMGMTJob $job) {
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
    );
    return $form;
  }

  /**
   * Overrides TMGMTDefaultTranslatorPluginController::pluginSettingsForm().
   */
  public function pluginSettingsForm($form, &$form_state, TMGMTTranslator $translator, $busy = FALSE) {
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
    return parent::pluginSettingsForm($form, $form_state, $translator, $busy);
  }

}
