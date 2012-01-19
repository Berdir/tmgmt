<?php
/**
 * @file
 * Pages for local translation.
 */

/**
 * Menu callback. Overview of pending jobs to translate.
 */
function tmgmt_local_translator_page_user_translate() {
  return 'NOT IMPLEMENTED';
}

/**
 * Menu callback. Configure which languages a user can handle.
 */
function tmgmt_local_translator_page_user_configure($user) {
  return 'NOT IMPLEMENTED';
}

/**
 * Menu callback. Translate actual job.
 */
function tmgmt_local_translator_page_job_translate(TMGMTJob $job) {
  // @TODO We will provide a form for each job item, arranged as vertical tabs.
  // For now this is just a collection of forms.
  $build = array();
  foreach ($job->getItems() as $key => $item) {
    $build[$key] = drupal_get_form('tmgmt_local_translator_job_item_form', $job, $item);
  }
  return $build;
}

/**
 * Provide a form to translate a job item.
 */
function tmgmt_local_translator_job_item_form($form, &$form_state, $job, $item) {
  $form['job'] = array('#type' => 'value', '#value' => $job);
  $form['job_item'] = array('#type' => 'value', '#value' => $item);
  $data = $item->getSourceData();
  dpm($data);
  $form['text'] = tmgmt_local_translator_job_item_subform($data);
  return $form;
}

/**
 * Build subform for translating single text
 */
function tmgmt_local_translator_job_item_subform($items) {
  $form = array();
  $form['#type'] = 'fieldset';
  $form['#tree'] = TRUE;
  if (!empty($items['#label'])) {
    $form['#title'] = $items['#label'];
  }
  if (!empty($items['#text'])) {
    $form['source'] = array(
      '#type' => 'textarea',
      '#disabled' => TRUE,
      '#default_value' => $items['#text'],
    );
    $form['translation'] = array(
      '#type' => 'textarea',
      '#default_value' => '',
    );
  }
  if (!empty($items['#description'])) {
    $form['#description'] = $items['#description'];
  }
  foreach (element_children($items) as $key) {
    $form[$key] = tmgmt_local_translator_job_item_subform($items[$key]);
  }
  return $form;
}
/**
 * Provide a form to translate a job item, submit.
 */
function tmgmt_local_translator_job_item_form_submit($form, &$form_state) {
  
  
}