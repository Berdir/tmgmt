<?php

/**
 * @file
 * Contains \Drupal\tmgmt\SourcePluginUiBase.
 */

namespace Drupal\tmgmt;

use Drupal\Component\Plugin\PluginBase;
use Drupal\tmgmt\Plugin\Core\Entity\JobItem;
use Drupal\tmgmt\SourcePluginUiInterface;

/**
 * Default ui controller class for source plugin.
 *
 * @ingroup tmgmt_source
 */
class SourcePluginUiBase extends PluginBase implements SourcePluginUiInterface {

  /**
   * {@inheritdoc}
   */
  public function reviewForm($form, &$form_state, JobItem $item) {
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function reviewDataItemElement($form, &$form_state, $data_item_key, $parent_key, array $data_item, JobItem $item) {
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function reviewFormValidate($form, &$form_state, JobItem $item) {
    // Nothing to do here by default.
  }

  /**
   * {@inheritdoc}
   */
  public function reviewFormSubmit($form, &$form_state, JobItem $item) {
    // Nothing to do here by default.
  }

  /**
   * {@inheritdoc}
   */
  public function overviewForm($form, &$form_state, $type) {
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function overviewFormValidate($form, &$form_state, $type) {
    // Nothing to do here by default.
  }

  /**
   * {@inheritdoc}
   */
  public function overviewFormSubmit($form, &$form_state, $type) {
    // Nothing to do here by default.
  }

  /**
   * {@inheritdoc}
   */
  public function hook_menu() {
    $items = array();
    if ($types = tmgmt_source_translatable_item_types($this->pluginId)) {
      $defaults = array(
        'file' => isset($this->pluginDefinition['file']) ? $this->pluginDefinition['file'] : $this->pluginDefinition['provider'] . '.pages.inc',
        'file path' => isset($this->pluginDefinition['file path']) ? $this->pluginDefinition['file path'] : drupal_get_path('module', $this->pluginDefinition['provider']),
        'page callback' => 'drupal_get_form',
        'access callback' => 'entity_page_create_access',
        'access arguments' => array('tmgmt_job'),
      );
      foreach ($types as $type => $name) {
        $items['admin/tmgmt/sources/' . $this->pluginId . '_' . $type] = $defaults + array(
          'title' => check_plain($name),
          'page arguments' => array('tmgmt_ui_' . $this->pluginId . '_source_' . $type . '_overview_form', $this->pluginId, $type),
          'type' => MENU_LOCAL_TASK,
        );
      }
    }
    return $items;
  }

  /**
   * {@inheritdoc}
   */
  public function hook_forms() {
    $info = array();
    if ($types = tmgmt_source_translatable_item_types($this->pluginId)) {
      foreach (array_keys($types) as $type) {
        $info['tmgmt_ui_' . $this->pluginId . '_source_' . $type . '_overview_form'] = array(
          'callback' => 'tmgmt_ui_source_overview_form',
          'wrapper_callback' => 'tmgmt_ui_source_overview_form_defaults',
        );
      }
    }
    return $info;
  }

  /**
   * {@inheritdoc}
   */
  public function hook_views_default_views() {
    return array();
  }

}
