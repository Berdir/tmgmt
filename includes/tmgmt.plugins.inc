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
  
  /**
   * Returns an array with the data structured for translation.
   */
  public function getTranslationStructure($data);
  
  /**
   * Saves a translation.
   */
  public function saveTranslation(TMGMTJobItem $job_item);

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
  
  public abstract function getTranslationStructure($data);
  
  public abstract function saveTranslation(TMGMTJobItem $job_item);

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

/**
 * Default controller class response.
 */
class TMGMTDefaultTranslatorResponse {
  public $status;
  public $message;
  //public $job;
  
  function __construct($status, $message) {
    
  }
  
  
}
