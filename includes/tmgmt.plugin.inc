<?php

/**
 * @file
 * Contains the classes and interfaces for the services and source plugins.
 */

/**
 * Prefix for source overview pages.
 *
 * @todo
 * Maybe move into the ui.
 *
 */
define('TMGMT_SOURCE_OVERVIEW_TAB_PATH', 'admin/tmgmt-overview');

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
   * Returns the URi for this job item.
   *
   * @see entity_uri()
   */
  public function getUri(TMGMTJobItem $job_item);

  /**
   * Implements hook_menu().
   *
   * @return array
   *   An array of menu items that this translator plugin provides.
   */
  public function hook_menu();
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
   * Implements TMGMTSourcePluginControllerInterface::getUridir().
   */
  public function getUri(TMGMTJobItem $job_item) {
    return array('path' => 'default/' . $job_item->item_id);
  }

  /**
   * Implements hook_menu().
   *
   * @return array
   *   An array of menu items that this translator plugin provides.
   */
  public function hook_menu() {
    return array();
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
   * @return bool
   *   TRUE if the translator plugin is available, FALSE otherwise.
   */
  public function isAvailable();

  /**
   * Return a reason why the translator is not available.
   *
   * Might be called when isAvailable() returns FALSE to get a reason that
   * can be displayed to the user.
   */
  public function getNotAvailableReason();

  /**
   * Check whether this service can handle a particular translation job.
   *
   * @param TMGMTJob $job
   *   The TMGMTJob entity that should be submitted.
   *
   * @return boolean
   *   TRUE if the job can be processed and translated, FALSE otherwise.
   */
  public function canTranslate(TMGMTJob $job);

  /**
   * Returns all available target languages that are supported by this service
   * when given a source language.
   *
   * @param $source_language
   *   The source language.
   * @return array
   *   An array of languages in ISO format.
   */
  public function getSupportedTargetLanguages($source_language);

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
   * Implements hook_menu().
   *
   * @return array
   *   An array of menu items that this translator plugin provides.
   */
  public function hook_menu();

  /**
   * A plugin can implement this method if it needs a settings form.
   *
   * This is not actually an interface method and that is why it is commented,
   * but we leave it here for API documentation.
   */
  //public function settingsForm($form, &$form_state);

  /**
   * A plugin can implement this method if it needs a job settings form.
   *
   * This is not actually an interface method and that is why it is commented,
   * but we leave it here for API documentation.
   */
  //public function jobSettingsForm($form, &$form_state, TMGMTJob $job);

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
  public function isAvailable() {
    // Assume that the translation service is always available.
    return TRUE;
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::canTranslate().
   */
  public function canTranslate(TMGMTJob $job) {
    // The job is only translatable if the translator is available too.
    if ($this->isAvailable() && array_key_exists($job->target_language, $this->getSupportedTargetLanguages($job->source_language))) {
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
   * Implements TMGMTTranslatorPluginControllerInterface::getNotAvailableReason().
   */
  public function getNotAvailableReason() {
    return t('@plugin needs to be configured before it can be used.', array('@plugin' => $this->pluginInfo['label']));
  }

  /**
   * Get settings for this Service.
   */
  protected function getOption($name, $default = NULL) {
    return variable_get('tmgmt_' . $this->pluginType . '_' . $name, $default);
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::hook_menu().
   */
  public function hook_menu() {
    $items = array();
    if (method_exists($this, 'settingsForm')) {
      $items['admin/config/regional/tmgmt/plugins/' . $this->pluginType] = array(
        'title' => $this->pluginInfo['label'],
        'description' => $this->pluginInfo['description'],
        'page callback' => 'drupal_get_form',
        'page arguments' => array('tmgmt_' . $this->pluginType . '_translator_settings_form', $this->pluginType),
        'access arguments' => array('administer tmgmt'),
      );
    }
    return $items;
  }

}
