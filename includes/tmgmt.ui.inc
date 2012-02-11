<?php

/**
 * Default ui controller class for source plugin.
 *
 * @ingroup tmgmt_source
 */
class TMGMTDefaultSourceUIController extends TMGMTPluginBase {

  /**
   * Implements hook_menu().
   */
  public function hook_menu() {
    $items = array();
    if ($types = tmgmt_source_translatable_item_types($this->pluginType)) {
      $defaults = array(
        'file' => isset($this->pluginInfo['file']) ? $this->pluginInfo['file'] : $this->pluginInfo['module'] . '.pages.inc',
        'file path' => isset($this->pluginInfo['file path']) ? $this->pluginInfo['file path'] : drupal_get_path('module', $this->pluginInfo['module']),
        'page callback' => 'drupal_get_form',
        'access arguments' => array('administer tmgmt'),
      );
      foreach ($types as $type => $name) {
        if (empty($items['admin/config/regional/tmgmt/' . $this->pluginType])) {
          // Make the first item type of this source the default menu tab.
          $items['admin/config/regional/tmgmt/' . $this->pluginType] = $defaults + array(
            'title' => t('@type sources', array('@type' => ucfirst($this->pluginInfo['label']))),
            'page arguments' => array('tmgmt_ui_' . $this->pluginType . '_source_' . $type . '_overview_form', $this->pluginType, $type),
            'type' => MENU_LOCAL_TASK,
          );
          $items['admin/config/regional/tmgmt/' . $this->pluginType . '/' . $type] = array(
            'title' => check_plain($name),
            'type' => MENU_DEFAULT_LOCAL_TASK,
          );
        }
        else {
          $items['admin/config/regional/tmgmt/' . $this->pluginType . '/' . $type] = $defaults + array(
            'title' => check_plain($name),
            'page arguments' => array('tmgmt_ui_' . $this->pluginType . '_source_' . $type . '_overview_form', $this->pluginType, $type),
            'type' => MENU_LOCAL_TASK,
          );
        }
      }
    }
    return $items;
  }

  /**
   * Implements hook_forms().
   */
  public function hook_forms() {
    $info = array();
    if ($types = tmgmt_source_translatable_item_types($this->pluginType)) {
      foreach (array_keys($types) as $type) {
        $info['tmgmt_ui_' . $this->pluginType . '_source_' . $type . '_overview_form'] = array(
          'callback' => 'tmgmt_ui_source_overview_form',
          'wrapper_callback' => 'tmgmt_ui_source_overview_form_defaults',
        );
      }
    }
    return $info;
  }

  /**
   * Implements hook_views_default_views().
   */
  public function hook_views_default_views() {
    // Load default views from the /views sub folder of the module that
    // implements this plugin.
    return _tmgmt_load_exports($this->pluginInfo['module'], 'views', 'view.inc', 'view');
  }

}

/**
 * Default ui controller class for translator plugins.
 *
 * @ingroup tmgmt_translator
 */
class TMGMTDefaultTranslatorUIController extends TMGMTPluginBase {

}
