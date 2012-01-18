<?php

/**
 * @file
 * Hooks provided by the Translation Management module.
 */

/**
 * @addtogroup source
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
 * @} End of "addtogroup translator".
 */

/**
 * @addtogroup translator
 * @{
 */

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
 * @} End of "addtogroup translator".
 */

/**
 * @defgroup job Translation Jobs
 *
 * A single task to translate something into a given language using a @link
 * translator translator @endlink.
 *
 * Attached to these jobs are job items, which specifiy which @link source
 * sources @endlink are to be translated.
 *
 * To create a new translation job, first create a job and then assign items to
 * each. Each item needs to specificy the source plugin that should be used
 * and the type and id, which the source plugin then uses to identify it later
 * on.
 *
 * @code
 * $job = tmgmt_job_create('en', $target_language);
 *
 * for ($i = 1; $i < 3; $i++) {
 *   $item = tmgmt_job_item_create('test_source', 'test', $i);
 *   $job->addItem($item);
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
 * $job->save();
 *
 * // Get the translator plugin and request a translation.
 * $job->requestTranslation();
 * @endcode
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
 * $job->setState(TMGMTJob::STATE_ACCEPTED);
 * $job->save();
 * $job->saveTranslations();
 * @endcode
 */

/**
 * @defgroup translator Translators
 *
 * A translator plugin integrates a translation service.
 *
 * To define a translator, hook_tmgmt_translator_plugin_info() needs to be
 * implemented and a controller class (specificed in the info) created.
 *
 * A translator plugin is then responsible for sending out a translation job and
 * storing the translated texts back into the job and marking it as needs review
 * once it's finished.
 *
 * TBD.
 */

/**
 * @defgroup source Translation source
 *
 * A source plugin represents translatable elements on a site.
 *
 * For example nodes, but also plain strings, menu items, other entities and so
 * on.
 *
 * To define a source, hook_tmgmt_source_plugin_info() needs to be implemented
 * and a controller class (specificed in the info) created.
 *
 * A source has three separate tasks.
 *
 * - Allows to reate a new @link job translation job @endlink and assign job
 *   items to itself.
 * - Extract the translatable text into a nested array when
 *   requested to do in their implementation of
 *   TMGMTSourcePluginControllerInterface::getData().
 * - Save the accepted translations returned by the translation plugin in their
 *   sources in their implementation of
 *   TMGMTSourcePluginControllerInterface::saveTranslation().
 */
