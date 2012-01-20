<?php

/**
 * @file
 * API documentation file for Translation Management API.
 */

/**
 * Allows modules to alter the default Workbench landing page.
 *
 * This hook is a convenience function to be used instead of
 * hook_page_alter(). In addition to the normal Render API elements,
 * you may also specify a #view and #view_display attribute, both
 * of which are strings that indicate which View to render on the page.
 *
 * The left and right columns in this output are given widths of 35% and 65%
 * respectively by tmgmt_ui.my-tmgmt.css.
 *
 * @param $output
 *  A Render API array of content items, passed by reference.
 *
 * @see tmgmt_content()
 */
function hook_tmgmt_ui_content_alter(&$output) {
  // Replace the default "Recent Content" view with our custom View.
  $output['tmgmt_ui_recent_content']['#view'] = 'custom_view';
  $output['tmgmt_ui_recent_content']['#view_display'] = 'block_2';
}

/**
 * Allows modules to alter the jobs overview.
 *
 * @param $output
 *  A Render API array of content items, passed by reference.
 *
 * @see tmgmt_ui_jobs()
 */
function hook_tmgmt_ui_jobs_alter(&$output) {
  $output = array();
  $output['field_tmgmt_jobs_dummy'] = array(
    '#title' => t('Jobs Overview'),
    '#markup' => 'dummy overview',
    '#theme' => 'tmgmt_element',
    '#weight' => -1,
  );

  return $output;
}

/**
 * Allows modules to alter the nodes overview.
 *
 * @param $output
 *  A Render API array of content items, passed by reference.
 *
 * @see tmgmt_ui_nodes()
 */
function hook_tmgmt_ui_nodes_alter(&$output) {
  $output = array();
  $output['field_tmgmt_nodes_dummy'] = array(
    '#title' => t('Nodes Overview'),
    '#markup' => 'dummy overview',
    '#theme' => 'tmgmt_element',
    '#weight' => -1,
  );

  return $output;
}