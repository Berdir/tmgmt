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

/**
 * @defgroup job Translation Jobs
 *
 * A single task to translate something into a given language using a @link
 * translator Translator @endlink.
 *
 * Attached to these jobs are job items, which specifiy which @link source
 * Sources @endlink are to be translated.
 *
 * To create a new translation job, first create a job and then assign items to
 * each. Each item needs to specificy the source plugin that should be used
 * and the type and id, which the source plugin then uses to identify it later
 * on.
 *
 * @code
 * $job = tmgmt_job_create('en', $target_language);
 * // Job needs to be saved first.
 * // @todo: Fix this.
 * $job->save();
 *
 * for ($i = 1; $i < 3; $i++) {
 *   $item = tmgmt_job_item_create('test_source', 'test', $i);
 *   tmgmt_job_add_item($job, $item);
 * }
 * @endcode
 *
 * Once a job has been created, it can be assigned to a translator plugin, which
 * is the service that is going to do the translation.
 *
 * @code
 * $job->translator = 'test_translator';
 * // Translator specific settings.
 * $job->translator_context = array(
 *   'prioritoy' => 5,
 * );
 * @endcode
 *
 * // Get the translator plugin and request a translation.
 * $translator = $job->getTranslatorPlugin();
 * $translator->requestTranslation($job);
 * ?>
 *
 * The translation plugin will then request the text from the source plugin.
 * Depending on the plugin, the text might be sent to an external service
 * or assign it to a local user or team of users. At some point, a translation
 * will be returned and saved in the translation.
 *
 * The translation can now be reviewed, accepted and the source plugins be told
 * // to save the translation.
 *
 * @code
 * // @todo: Avoid direct state assignements.
 * $job->state = TMGMTJob::STATE_ACCEPTED;
 * $job->save();
 * tmgmt_job_save_translations($job);
 * // @todo: Does this need a new state?
 * @endcode
 */

/**
 * @defgroup translator Translators
 *
 * @todo
 */

/**
 * @defgroup source Translation source
 *
 * @todo
 */
