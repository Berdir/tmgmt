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
    drupal_add_css(drupal_get_path('module', 'tmgmt_local_translator') . '/tmgmt_local_translator.css');
  // @TODO We will provide a form for each job item, arranged as vertical tabs.
  // For now this is just a collection of forms.
  $build = array();
  $build['job'] = drupal_get_form('tmgmt_local_translator_job_form', $job);
  foreach ($job->getItems() as $key => $item) {
    $build['items'][$key] = drupal_get_form('tmgmt_local_translator_job_item_form', $job, $item);
  }
  return $build;
}

/**
 * Job form for submitting to review.
 */
function tmgmt_local_translator_job_form($form, &$form_state, $job) {
  $form['job'] = array('#type' => 'value', '#value' => $job);
  $form['controls'] = array(
    '#type' => 'fieldset',
  );
  $languages = locale_language_list('name', TRUE);
  $form['controls']['source'] = array(
    '#type' => 'item',
    '#title' => t('From'),
    '#markup' => $languages[$job->source_language],
  );
  $form['controls']['target'] = array(
    '#type' => 'item',
    '#title' => t('To'),
    '#markup' => $languages[$job->target_language],
  );
  $form['controls']['state'] = array(
  	'#type' => 'select',
    '#options' => tmgmt_job_states(),
    '#default_value' => $job->getState(),
  );
  $form['controls']['submit'] = array('#type' => 'submit', '#value' => t('Submit job'));
  return $form;
}

/**
 * Job form for submitting to review.
 */
function tmgmt_local_translator_job_form_submit($form, &$form_state) {
  $job = $form_state['values']['job'];
  $job->setState($form_state['values']['state']);
  $job->save();
  drupal_set_message(t('The translation job has been submitted.'));
}

/**
 * Provide a form to translate a job item.
 */
function tmgmt_local_translator_job_item_form($form, &$form_state, $job, $item) {
  $form['job'] = array('#type' => 'value', '#value' => $job);
  $form['job_item'] = array('#type' => 'value', '#value' => $item);
  $data = $item->getSourceData();
  $form['text'] = tmgmt_local_translator_job_item_subform($data);
  $form['controls']['save'] = array('#type' => 'submit', '#value' => t('Save translation'));
  $form['#attributes'] = array('class' => array('tmgmt-job-item'));
  return $form;
}

/**
 * Build subform for translating single text
 */
function tmgmt_local_translator_job_item_subform($items) {
  $form = array();
  $form['#tree'] = TRUE;
  if (!empty($items['#label'])) {
    $form['#title'] = $items['#label'];
    $form['#type'] = 'fieldset';
  }
  if (!empty($items['#text'])) {
    // Approximate the number of rows to use in the default textarea.
    $rows = min(ceil(str_word_count($items['#text']) / 12), 10);
    
    $form['source'] = array(
      '#type' => 'textarea',
      '#disabled' => TRUE,
      '#default_value' => $items['#text'],
      '#attributes' => array('class' => array('tmgmt-source')),
      '#rows' => $rows,
    );
    $form['translation'] = array(
      '#type' => 'textarea',
      '#default_value' => '',
      '#attributes' => array('class' => array('tmgmt-translation')),
      '#rows' => $rows,
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
  $item = $form_state['values']['job_item'];
  $translation = tmgmt_local_translator_job_item_strings($item->getSourceData(), $form_state['values']['text']);
  if ($translation) {
    $item->addTranslatedData($translation);
    //$job->needsReview();
    drupal_set_message(t('Your translation has been submitted'));
  }
}

/**
 * Recursively convert submitted strings to translation-link data arrays
 */
function tmgmt_local_translator_job_item_strings($source, $values) {
  $translation = array();
  if (isset($source['#text']) && !empty($values['translation'])) {
    $translation['#text'] = $source['#text'];
  }
  foreach (element_children($source) as $key) {
    if (isset($values[$key])) {
      $translation[$key] = tmgmt_local_translator_job_item_strings($source[$key], $values[$key]);
    }
  }
  return $translation;
}