<?php

/**
 * @file
 * Provides the node source ui controller.
 */

class TMGMTNodeSourceUIController extends TMGMTDefaultSourceUIController {

  /**
   * Overrides TMGMTDefaultSourceUIController::hook_menu().
   */
  public function hook_menu() {
    // The node source overview is a View using Views Bulk Operations. Therefore
    // we don't need to provide any menu items.
    return array();
  }

  /**
   * Overrides TMGMTDefaultSourceUIController::hook_form().
   */
  public function hook_forms() {
    // The node source overview is a View using Views Bulk Operations. Therefore
    // we don't need to provide any forms.
    return array();
  }

}
