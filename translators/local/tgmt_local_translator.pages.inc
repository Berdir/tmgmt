<?php
/**
 * @file
 * Pages for local translation.
 */

/**
 * Menu callback. Overview of pending jobs to translate.
 */
function tgmt_local_translator_page_user_translate() {
  return 'NOT IMPLEMENTED';
}

/**
 * Menu callback. Configure which languages a user can handle.
 */
function tgmt_local_translator_page_user_configure($user) {
  return 'NOT IMPLEMENTED';
}

/**
 * Menu callback. Translate actual job.
 */
function tgmt_local_translator_page_job_translate(TGMTJob $job) {
  // @TODO We will provide a form for each job item, arranged as vertical tabs.
  // For now this is just a collection of forms.
  $build = array();
  foreach ($job->getItems() as $key => $item) {
    $build[$key] = drupal_get_form('tgmt_local_translator_job_item_form', $job, $item);
  }
  return $build;
}

/**
 * Provide a form to translate a job item.
 */
function tgmt_local_translator_job_item_form($form, &$form_state, $job, $item) {
  $form['job'] = array('#type' => 'value', '#value' => $job);
  $form['job_item'] = array('#type' => 'value', '#value' => $item);
  $form['title']['#markup'] = 'NOT IMPLEMENTED';
  return $form;
}

/**
 * Provide a form to translate a job item, submit.
 */
function tgmt_local_translator_job_item_form_submit($form, &$form_state) {
  
  
}