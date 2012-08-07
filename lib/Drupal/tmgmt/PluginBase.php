<?php

/**
 * @file
 * Contains Drupal\tmgmt\PluginBase.
 */

namespace Drupal\tmgmt;

/**
 * Base class for Translation Management plugins.
 */
class PluginBase implements PluginBaseInterface {

  protected $pluginType;
  protected $pluginInfo;

  /**
   * Implements TMGMTSourcePluginControllerInterface::__construct().
   */
  public function __construct($type, $plugin) {
    $this->pluginType = $plugin;
    $this->pluginInfo = _tmgmt_plugin_info($type, $plugin);
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
