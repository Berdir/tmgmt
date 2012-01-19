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
  $output['#theme'] = 'tmgmt_ui_element';
  // This left column is given a width of 35% by workbench.myworkbench.css.
  $output['tmgmt_ui_current_user'] = array(
    '#title' => t('My Profile'),
    '#attributes' => array('class' => array('left', 'clearfix')),
    '#theme' => 'tmgmt_ui_element',
  );
  // This right column is given a width of 65% by workbench.myworkbench.css.
  $output['tmgmt_ui_jobs_overview'] = array(
    '#view' => 'tmgmt_ui_jobs_overview',
    '#view_display' => 'block_1',
    '#attributes' => array('class' => array('right', 'clearfix')),
    '#theme' => 'tmgmt_ui_element',
  );

  // Allow other modules to alter the default page.
  drupal_alter('tmgmt_ui_content', $output);

  // Transform the Views into markup.
  // @see views_embed_view()
  foreach (element_children($output) as $key) {
    if (isset($output[$key]['#view']) && $view = views_get_view($output[$key]['#view'])) {
      $output[$key] += array('#markup' => '', '#view_display' => 'default');
      $display_id = $output[$key]['#view_display'];
      if ($view->access($display_id)) {
        $output[$key]['#markup'] .= $view->preview($display_id, array());
        if ($title = $view->get_title()) {
          $output[$key]['#title'] = $title;
        }
      }
      $view->destroy();
    }
  }

  return $output;
}

/**
 * Shows the job view.
 *
 * @see hook_tmgmt_ui_jobs_alter()
 *
 * @return
 *  A Render API array of content creation options.
 */
function tmgmt_ui_jobs() {
  $output = array();
  
  $view = views_get_view('tmgmt_ui_jobs_overview');
  
  $output['#title'] = $view->get_title();
  $output['#markup'] = $view->preview('block_1', array());

  // Allow other modules to add content here.
  drupal_alter('tmgmt_ui_jobs', $output);

  return $output;
}

/**
 * Shows the node view.
 *
 * @see hook_tmgmt_ui_nodes_alter()
 *
 * @return
 *  A Render API array of content creation options.
 */
function tmgmt_ui_nodes() {
  $output = array();
  $output['#markup'] = '<h3>' . t('Hello World') . '</h3><p>nodes overview</p>';

  // Allow other modules to add content here.
  drupal_alter('tmgmt_ui_nodes', $output);

  return $output;
}