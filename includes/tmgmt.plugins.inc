<?php

/**
 * @file
 * Contains the classes and interfaces for the services and source plugins.
 */

/**
 * Interface for source plugin controllers.
 */
interface TMGMTSourcePluginControllerInterface {

  /**
   * Constructor.
   */
  public function __construct($plugin);

}

/**
 * Default controller class for source plugins.
 */
class TMGMTDefaultSourcePluginController implements TMGMTSourcePluginControllerInterface {

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
   * @return boolean
   *   TRUE if the job has been successfully submitted, FALSE otherwise.
   */
  public function requestTranslation(TMGMTJob $job);

  /**
   * Cancel translation job
   *
   * @param TMGMTJob $job
   */
  public function cancelTranslation(TMGMTJob $job);
  
  /**
   * Get status for translation job
   *
   * @param TMGMTJob $job
   * 
   * @return int
   *   Job status
   */
  public function getJobStatus(TMGMTJob $job);
}

/**
 * Default controller class for service plugins.
 */
class TMGMTDefaultTranslatorPluginController implements TMGMTTranslatorPluginControllerInterface {

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
    return FALSE;
  }
  /**
   * Submit callback.
   */
  public function requestTranslation(TMGMTJob $job) {
    return TRUE;
  }

  /**
   * Cancel translation job
   *
   * @param TMGMTJob $job
   */
  public function cancelTranslation(TMGMTJob $job) {
  }
  
  /**
   * Get status for translation job
   *
   * @param TMGMTJob $job
   * 
   * @return int
   *   Job status (T.Mgmt)
   */
  public function getJobStatus(TMGMTJob $job) {
  }
  
  /**
   * Get setting for this Service
   */
  protected function getOption($name, $default = NULL) {
    return variable_get('tmgmt_' . $this->pluginType . '_' . $name, $default);
  }

}
