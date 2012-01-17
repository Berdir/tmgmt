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

}
