<?php


/**
 * Default ui controller class for source plugin.
 *
 * @ingroup tmgmt_source
 */
class TMGMTDefaultSourceUIController extends TMGMTPluginBase implements TMGMTSourceUIControllerInterface {

  /**
   * Implements TMGMTSourceUIControllerInterface::reviewForm().
   */
  public function reviewForm($form, &$form_state, TMGMTJobItem $item) {
    return $form;
  }

  /**
   * Implements TMGMTSourceUIControllerInterface::reviewDataItemElement().
   */
  public function reviewDataItemElement($form, &$form_state, $data_item_key, array $data_item, TMGMTJobItem $item) {
    return $form;
  }

  /**
   * Implements TMGMTSourceUIControllerInterface::reviewFormValidate().
   */
  public function reviewFormValidate($form, &$form_state, TMGMTJobItem $item) {
    // Nothing to do here by default.
  }

  /**
   * Implements TMGMTSourceUIControllerInterface::reviewFormSubmit().
   */
  public function reviewFormSubmit($form, &$form_state, TMGMTJobItem $item) {
    // Nothing to do here by default.
  }

  /**
   * Implements TMGMTSourceUIControllerInterface::overviewForm().
   */
  public function overviewForm($form, &$form_state, $type) {
    return $form;
  }

  /**
   * Implements TMGMTSourceUIControllerInterface::overviewFormValidate().
   */
  public function overviewFormValidate($form, &$form_state, $type) {
    // Nothing to do here by default.
  }

  /**
   * Implements TMGMTSourceUIControllerInterface::overviewFormSubmit().
   */
  public function overviewFormSubmit($form, &$form_state, $type) {
    // Nothing to do here by default.
  }

  /**
   * Implements TMGMTSourceUIControllerInterface::hook_menu().
   */
  public function hook_menu() {
    $items = array();
    if ($types = tmgmt_source_translatable_item_types($this->pluginType)) {
      $defaults = array(
        'file' => isset($this->pluginInfo['file']) ? $this->pluginInfo['file'] : $this->pluginInfo['module'] . '.pages.inc',
        'file path' => isset($this->pluginInfo['file path']) ? $this->pluginInfo['file path'] : drupal_get_path('module', $this->pluginInfo['module']),
        'page callback' => 'drupal_get_form',
        'access callback' => 'tmgmt_job_access',
        'access arguments' => array('create'),
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
   * Implements TMGMTSourceUIControllerInterface::hook_form().
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
   * Implements TMGMTSourceUIControllerInterface::hook_views_default_views().
   */
  public function hook_views_default_views() {
    return array();
  }

}
