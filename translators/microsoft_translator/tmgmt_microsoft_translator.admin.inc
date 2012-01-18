<?php

/**
 * @file
 * Admin page callbacks for the microsoft_translator module.
 */

/**
 * Form builder; Configure Microsoft Translator service.
 *
 * @ingroup forms
 */
function tmgmt_microsoft_translator_settings() {
  $form['tmgmt_microsoft_translator_bing_api_id'] = array(
    '#type' => 'textfield',
    '#title' => t('Bing API ID'),
    // @todo - remove default test bing id before production release.
    '#default_value' => variable_get('tmgmt_microsoft_translator_bing_api_id', 'DA903AE0DF7668FD33F53192B5382E4D1D2CF369'),
    '#description' => t('Please enter yout Bing API ID, you can generate one on the !link.', array('!link' => l('http://www.bing.com/developers/appids.aspx', 'http://www.bing.com/developers/appids.aspx.'))),
  );

  return system_settings_form($form);
}
