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
  // This left column is given a width of 35% by tmgmt_ui.my-tmgmt.css
  $output['tmgmt_ui_current_user'] = array(
    '#title' => t('My Profile'),
    '#view' => 'tmgmt_ui_current_user',
    '#view_display' => 'block_1',
    '#attributes' => array('class' => array('left', 'clearfix')),
    '#theme' => 'tmgmt_ui_element',
  );
  
  // This right column is given a width of 65% by tmgmt_ui.my-tmgmt.css
  $output['tmgmt_ui_overview_top_left'] = array(
    '#title' => t('to be submitted'),
    '#view' => 'tmgmt_ui_jobs_by_state',
    '#view_display' => 'block_1',
    '#view_arguments' => array(TMGMT_JOB_STATE_PREPARED),
    '#attributes' => array('class' => array('right', 'clearfix')),
    '#theme' => 'tmgmt_ui_element',
  );

  $output['tmgmt_ui_jobs_by_state_4'] = array(
    '#title' => t('Review Pending'),
    '#view' => 'tmgmt_ui_jobs_by_state',
    '#view_display' => 'block_1',
    '#view_arguments' => array(TMGMT_JOB_STATE_REVIEW),
    '#attributes' => array('class' => array('cleared')),
    '#theme' => 'tmgmt_ui_element',
  );
  
  $output['tmgmt_ui_jobs_by_state_2'] = array(
    '#title' => t('Active Jobs'),
    '#view' => 'tmgmt_ui_jobs_by_state',
    '#view_display' => 'block_1',
    '#view_arguments' => array(TMGMT_JOB_STATE_SUBMITTED),
    '#theme' => 'tmgmt_ui_element',
  );

  $output['tmgmt_ui_jobs_by_state_5'] = array(
    '#title' => t('Recently Published'),
    '#view' => 'tmgmt_ui_jobs_by_state',
    '#view_display' => 'block_1',
    '#view_arguments' => array(TMGMT_JOB_STATE_ACCEPTED),
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
        $arguments = array();
        if (isset($output[$key]['#view_arguments'])) {
          $arguments = $output[$key]['#view_arguments'];
        }
        $output[$key]['#markup'] .= $view->preview($display_id, $arguments);
        if (!isset($output[$key]['#title']) && $title = $view->get_title()) {
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
 * @return
 *  A Render API array of content creation options.
 */
function tmgmt_ui_jobs() {
  $output = array();

  $view = views_get_view('tmgmt_ui_jobs_overview');

  $output['#title'] = $view->get_title();
  $output['#markup'] = $view->preview('block_1', array());

  return $output;
}

/**
 * Shows the untranslated elemets.
 *
 * @see hook_tmgmt_ui_untranslated_alter()
 *
 * @return
 *  A Render API array of content creation options.
 */
function tmgmt_ui_untranslated() {
  $output = array();
  $output['#markup'] = '<h3>' . t('untranslated') . '</h3><p>all untranslated elements</p>';

  // Allow other modules to add content here.
  drupal_alter('tmgmt_ui_untranslated', $output);

  return $output;
}

/**
 * Shows the pendning elements.
 *
 * @see hook_tmgmt_ui_pending_alter()
 *
 * @return
 *  A Render API array of content creation options.
 */
function tmgmt_ui_pending() {
  $output = array();
  $output['#markup'] = '<h3>' . t('pending') . '</h3><p>all pending elements</p>';

  // Allow other modules to add content here.
  drupal_alter('tmgmt_ui_pending', $output);

  return $output;
}

/**
 * Shows the node view.
 *
 * TOBE deleted or moved to admin.inc
 *
 * @return
 *  A Render API array of content creation options.
 */
function tmgmt_ui_fake_admin() {
  $output = array();
  $output['#markup'] = '<h3>' . t('Administer UI') . '</h3><p>but not yet ..</p>';

  return $output;
}

function tmgmt_job_view(TMGMTJob $job) {
  return entity_view('tmgmt_job', array($job->tjid => $job));
}