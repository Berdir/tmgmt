<?php

/**
 * @file
 * Provides page callbacks and form functions for the Translation Management
 * Tool User Interface module.
 */

/**
 * Simple page callback for viewing a job.
 *
 * @param Job $job
 *   The viewed job.
 *
 * @return array
 *   A renderable array.
 */
function tmgmt_ui_job_view(Job $job) {
  return entity_view($job->entityType(), array($job), 'full', NULL, TRUE);
}

/**
 * Simple page callback for viewing a job item.
 *
 * @param JobItem $item
 *   The viewed job item.
 *
 * @return array
 *   A renderable array.
 */
function tmgmt_ui_job_item_view(JobItem $item) {
  // Update the breadcrumb to include the job path.
  _tmgmt_ui_breadcrumb($item);
  return entity_view($item->entityType(), array($item), 'full', NULL, TRUE);
}

/**
 * Simple page callback for reviewing a job item.
 *
 * @param JobItem $job
 *   The job item to be reviewed
 *
 * @return array
 *   A renderable array.
 */
function tmgmt_ui_job_item_review(JobItem $item) {
  if ($controller = $item->getSourceController()) {
    $plugin = $controller->pluginType();
    return drupal_get_form('tmgmt_ui_' . $plugin . '_translation_review_form', $item);
  }
  drupal_access_denied();
  exit;
}

/**
 * Helper function for redirecting a form after a button has been clicked.
 */
function tmgmt_ui_submit_redirect($form, &$form_state){
  if (isset($form_state['triggering_element']['#redirect'])) {
    $form_state['redirect'] = $form_state['triggering_element']['#redirect'];
  }
}

/**
 * Ajax callback for loading the translator plugin settings form for the
 * currently selected translator plugin.
 */
function tmgmt_ui_ajax_callback_translator_plugin_select($form, &$form_state) {
  return $form['plugin_wrapper'];
}

/**
 * Ajax callback to fetch the supported translator services and rebuild the
 * target / source language dropdowns.
 */
function tmgmt_ui_ajax_callback_language_select($form, &$form_state) {
  $replace = $form_state['input']['_triggering_element_name'] == 'source_language' ? 'target_language' : 'source_language';
  $commands[] = ajax_command_replace('#tmgmt-ui-translator-wrapper', drupal_render($form['translator_wrapper']));
  $commands[] = ajax_command_replace('#tmgmt-ui-' . str_replace('_', '-', $replace), drupal_render($form['info'][$replace]));

  // Replace value of the label field with ajax on language change.
  // @todo This manual overwrite is necessary because somehow an old job entity seems to be used.
  $form['info']['label']['#value'] = $form_state['values']['label'];
  $commands[] = ajax_command_replace('#tmgmt-ui-label', drupal_render($form['info']['label']));

  return array('#type' => 'ajax', '#commands' => $commands);
}

/**
 * Ajax callback to fetch the options provided by a translator.
 */
function tmgmt_ui_ajax_callback_translator_select($form, &$form_state) {
  return $form['translator_wrapper']['settings'];
}

/**
 * Helper function for appending the parent job path to the breadcrumb of a job
 * item menu path.
 */
function _tmgmt_ui_breadcrumb(JobItem $item) {
  $breadcrumb = drupal_get_breadcrumb();
  $menu = menu_get_item('admin/tmgmt/jobs/' . $item->tjid);
  $breadcrumb[] = l($menu['title'], 'admin/tmgmt/jobs/' . $item->tjid);
  drupal_set_breadcrumb($breadcrumb);
}
