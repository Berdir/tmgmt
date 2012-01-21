<?php

/**
 * @file
 * Please supply a file description.
 */

/**
 * The checkout form.
 */
function tmgmt_job_checkout_form($form, &$form_state, TMGMTJob $job) {
  // Get all available languages from core translation module.
  $available_languages = entity_metadata_language_list();
  // Keep the label of the source language.
  $source_language = $available_languages[$job->source_language];
  // Remove LANGUAGE_NONE and the source language from the list of available
  // languages and then apply a filter that only leaves the supported target
  // languages on the list.
  unset($available_languages[LANGUAGE_NONE], $available_languages[$job->source_language]);
  // Get the translator plugin that is currently selected.
  $translator_plugins = tmgmt_translator_availability_filter(tmgmt_translator_plugin_labels());
  $selected_translator_plugin = isset($form_state['values']['translator']) ? $form_state['values']['translator'] : key($translator_plugins);
  $available_languages = tmgmt_translator_languages_filter($selected_translator_plugin, $job->source_language, $available_languages);
  // Load the plugin info for the selected translator plugin.
  $info = tmgmt_translator_plugin_info($selected_translator_plugin);
  // Add a link for each item in this job.
  $form['links'] = array(
    '#theme' => 'links',
    '#title' => t('Items in this job.'),
  );
  foreach ($job->getItems() as $item) {
    $uri = $item->uri();
    $form['links']['#links'][$item->tjiid] = array(
      'title' => $item->label(),
      'href' => $uri['path'],
    );
  }
  $form['tjid'] = array(
    '#type' => 'hidden',
    '#value' => $job->tjid,
  );
  $form['source_language'] = array(
    '#type' => 'hidden',
    '#value' => $source_language,
  );
  $form['source_language_display'] = array(
    '#type' => 'item',
    '#title' => t('Source language'),
    '#markup' => $node_language,
  );
  $form['target_language'] = array(
    '#type' => 'select',
    '#title' => t('Target language'),
    '#description' => t('The target language'),
    '#options' => $available_languages,
    '#ajax' => array(
      'callback' => 'tmgmt_get_supported_translators',
      'wrapper' => 'tmgmt-translator-wrapper',
      'method' => 'replace',
    ),
  );
  $form['translator_wrapper'] = array(
    '#type' => 'container',
    '#prefix' => '<div id="tmgmt-translator-wrapper">',
    '#suffix' => '</div>',
  );
  if (!empty($translator_plugins)) {
    $form['translator_wrapper']['translator'] = array(
      '#type' => 'select',
      '#title' => t('Translation type'),
      '#options' => $translator_plugins,
      '#default_value' => $selected_translator_plugin,
      '#ajax' => array(
        'callback' => 'tmgmt_get_translator_options',
        'wrapper' => 'tmgmt-translator-options',
        'method' => 'replace',
      ),
    );
    $form['translator_wrapper']['options'] = array(
      '#type' => 'container',
      '#prefix' => '<div id="tmgmt-translator-options">',
      '#suffix' => '</div>',
    );
    $form['translator_wrapper']['options']['description'] = array(
      '#type' => 'item',
      '#title' => t('About this service'),
      '#markup' => $info['description'],
    );
    // Load plugin controller and check is it defining job settings form.
    // @todo need a function to get the provided languages.
    $plugin_controller = tmgmt_translator_plugin_controller($selected_translator_plugin);
    // $foo = $plugin_controller->getSupportedTargetLanguages($job->source_language);
    if (method_exists($plugin_controller, 'jobSettingsForm')) {
      $form['translator_wrapper']['options']['settings'] = $plugin_controller->jobSettingsForm($form, $form_state);
    }

    $form['translator_wrapper']['actions']['#type'] = 'actions';
    $form['translator_wrapper']['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Submit'),
      '#weight' => 50,
    );
  }
  else {
    drupal_set_message(t('You have to install a translator plugin module'), 'warning');
  }
  return $form;
}

/**
 * submit function, will create a job.
 */
function tmgmt_checkout_form_submit($form, &$form_state) {
  $values = $form_state['values'];
  // Create the job object.
  //  $job_values = array('translator' => $values['translator'], 'uid' => $GLOBALS['user']->uid);
  //  if (isset($values['options']['settings'])) {
  //    $job_values['translator_context'] = $values['options']['settings'];
  //  }
  //  $job = tmgmt_job_create($values['source_language'], $values['target_language'], $job_values);
  //  // Create a job item and add it to the job.
  //  $job->addItem(tmgmt_job_item_create('node', 'node', $values['nid']));

  $job = tmgmt_job_load($values['tjid']);
  $job->target_language = $values['target_language'];
  $job->translator = $values['translator_wrapper']['translator'];
  //  dsm($job);
  //  $job->prepared('Job has been prepared.');
  // Print a message to the screen and redirect the user.
  drupal_set_message(t('Translation job has been created.'));
}

/**
 * Ajax callback to fetch the supported translator services.
 */
function tmgmt_get_supported_translators($form, &$form_state) {
  return $form['translator_wrapper'];
}

/**
 * Ajax callback to fetch the options provided by a translator.
 */
function tmgmt_get_translator_options($form, &$form_state) {
  return $form['options'];
}