<?php

/**
 * @file
 * Provides the user translator plugin controller.
 */

class TMGMTDummyTranslator extends TMGMTDefaultTranslatorPluginController {

  /**
   * It can 'translate' any job
   */
  function canTranslate(TMGMTJob $job) {
    return TRUE;
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::requestTranslation().
   */
  function requestTranslation(TMGMTJob $job) {
    $job->addTranslatedData($job->getSourceData());
    $job->needsReview('Dummy translation completed.');
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::requestTranslation().
   */
  function getSupportedTargetLanguages($source_language) {
    $languages = entity_metadata_language_list();
    unset($languages[LANGUAGE_NONE], $languages[$source_language]);
    return drupal_map_assoc(array_keys($languages));
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::jobSettingsForm().
   */
  function jobSettingsForm($form, &$form_state) {
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

}

