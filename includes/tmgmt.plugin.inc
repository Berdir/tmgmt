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
   * @todo: Handle failures.
   */
  public function saveTranslation(TMGMTJobItem $job_item);

  /**
   * Return a title for this job item.
   */
  public function getTitle(TMGMTJobItem $job_item);

  /**
   * Returns the URi for this job item.
   *
   * @see entity_uri().
   */
  public function getUri(TMGMTJobItem $job_item);

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
   * Check whether this service can handle this translation job.
   *
   * @param TMGMTJob $job
   *   The TMGMTJob entity that should be submitted.
   *
   * @return boolean
   *   TRUE if the job can be processed and translated, FALSE otherwise.
   */
  public function canTranslate(TMGMTJob $job);

  /**
   * @param $source_language
   * @return mixed
   */
  public function getSupportedTargetLanguages($source_language);

  /**
   * @abstract
   *
   * @param TMGMTJob $job
   *   The job that should be submitted.
   *
   * @param TMGMTJob $job
   */
  public function requestTranslation(TMGMTJob $job);

  /**
   * Cancels a translation job.
   *
   * @param TMGMTJob $job
   *   The job that should have its translation cancelled.
   */
  public function cancelTranslation(TMGMTJob $job);

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
    return TRUE;
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::canTranslate().
   */
  public function canTranslate(TMGMTJob $job) {
    return $this->isAvailable();
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::cancelTranslation().
   */
  public function cancelTranslation(TMGMTJob $job) {
    $job->cancelled('Canceled');
  }

  /**
   * Get settings for this Service.
   */
  protected function getOption($name, $default = NULL) {
    return variable_get('tmgmt_' . $this->pluginType . '_' . $name, $default);
  }

}
