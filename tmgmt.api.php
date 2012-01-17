<?php

/**
 * @file
 * Hooks provided by the Translation Management module.
 */

/**
 * @addtogroup hooks
 * @{
 */

/**
 * Provide information about source plugins.
 */
function hook_tmgmt_source_plugin_info() {
  return array(
    'test_source' => array(
      'label' => t('Test source'),
      'description' => t('Simple source for testing purposes.'),
      'controller class' => 'TMGMTTestSourcePluginController',
    ),
  );
}

/**
 * Alter source plugins information.
 *
 * @param $info
 */
function hook_tmgmt_source_plugin_info_alter(&$info) {

}

/**
 * Provide information about translator plugins.
 */
function hook_tmgmt_translator_plugin_info() {
  return array(
    'test_translator' => array(
      'label' => t('Test translator'),
      'description' => t('Simple translator for testing purposes.'),
      'controller class' => 'TMGMTTestTranslatorPluginController',
    ),
  );
}

/**
 * Alter information about translator plugins.
 */
function hook_tmgmt_translator_plugin_info_alter(&$info) {

}

/**
 * @} End of "addtogroup hooks".
 */
