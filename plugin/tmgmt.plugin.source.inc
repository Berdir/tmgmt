<?php

/**
 * @file
 * Contains the abstract source base plugin class.
 */

/**
 * Default controller class for source plugins.
 *
 * @ingroup tmgmt_source
 */
abstract class TMGMTDefaultSourcePluginController extends TMGMTPluginBase implements TMGMTSourcePluginControllerInterface {

  /**
   * Implements TMGMTSourcePluginControllerInterface::getLabel().
   */
  public function getLabel(TMGMTJobItem $job_item) {
    return t('@plugin item unavailable (@item)', array('@plugin' => $this->pluginInfo['label'], '@item' => $job_item->item_type . ':' . $job_item->item_id));
  }

  /**
   * Implements TMGMTSourcePluginControllerInterface::getUri().
   */
  public function getUri(TMGMTJobItem $job_item) {
    return array(
      'path' => '',
      'options' => array(),
    );
  }

  /**
   * Implements TMGMTSourcePluginControllerInterface::getItemTypes().
   */
  public function getItemTypes() {
    return isset($this->pluginInfo['item types']) ? $this->pluginInfo['item types'] : array();
  }

  /**
   * Implements TMGMTSourcePluginControllerInterface::getItemTypeLabel().
   */
  public function getItemTypeLabel($type) {
    $types = $this->getItemTypes();
    if (isset($types[$type])) {
      return $types[$type];
    }
    return '';
  }

}
