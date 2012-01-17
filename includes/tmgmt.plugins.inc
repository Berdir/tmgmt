<?php

/**
 * @file
 * Contains the classes and interfaces for the services and source plugins.
 */

/**
 * Interface for source plugin controllers.
 */
interface TranslationManagementSourcePluginControllerInterface {

  /**
   * Constructor.
   */
  public function __construct($plugin);

}

/**
 * Default controller class for source plugins.
 */
class TranslationManagementDefaultSourcePluginController implements TranslationManagementSourcePluginControllerInterface {

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
interface TranslationManagementTranslatorPluginControllerInterface {

  /**
   * Constructor.
   */
  public function __construct($plugin);

  /**
   * Submit callback.
   *
   * @param $job TranslationManagementJob
   *   The TranslationManagementJob that should be submitted.
   *
   * @return boolean
   *   TRUE if the job has been successfully submitted, FALSE otherwise.
   */
  public function submit(TranslationManagementJob $job);

  /**
   * Receive callback.
   *
   * @param $object
   *   The processed object that has been returned by the internal or external
   *   translation service.
   */
  public function receive($object);

}

/**
 * Default controller class for service plugins.
 */
class TranslationManagementDefaultTranslatorPluginController implements TranslationManagementTranslatorPluginControllerInterface {

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
   * Submit callback.
   */
  public function submit(TranslationManagementJob $job) {
    return TRUE;
  }

  /**
   * Receive callback.
   */
  public function receive($object) {
    
  }

}
