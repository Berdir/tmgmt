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
    $this->pluginInfo = t9n_mgmt_source_plugin_info($plugin);
  }

}

/**
 * Interface for service plugin controllers.
 */
interface TranslationManagementServicePluginControllerInterface {

  /**
   * Constructor.
   */
  public function __construct($plugin);

}

/**
 * Default controller class for service plugins.
 */
class TranslationManagementDefaultServicePluginController implements TranslationManagementServicePluginControllerInterface {

  protected $pluginType;
  protected $pluginInfo;

  /**
   * Constructor.
   */
  public function __construct($plugin) {
    $this->pluginType = $plugin;
    $this->pluginInfo = t9n_mgmt_service_plugin_info($plugin);
  }

}
