<?php

/**
 * @file
 * Provides Microsoft Translator ui controller.
 */

/**
 * Microsoft translator ui controller.
 */
class TMGMTMicrosoftTranslatorUIController extends TMGMTDefaultTranslatorUIController {

  /**
   * Overrides TMGMTDefaultTranslatorUIController::pluginSettingsForm().
   */
  public function pluginSettingsForm($form, &$form_state, TMGMTTranslator $translator, $busy = FALSE) {
    $form['clientid'] = array(
      '#type' => 'textfield',
      '#title' => t('Microsoft Client Id'),
      '#default_value' => $translator->getSetting('clientid'),
      '#description' => t('Please enter your Microsoft Client ID, or follow this !link to generate one.', array('!link' => l(t('instructions'), 'http://msdn.microsoft.com/en-us/library/hh454950.aspx'))),
    );
    $form['clientsecret'] = array(
      '#type' => 'textfield',
      '#title' => t('Microsoft Client Secret'),
      '#default_value' => $translator->getSetting('clientsecret'),
      '#description' => t('Please enter your Microsoft Client Secret, or follow this !link to generate one.', array('!link' => l(t('instructions'), 'http://msdn.microsoft.com/en-us/library/hh454950.aspx'))),
    );
    $form['api'] = array(
      '#type' => 'textfield',
      '#title' => t('Microsoft API Key'),
      '#default_value' => $translator->getSetting('api'),
      '#description' => t('This parameter is deprecated and not necessary. Switch to a client id as soon as possible if you are still using it.'),
    );
    return parent::pluginSettingsForm($form, $form_state, $translator);
  }

}
