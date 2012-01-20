<?php

/**
 * @file
 * Admin page callbacks for the mygengo module.
 */

/**
 * Form builder; Configure Microsoft Translator service.
 */
function tmgmt_mygengo_settings() {
  $form['tmgmt_mygengo_api_public_key'] = array(
    '#type' => 'textfield',
    '#title' => t('myGengo API Public key'),
    '#default_value' => variable_get('tmgmt_mygengo_api_public_key', ''),
    '#description' => t('Please enter yout myGengo API Public key.'),
  );

  $form['tmgmt_mygengo_api_private_key'] = array(
    '#type' => 'textfield',
    '#title' => t('myGengo API Private key'),
    '#default_value' => variable_get('tmgmt_mygengo_api_private_key', ''),
    '#description' => t('Please enter your myGengo API Private key.'),
  );

  return system_settings_form($form);
}
