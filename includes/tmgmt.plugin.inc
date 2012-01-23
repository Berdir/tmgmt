<?php

/**
 * @file
 * Contains the classes and interfaces for the services and source plugins.
 */

/**
 * Interface for source plugin controllers.
 *
 * @ingroup tmgmt_source
 */
interface TMGMTSourcePluginControllerInterface {

  /**
   * Constructor.
   */
  public function __construct($plugin);

  /**
   * Returns an array with the data structured for translation.
   *
   * @see TMGMTJobItem::getSourceData()
   */
  public function getData(TMGMTJobItem $job_item);

  /**
   * Saves a translation.
   *
   * @return bool
   *   TRUE if the translation was saved successfully, FALSE otherwise.
   */
  public function saveTranslation(TMGMTJobItem $job_item);

  /**
   * Return a title for this job item.
   */
  public function getTitle(TMGMTJobItem $job_item);

  /**
   * Returns the Uri for this job item.
   *
   * @see entity_uri()
   */
  public function getUri(TMGMTJobItem $job_item);

  /**
   * Returns the info of the type of the plugin.
   *
   * @see tmgmt_source_plugin_info()
   */
  public function pluginInfo();

  /**
   * Returns the type of the plugin.
   */
  public function pluginType();
}

/**
 * Default controller class for source plugins.
 *
 * @ingroup tmgmt_source
 */
abstract class TMGMTDefaultSourcePluginController implements TMGMTSourcePluginControllerInterface {

  protected $pluginType;
  protected $pluginInfo;

  /**
   * Constructor.
   */
  public function __construct($plugin) {
    $this->pluginType = $plugin;
    $this->pluginInfo = tmgmt_source_plugin_info($plugin);
  }

  /**
   * Implements TMGMTSourcePluginControllerInterface::getTitle().
   */
  public function getTitle(TMGMTJobItem $job_item) {
    return $job_item->source_plugin . ': ' . $job_item->item_type . ' :' . $job_item->item_id;
  }

  /**
   * Implements TMGMTSourcePluginControllerInterface::getUri().
   */
  public function getUri(TMGMTJobItem $job_item) {
    return array('path' => 'default/' . $job_item->item_id);
  }

  /**
   * Implements TMGMTSourcePluginControllerInterface::pluginInfo().
   */
  public function pluginInfo() {
    return $this->pluginInfo;
  }

  /**
   * Implements TMGMTSourcePluginControllerInterface::pluginType().
   */
  public function pluginType() {
    return $this->pluginType;
  }

}

/**
 * Interface for service plugin controllers.
 *
 * @ingroup tmgmt_translator
 */
interface TMGMTTranslatorPluginControllerInterface {

  /**
   * Constructor.
   */
  public function __construct($plugin);

  /**
   * Checks whether a translator is available.
   *
   * @param TMGMTTranslator $translator
   *   The translator entity.
   *
   * @return bool
   *   TRUE if the translator plugin is available, FALSE otherwise.
   */
  public function isAvailable(TMGMTTranslator $translator);

  /**
   * Return a reason why the translator is not available.
   *
   * @param TMGMTTranslator $translator
   *   The translator entity.
   *
   * Might be called when isAvailable() returns FALSE to get a reason that
   * can be displayed to the user.
   */
  public function getNotAvailableReason(TMGMTTranslator $translator);

  /**
   * Check whether this service can handle a particular translation job.
   *
   * @param TMGMTJob $job
   *   The TMGMTJob entity that should be translated.
   *
   * @return boolean
   *   TRUE if the job can be processed and translated, FALSE otherwise.
   */
  public function canTranslate(TMGMTJob $job);

  /**
   * Returns all available target languages that are supported by this service
   * when given a source language.
   *
   * @param TMGMTTranslator $translator
   *   The translator entity.
   * @param $source_language
   *   The source language.
   *
   * @return array
   *   An array of languages in ISO format.
   */
  public function getSupportedTargetLanguages(TMGMTTranslator $translator, $source_language);

  /**
   * Returns all languages that are supported by this service.
   *
   * @param TMGMTTranslator $translator
   *   The translator entity.
   *
   * @return array
   *   An array of languages in ISO format.
   */
  public function getSupportedLanguages(TMGMTTranslator $translator);

  /**
   * @abstract
   *
   * Submits the translation request and sends it to the translation provider.
   *
   * @param TMGMTJob $job
   *   The job that should be submitted.
   */
  public function requestTranslation(TMGMTJob $job);

  /**
   * Cancels a translation job.
   *
   * @param TMGMTJob $job
   *   The job that should have its translation cancelled.
   *
   * @return bool
   *   TRUE if the job could be cancelled, FALSE otherwise.
   */
  public function cancelTranslation(TMGMTJob $job);

  /**
   * Defines default settings for a translator plugin.
   *
   * @return array
   *   An array of default settings.
   */
  public function defaultTranslatorSettings();

  /**
   * A plugin can implement this method if it needs a settings form.
   *
   * This is not actually an interface method and that is why it is commented,
   * but we leave it here for API documentation.
   */
  public function translatorSettingsForm($form, &$form_state, TMGMTTranslator $translator);

  /**
   * A plugin can implement this method if it needs a job settings form.
   *
   * This is not actually an interface method and that is why it is commented,
   * but we leave it here for API documentation.
   */
  public function jobSettingsForm($form, &$form_state, TMGMTJob $job);

  /**
   * Returns the info of the type of the plugin.
   *
   * @see tmgmt_translator_plugin_info()
   */
  public function pluginInfo();

  /**
   * Returns the type of the plugin.
   */
  public function pluginType();
}

/**
 * Default controller class for service plugins.
 *
 * @ingroup tmgmt_translator
 */
abstract class TMGMTDefaultTranslatorPluginController implements TMGMTTranslatorPluginControllerInterface {

  protected $pluginType;
  protected $pluginInfo;

  /**
   * Constructor.
   *
   * @param $plugin
   *   The machine-readable name of the translator plugin.
   */
  public function __construct($plugin) {
    $this->pluginType = $plugin;
    $this->pluginInfo = tmgmt_translator_plugin_info($plugin);
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::isAvailable().
   */
  public function isAvailable(TMGMTTranslator $translator) {
    // Assume that the translation service is always available.
    return TRUE;
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::canTranslate().
   */
  public function canTranslate(TMGMTJob $job) {
    // The job is only translatable if the translator is available too.
    if ($this->isAvailable($job->getTranslator()) && array_key_exists($job->target_language, $this->getSupportedTargetLanguages($job->getTranslator(), $job->source_language))) {
      // We can only translate this job if the target language of the job is in
      // one of the supported languages.
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::cancelTranslation().
   */
  public function cancelTranslation(TMGMTJob $job) {
    // Assume that we can cancel a translation job at any time.
    return TRUE;
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::getSupportedTargetLanguages().
   */
  public function getSupportedTargetLanguages(TMGMTTranslator $translator, $source_language) {
    $languages = $this->getSupportedLanguages($translator);
    unset($languages[$source_language]);
    return $languages;
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::getSupportedLanguages().
   */
  public function getSupportedLanguages(TMGMTTranslator $translator) {
    $languages = entity_metadata_language_list();
    return drupal_map_assoc(array_keys($languages));
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::getNotAvailableReason().
   */
  public function getNotAvailableReason(TMGMTTranslator $translator) {
    return t('@plugin needs to be configured before it can be used', array('@plugin' => $this->pluginInfo['label']));
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::defaultTranslatorSettings().
   */
  public function defaultTranslatorSettings() {
    return array();
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::defaultJobSettings().
   */
  public function defaultJobSettings() {
    return array();
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::settingsForm().
   */
  public function translatorSettingsForm($form, &$form_state, TMGMTTRanslator $translator) {
    return array();
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::jobSettingsForm().
   */
  public function jobSettingsForm($form, &$form_state, TMGMTJob $job) {
    return array();
  }

  /**
   * Implements TMGMTSourcePluginControllerInterface::pluginInfo().
   */
  public function pluginInfo() {
    return $this->pluginInfo;
  }

  /**
   * Implements TMGMTSourcePluginControllerInterface::pluginType().
   */
  public function pluginType() {
    return $this->pluginType;
  }

}
