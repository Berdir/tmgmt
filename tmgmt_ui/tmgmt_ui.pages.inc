<?php

/**
 * @file
 * tmgmt page callbacks.
 */

/**
 * Page callback for the tmgmt content page.
 *
 * Note that we add Views information to the array and render
 * the Views as part of the alter hook provided here.
 *
 * @see hook_tmgmt_ui_content_alter()
 *
 * @return
 *  A Render API array of content creation options.
 */
function tmgmt_ui_content() {
  $output = array();

  // Allow other modules to add content here.
  $output['#attributes'] = array('class' => array('admin', 'my-tmgmt'));
  $output['#attached'] = array(
    'css' => array(drupal_get_path('module', 'tmgmt_ui') . '/css/tmgmt_ui.my-tmgmt.css'),
  );

  return $output;
}
