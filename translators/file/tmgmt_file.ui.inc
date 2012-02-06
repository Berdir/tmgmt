<?php

/**
 * @file
 * Please supply a file description.
 */

/**
 * File translator plugin controller.
 */
class TMGMTFileTranslatorUIController extends TMGMTDefaultTranslatorUIController {

  /**
   * TMGMTDefaultTranslatorUIController
   */
  public function pluginSettingsForm($form, &$form_state, TMGMTTranslator $translator, $busy = FALSE) {
    $form['export_format'] = array(
      '#type' => 'radios',
      '#title' => t('Export to'),
      '#options' => array(
        'xliff' => t('XLIFF'),
        'html' => t('HTML'),
      ),
      '#default_value' => $translator->getSetting('export_format'),
      '#description' => t('Please select the format you want to export data.'),
    );

    $form['allow_override'] = array(
      '#type' => 'checkbox',
      '#title' => t('Allow to override the format per job'),
      '#default_value' => $translator->getSetting('allow_override'),
    );

    return parent::pluginSettingsForm($form, $form_state, $translator);
  }

  /**
   * Implements TMGMTTranslatorUIControllerInterface::checkoutSettingsForm().
   */
  public function checkoutSettingsForm($form, &$form_state, TMGMTJob $job) {
    if ($job->getTranslator()->getSetting('allow_override')) {
      $form['export_format'] = array(
        '#type' => 'radios',
        '#title' => t('Export to'),
        '#options' => array(
          'xliff' => t('XLIFF'),
          'html' => t('HTML'),
        ),
        '#default_value' => $translator->getSetting('export_format'),
        '#description' => t('Please select the format you want to export data.'),
      );
    }
    parent::checkoutSettingsForm($form, $form_state, $job);
  }

}
