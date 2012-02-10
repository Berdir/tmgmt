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
   * The settings form.
   */
  public function pluginSettingsForm($form, &$form_state, TMGMTTranslator $translator, $busy = FALSE) {
    $form['export_format'] = array(
      '#type' => 'radios',
      '#title' => t('Export to'),
      '#options' => array(
        'html' => t('HTML'),
        'xliff' => t('XLIFF'),
      ),
      '#default_value' => $translator->getSetting('export_format'),
      '#description' => t('Please select the format you want to export data.'),
    );
    return parent::pluginSettingsForm($form, $form_state, $translator);

  }
}
