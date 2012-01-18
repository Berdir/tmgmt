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
   * Check whether this service can handle this translation job.
   *
   * @param TMGMTJob $job
   *
   * @return boolean
   */
  public function canTranslate(TMGMTJob $job);

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
   * Cancel translation job
   *
   * @param TMGMTJob $job
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
   * Check whether this service can handle this translation job.
   *
   * @param TMGMTJob $job
   *
   * @return boolean
   */
  public function canTranslate(TMGMTJob $job) {
    return TRUE;
  }

  /**
   * Cancel translation job
   *
   * @param TMGMTJob $job
   */
  public function cancelTranslation(TMGMTJob $job) {
    return TMGMTTranslatorResponse(TMGMTJob::STATE_CANCELLED, t('Canceled'));
  }

  /**
   * Get setting for this Service
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

  public function __construct($code, $string) {
    $this->code = $code;
    $this->string = $string;
  }

  /**
   * Implements TMGMTranslatorResponseInterface::toString(),
   */
  function toString() {
    return $this->string;
  }

  /**
   * Implements TMGMTranslatorResponseInterface::toJobState(),
   */
  function toJobState() {
    return $this->code;
  }

}

