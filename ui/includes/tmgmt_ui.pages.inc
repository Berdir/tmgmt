<?php

/**
 * @file
 * tmgmt page callbacks.
 */

/**
 * Creates and adds a jobitem to a certain job. If job is not defined, a new job
 * will be created. Destinatoin will be needed to return to the submitters page.
 */
function tmgmt_job_add_item($source_language, $plugin, $item_type, $item_ids, $tjid = NULL) {
  if (!$tjid) {
    $job = tmgmt_job_create($source_language, '');
  }
  else {
    $job = tmgmt_job_load($tjid);
    if (!$job) {
      return MENU_NOT_FOUND;
    }
  }
  if ($source_language != $job->source_language) {
    drupal_set_message(t("The source language of your item doesn't match the source language of your job."), 'error');
  }
  else {
    $item_ids = explode(',', $item_ids);
    foreach ($item_ids as $item_id) {
      $job->addItem($plugin, $item_type, $item_id);
    }
  }
  drupal_goto('admin/config/regional/tmgmt/jobs/' . $job->tjid . '/manage');
}

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
    // @fixme: I know it's evil and ugly but it works.
    // Embedding views admin css file for 6 lines of css.
    'css' => array(drupal_get_path('module', 'tmgmt_ui') . '/css/tmgmt_ui.admin.css'),
  );

  $view_output = tmgmt_ui_embed_view('tmgmt_ui_reviews_pending');
  if (!empty($view_output)) {
    $output['review_pending'] = array(
      '#type' => 'item',
      '#title' => t('Reviews pending'),
      '#markup' => $view_output,
    );
  }

  $view_output = tmgmt_ui_embed_view('tmgmt_ui_new_jobs');
  if (!empty($view_output)) {
    $output['new_jobs'] = array(
      '#type' => 'item',
      '#title' => t('New jobs'),
      '#markup' => $view_output,
      '#theme_wrappers' => array('form_element', 'container'),
      '#attributes' => array('class' => array('tmgmt_ui_left_48')),
    );
  }

  $view_output = tmgmt_ui_embed_view('tmgmt_ui_recently_published');
  if (!empty($view_output)) {
    $output['recently_published'] = array(
      '#type' => 'item',
      '#title' => t('Recently published'),
      '#markup' => $view_output,
      '#theme_wrappers' => array('form_element', 'container'),
      '#attributes' => array('class' => array('tmgmt_ui_right_48')),
    );
  }

  return $output;
}

/**
 * Embed a view but don't render it if it's empty.
 */
function tmgmt_ui_embed_view($view) {
  $view = views_get_view($view);
  if (!empty($view)) {
    $view->init_display();
    $output = $view->preview();

    if (!empty($view->result)) {
      return $output;
    }
  }
  return '';
}
