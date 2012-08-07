<?php

/**
 * @file
 * Contains Drupal\tmgmt\DefaultSourcePluginController.
 */

namespace Drupal\tmgmt;

use Drupal\tmgmt\Plugin\Core\Entity\JobItem;
use Drupal\tmgmt\PluginBase;
use Drupal\tmgmt\SourcePluginControllerInterface;


/**
 * Default controller class for source plugins.
 *
 * @ingroup tmgmt_source
 */
abstract class DefaultSourcePluginController extends PluginBase implements SourcePluginControllerInterface {

  /**
   * Implements SourcePluginControllerInterface::getLabel().
   */
  public function getLabel(JobItem $job_item) {
    return t('@plugin item unavailable (@item)', array('@plugin' => $this->pluginInfo['label'], '@item' => $job_item->item_type . ':' . $job_item->item_id));
  }

  /**
   * Implements SourcePluginControllerInterface::getUri().
   */
  public function getUri(JobItem $job_item) {
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