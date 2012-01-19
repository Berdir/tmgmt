<?php

/**
 * @file
 * Admin page callbacks for the mygengo module.
 */

/**
 * Form builder; Configure Microsoft Translator service.
 *
 * @ingroup forms
 */
function tmgmt_mygengo_settings() {
  $form['tmgmt_mygengo_api_key'] = array(
    '#type' => 'textfield',
    '#title' => t('myGengo API key'),
    '#default_value' => variable_get('tmgmt_mygengo_api_key', ''),
    '#description' => t('Please enter yout myGengo API key.'),
  );

  return system_settings_form($form);
}
