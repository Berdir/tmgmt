<?php

/**
 * @file
 * Contains the classes and interfaces for the services and source plugins.
 */

/**
 * Interface for source plugin controllers.
 *
 * @ingroup source
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

}

/**
 * Default controller class for source plugins.
 *
 * @ingroup source
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

}

/**
 * Interface for service plugin controllers.
 *
 * @ingroup translator
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
   * Submit callback.
   *
   * @param $job TMGMTJob
   *   The TMGMTJob that should be submitted.
   *
   * @return TMGMTTranslatorResponseInterface
   *   TRUE if the job has been successfully submitted, FALSE otherwise.
   */
  public function requestTranslation(TMGMTJob $job);

  /**
   * Cancels a translation job.
   *
   * @param TMGMTJob $job
   *   The TMGMTJob entity that should have its translation cancelled.
   */
  public function cancelTranslation(TMGMTJob $job);

}

/**
 * Default controller class for service plugins.
 *
 * @ingroup translator
 */
abstract class TMGMTDefaultTranslatorPluginController implements TMGMTTranslatorPluginControllerInterface {

  protected $pluginType;
  protected $pluginInfo;

  /**
   * Constructor.
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
    return new TMGMTTranslatorResponse(TMGMTJob::STATE_CANCELLED, t('Canceled'));
  }

  /**
   * Get settings for this Service
   */
  protected function getOption($name, $default = NULL) {
    return variable_get('tmgmt_' . $this->pluginType . '_' . $name, $default);
  }

}

/**
 * Interface for handling responses communication with translation providers.
 *
 * @ingroup translator
 */
interface TMGMTTranslatorResponseInterface {

  /**
   * Translates the response of the translation provider into a message that
   * can be displayed to the user.
   *
   * @return string
   *   A message that describes the response from the translation provider.
   */
  function toString();

  /**
   * Returns a valid Translation Management job state depending on the response
   * of the translator plugin. Has to be one of the defined state constants or
   * NULL if the reponse of the provider could not be mapped to a valid job
   * state.
   *
   * @return
   *   A valid state for a TranslationManagementJob entity or NULL.
   */
  function toJobState();

}

/**
 * Interface for handling responses communication with translation providers.
 *
 * @ingroup translator
 */
class TMGMTTranslatorResponse implements TMGMTTranslatorResponseInterface {

  /**
   * @var
   */
  protected $code;

  /**
   * @var
   */
  protected $string;

  /**
   * Constructor.
   *
   * @param $code
   * @param $string
   */
  public function __construct($code, $string) {
    $this->code = $code;
    $this->string = $string;
  }

  /**
   * Implements TMGMTTranslatorResponseInterface::toString(),
   */
  function toString() {
    return $this->string;
  }

  /**
   * Implements TMGMTTranslatorResponseInterface::toJobState(),
   */
  function toJobState() {
    return $this->code;
  }

}

