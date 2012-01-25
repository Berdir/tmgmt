<?php
/**
 * @file
 * Pages for local translation.
 */


/**
 * Menu callback. Configure which languages a user can handle.
 */
function tmgmt_local_translator_page_user_configure($account) {
  $language_list = locale_language_list('name', TRUE);
  $build['current'] = drupal_get_form('tmgmt_local_translator_user_languages_form', $account, $language_list);
  return $build;
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
    // Build the form with one vertical tab per display
  $build['vertical'] = array(
    '#type' => 'vertical_tabs',
  );
  foreach ($job->getItems() as $key => $item) {
    $build['items'][$key] = drupal_get_form('tmgmt_local_translator_job_item_form', $job, $item);
    //$build['items'][$key]['#title'] = t('Item @key', array('@key' => $key));
  }

  dpm($build);
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
  $translation = $item->translation;
  $form['text'] = tmgmt_local_translator_job_item_subform($data, $translation);
  $form['text']['#type'] = 'fieldset';
  $form['text']['#collapsible'] = TRUE;
  $form['text']['#collapsed'] = TRUE;
  $form['text']['#group'] = 'vertical';
  $form['text']['controls']['save'] = array('#type' => 'submit', '#value' => t('Save translation'));
  $form['#attributes'] = array('class' => array('tmgmt-job-item'));
  return $form;
}

/**
 * Build subform for translating single text
 */
function tmgmt_local_translator_job_item_subform($items, $translation) {
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
      '#default_value' => isset($translation['#text']) ? $translation['#text'] : '',
      '#attributes' => array('class' => array('tmgmt-translation')),
      '#rows' => $rows,
    );
  }
  if (!empty($items['#description'])) {
    $form['#description'] = $items['#description'];
  }
  foreach (element_children($items) as $key) {
    $form[$key] = tmgmt_local_translator_job_item_subform($items[$key], isset($translation[$key]) ? $translation[$key] : array());
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

/**
 * Form to configure which languaes user can translate
 */
function tmgmt_local_translator_user_languages_form($form, &$form_state, $account, $language_list) {
  $form['user'] = array('#type' => 'value', '#value' => $account);
  $user_list = isset($account->tmgmt_local_translator) ? $account->tmgmt_local_translator : array();
  $user_list += array('source' => array(), 'target' => array());

  $form['translate'] = array(
    '#tree' => TRUE,
    '#type' => 'fieldset',
    '#description' => t('Please define your translation capabilities telling us which languages you can understand (Source) and into which languages you can translate (Target). You can add languages one at a time or remove existing ones.'),
  );
  // Build a generic language select element that has a blank at the end if you want to remove.
  $language_blank = array('' => '');
  $language_select = array(
    '#type' => 'select',
    '#options' => $language_list + $language_blank,
  );
  // We'll build a column for each type of languages
  foreach (array('source' => t('Source languages (translate from)') , 'target' => t('Target languages (translate into)')) as $type => $type_name) {
    $form['translate'][$type] = array(
      '#type' => 'fieldset',
      '#title' => $type_name,
      '#tree' => TRUE,
    );
    // Compute available languages to be added for each category.
    $available_list = $language_list;
    foreach (array_keys($user_list[$type]) as $langcode) {
      $form['translate'][$type][$langcode] = array(
        '#type' => 'select',
        '#options' => array($langcode => $language_list[$langcode]) + array('' => t('<remove language>')),
        '#default_value' => $langcode,
      );
      unset($available_list[$langcode]);
    }
    // If there are any language left, add the option to add it (override options so blank is first)
    if ($available_list) {
      $form['translate'][$type]['new'] = array(
        '#type' => 'select',
        '#options' => array('' => t('<more languages>'))  + $available_list,
        '#default_value' => key($language_blank),
      );
    }
  }
  // To add a new target language
  $form['controls']['save'] = array('#type' => 'submit', '#value' => t('Update languages'));
  return $form;
}

/**
 * Form to configure which languaes user can translate
 */
function tmgmt_local_translator_user_languages_form_submit($form, &$form_state) {
  $account = $form_state['values']['user'];
  if (isset($form_state['values']['translate'])) {
    $user_list  = array_map('array_filter', $form_state['values']['translate']);
    $user_list = array_map('array_unique', $user_list);
    $user_list = array_map('array_flip', $user_list);
  }
  else {
    $user_list = array();
  }
  // Save account
  $account->tmgmt_local_translator = $user_list;
  user_save($account);
  drupal_set_message(t('Your translator\'s language preferences have been saved.'));
}
