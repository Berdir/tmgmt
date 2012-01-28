<?php

/**
 * @file
 * Please supply a file description.
 */

/**
 * Simple page callback for viewing a job.
 *
 * @param TMGMTJob $job
 *   The viewed job.
 *
 * @return array
 *   A renderable array.
 */
function tmgmt_ui_job_view(TMGMTJob $job) {
  return entity_view($job->entityType(), array($job), 'full', NULL, TRUE);
}

/**
 * Entity API form for the translator entity.
 */
function tmgmt_translator_form($form, &$form_state, TMGMTTranslator $translator, $op = 'edit') {
  // Check if the translator entity is completely new or not.
  $old = empty($translator->is_new) && $op != 'clone';
  // Build up the stored entity from the last (failed) form submit.
  if (!empty($form_state['values'])) {
    $translator = entity_ui_form_submit_build_entity($form, $form_state);
  }
  // We don't want the terrible long title that Entity API generates for us.
  if (in_array($op, array('import', 'add'))) {
    drupal_set_title($op == 'import' ? t('Import Translator') : t('Add Translator'), PASS_THROUGH);
  }
  // Check if the translator is currently in use.
  if ($busy = $old ? tmgmt_translator_busy($translator->name) : FALSE) {
    drupal_set_message(t("This translator is currently in use and therefore can't be modified or deleted."), 'warning');
  }
  $available = tmgmt_translator_plugin_labels();
  // If the translator plugin is not set, pick the first available plugin as the
  // default.
  $translator->plugin = empty($translator->plugin) ? key($available) : $translator->plugin;
  $form['label'] = array(
    '#type' => 'textfield',
    '#title' => t('Label'),
    '#description' => t('The label of the translator.'),
    '#default_value' => $translator->label,
    '#required' => TRUE,
    '#size' => 32,
    '#maxlength' => 64,
  );
  $form['name'] = array(
    '#type' => 'machine_name',
    '#title' => t('Machine name'),
    '#description' => t('The machine readable name of this translator. It must be unique, and it must contain only alphanumeric characters and underscores. Once created, you will not be able to change this value!'),
    '#default_value' => $translator->name,
    '#machine_name' => array(
      'exists' => 'tmgmt_translator_exists',
      'source' => array('label'),
    ),
    '#disabled' => $old,
    '#size' => 32,
    '#maxlength' => 64,
  );
  $form['description'] = array(
    '#type' => 'textarea',
    '#title' => t('Description'),
    '#description' => t('The description of the translator.'),
    '#default_value' => $translator->description,
    '#size' => 32,
    '#maxlength' => 64,
  );
  $form['plugin_wrapper'] = array(
    '#type' => 'container',
    '#prefix' => '<div id="tmgmt-plugin-wrapper">',
    '#suffix' => '</div>',
  );
  // Pull the translator plugin info if any.
  $info = !empty($translator->plugin) ? tmgmt_translator_plugin_info($translator->plugin) : array();
  $form['plugin_wrapper']['plugin'] = array(
    '#type' => 'select',
    '#title' => t('Translator plugin'),
    '#description' => isset($info['description']) ? filter_xss($info['description']) : t('The plugin that controls the translator.'),
    '#options' => $available,
    '#default_value' => $translator->plugin,
    '#required' => TRUE,
    '#disabled' => $busy,
    '#ajax' => array(
      'callback' => 'tmgmt_ui_ajax_callback_translator_plugin',
      'wrapper' => 'tmgmt-plugin-wrapper',
    ),
  );
  $form['plugin_wrapper']['settings'] = array(
    '#type' => 'fieldset',
    '#collapsible' => TRUE,
    '#tree' => TRUE,
  );
  // Add the translator plugin settings form.
  $form['plugin_wrapper']['settings'] += tmgmt_ui_plugin_settings_form($form_state, $translator, $busy);
  // Add a submit button and a cancel link to the form.
  $form['actions']['#type'] = 'actions';
  $form['actions']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save translator'),
    '#disabled' => empty($available),
  );
  $form['actions']['delete'] = array(
    '#type' => 'submit',
    '#value' => t('Delete'),
    '#submit' => array('tmgmt_ui_submit_redirect'),
    '#redirect' => 'admin/config/regional/tmgmt/translators/manage/' . $translator->name . '/delete',
    '#access' => $old,
  );
  $form['actions']['cancel'] = array(
    '#type' => 'link',
    '#title' => t('Cancel'),
    '#href' => 'admin/config/regional/tmgmt/translators',
  );
  return $form;
}

/**
 * Form validator for the translator entity form.
 */
function tmgmt_translator_form_validate($form, &$form_state) {
  // We need to clone this here so we don't change the translator entity in the
  // form state. Otherwise the form will break.
  $backup = clone $form_state[$form_state['entity_type']];
  $translator = entity_ui_form_submit_build_entity($form, $form_state);
  $form_state[$form_state['entity_type']] = $backup;
  if (empty($translator->plugin)) {
    form_set_error('plugin', t('You have to select a translator plugin.'));
  }
}

/**
 * Submit handler for the translator entity form.
 *
 * Populates and stores the submitted values for the translator entity via
 * entity_ui_form_submit_build_entity().
 *
 * @see entity_ui_form_submit_build_entity()
 */
function tmgmt_translator_form_submit($form, &$form_state) {
  entity_ui_form_submit_build_entity($form, $form_state)->save();
  drupal_set_message(t('The configuration options have been saved.'));
  $form_state['redirect'] = 'admin/config/regional/tmgmt/translators';
}

/**
 * Entity API form the job entity.
 */
function tmgmt_job_form($form, &$form_state, TMGMTJob $job, $op = 'edit') {
  // Check if the translator entity is completely new or not.
  $old = empty($job->is_new) && $op != 'clone';
  // Look up the items on the job.
  $items = $job->getItems();
  // Find pre-selected values and get all available languages.
  // @todo Make sure that our ajax callback also replaces the opposite language
  // options list so we never have the same source and target language.
  $available = tmgmt_available_languages();
  $preselected['target_language'] = !empty($job->target_language) ? $job->target_language : FALSE;
  $preselected['source_language'] = !empty($job->source_language) ? $job->source_language : FALSE;
  $preselected['translator'] = !empty($job->translator) ? $job->translator : FALSE;
  $job->target_language = $preselected['target_language'] ? $preselected['target_language'] : (isset($form_state['values']['target_language']) ? $form_state['values']['target_language'] : key($available));
  $job->source_language = $preselected['source_language'] ? $preselected['source_language'] : (isset($form_state['values']['source_language']) ? $form_state['values']['source_language'] : key($available));
  // Show a list of translators tagged by availability for the selected source
  // and target language combination
  $translators = tmgmt_translator_labels_flagged($job->source_language, $job->target_language);
  $job->translator = isset($form_state['values']['translator']) ? $form_state['values']['translator'] : key($translators);
  $job->label = isset($form_state['values']['label']) ? $form_state['values']['label'] : $job->label;
  // Mark form elements as disabled under certain circumstances.
  $disabled = !$job->isUnprocessed() && !$job->isCancelled() && !$job->isRejected();
  // Get the job metadata wrapper so we can display the current status nicely.
  $wrapper = entity_metadata_wrapper('tmgmt_job', $job);
  // Show the items that are attached to this job if there are not more than 20.
  $form['override_label'] = array(
    '#type' => 'checkbox',
    '#title' => t('Override the default label (%label)', array('%label' => $job->label())),
    '#default_value' => !empty($job->label),
  );
  $form['label'] = array(
    '#type' => 'textfield',
    '#title' => t('Label'),
    '#description' => t('You can provide a label for this job in order to identify it easily later on.'),
    '#default_value' => $job->label,
    '#states' => array(
      'visible' => array(
        ':input[name="override_label"]' => array('checked' => TRUE),
      ),
    ),
  );
  // Show the current state of the job.
  $form['state'] = array(
    '#type' => 'item',
    '#title' => t('Current status'),
    '#markup' => $wrapper->state->label(),
  );
  // Add a list of job items.
  $form['items'] = array(
    '#type' => 'fieldset',
    '#title' => format_plural(count($items), '@count attached item', '@count attached items'),
    '#collapsible' => TRUE,
    '#collapsed' => TRUE,
  );
  $form['items'] += tmgmt_ui_job_list_items($items);
  // Show the source and target language in a fieldset.
  $form['languages'] = array(
    '#type' => 'fieldset',
    '#title' => t('Language'),
  );
  // Make the source and target language flexible by showing either a select
  // dropdown or the plain string (if preselected).
  $form['languages']['source_language'] = array(
    '#title' => t('From'),
    '#type' => 'select',
    '#options' => $available,
    '#default_value' => $job->source_language,
    '#required' => TRUE,
    '#prefix' => '<div class="tmgmt-ui-source-language">',
    '#suffix' => '</div>',
    '#disabled' => !empty($preselected['source_language']) || $disabled,
    '#ajax' => array(
      'callback' => 'tmgmt_ui_ajax_callback_get_supported_translators',
      'wrapper' => 'tmgmt-translator-wrapper',
    ),
  );
  $form['languages']['target_language'] = array(
    '#title' => t('To'),
    '#type' => 'select',
    '#options' => $available,
    '#default_value' => $job->target_language,
    '#required' => TRUE,
    '#prefix' => '<div class="tmgmt-ui-source-language">',
    '#suffix' => '</div>',
    '#disabled' => !empty($preselected['target_language']) || $disabled,
    '#ajax' => array(
      'callback' => 'tmgmt_ui_ajax_callback_supported_translators',
      'wrapper' => 'tmgmt-translator-wrapper',
    ),
  );
  $form['translator_wrapper'] = array(
    '#type' => 'container',
    '#prefix' => '<div id="tmgmt-translator-wrapper">',
    '#suffix' => '</div>',
  );
  $form['translator_wrapper']['translator'] = array(
    '#type' => 'select',
    '#title' => t('Translator'),
    '#options' => $translators,
    '#default_value' => $job->translator,
    '#required' => TRUE,
    '#disabled' => $disabled,
    '#ajax' => array(
      'callback' => 'tmgmt_ui_ajax_callback_translator_settings',
      'wrapper' => 'tmgmt-translator-settings',
    ),
  );
  // Display the checkout settings form if the job can be checked out.
  if (!$job->isActive() && !$job->isAccepted() && !$job->isNeedsReview()) {
    $form['translator_wrapper']['settings'] = array(
      '#type' => 'fieldset',
      '#title' => t('Checkout settings'),
      '#prefix' => '<div id="tmgmt-translator-settings">',
      '#suffix' => '</div>',
      '#tree' => TRUE,
    );
    $form['translator_wrapper']['settings'] += tmgmt_ui_checkout_settings_form($form_state, $job);
  }
  // Otherwise display the checkout info form.
  else {
    $form['translator_wrapper']['info'] = array(
      '#type' => 'fieldset',
      '#title' => t('Checkout information'),
    );
    $form['translator_wrapper']['info'] += $job->getInfo();
  }
  // Add the buttons and action links.
  $form['actions']['#type'] = 'actions';
  $form['actions']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save job'),
  );
  $form['actions']['checkout'] = array(
    '#type' => 'submit',
    '#value' => t('Submit to translator'),
    '#access' => !$job->isActive() && !$job->isAccepted() && !$job->isNeedsReview(),
  );
  if ($old) {
    $form['actions']['delete'] = array(
      '#type' => 'submit',
      '#value' => t('Delete'),
      '#submit' => array('tmgmt_ui_submit_redirect'),
      '#redirect' => 'admin/config/regional/tmgmt/jobs/' . $job->tjid . '/delete',
    );
  }
  // Only show the 'Cancel' button if the job has been submitted to the
  // translator.
  $form['actions']['cancel'] = array(
    '#type' => 'button',
    '#value' => t('Cancel'),
    '#submit' => array('tmgmt_ui_submit_redirect'),
    '#redirect' => 'admin/config/regional/tmgmt/jobs',
    '#access' => $job->isActive(),
  );
  // We need some CSS for showing the source and target language in one row.
  $form['#attached']['css'][] = drupal_get_path('module', 'tmgmt_ui') . '/css/tmgmt_ui.admin.css';
  return $form;
}

/**
 * Submit function, will set a job to request translation status.
 */
function tmgmt_job_form_submit($form, &$form_state) {
  // Build up the job from the submitted form values and save here so we got the
  // plain 'Submit' case covered. Also, if the translator fails it won't save on
  // its own.
  $job = entity_ui_form_submit_build_entity($form, $form_state);
  if (!$form_state['values']['override_label']) {
    // Make sure the job label is empty if the override checkbox isn't checked.
    $job->label = '';
  }
  $job->save();
  // Per default we want to redirect the user to the overview.
  $form_state['redirect'] = 'admin/config/regional/tmgmt';
  // Everything below this line is only invoked if the 'Submit to translator'
  // button was clicked.
  if ($form_state['triggering_element']['#value'] == t('Submit to translator')) {
    if (!tmgmt_ui_job_request_translation($job)) {
      // Don't redirect the user if the translation request failed but retain
      // existing destination parameters so we can redirect once the request
      // finished successfully.
      $form_state['redirect'] = array(current_path(), array('query' => drupal_get_destination()));
      unset($_GET['destination']);
    }
  }
}

/**
 * Validates the checkout form.
 */
function tmgmt_job_form_validate($form, &$form_state) {
  // We need to clone this here so we don't change the job entity in the form
  // state. Otherwise the form will break.
  $backup = clone $form_state[$form_state['entity_type']];
  $job = entity_ui_form_submit_build_entity($form, $form_state);
  $form_state[$form_state['entity_type']] = $backup;
  // Load the selected translator.
  $translator = tmgmt_translator_load($job->translator);
  // Load the metadata wrapper so we can display the selected language.
  $wrapper = entity_metadata_wrapper('tmgmt_job', $job);
  // Check translator availability.
  if (!$translator->isAvailable()) {
    form_set_error('translator', $translator->getNotAvailableReason());
  }
  elseif (!array_key_exists($job->target_language, $translator->getSupportedTargetLanguages($job->source_language))) {
    form_set_error('translator', t('@translator can not translate from @source to @target.', array('@translator' => $translator->label(), '@source' => $wrapper->source_language->label(), '@target' => $wrapper->target_language->label())));
  }
  if (!$job->isTranslatable()) {
    // @todo Add a generic error message.
    form_set_error('translator', t('Something went wrong.'));
  }
}

/**
 * Helper function for retrieving the job settings form.
 */
function tmgmt_ui_checkout_settings_form(&$form_state, TMGMTJob $job) {
  $translator = $job->getTranslator();
  if (!$translator) {
    return array(
      '#description' => t('No translator available.'),
    );
  }
  if (!$translator->isAvailable()) {
    return array(
      '#description' => filter_xss($job->getTranslator()->getNotAvailableReason()),
    );
  }
  elseif (!array_key_exists($job->target_language, $translator->getSupportedTargetLanguages($job->source_language))) {
    $wrapper = entity_metadata_wrapper('tmgmt_job', $job);
    return array(
      '#description' => t('@translator can not translate from @source to @target.', array('@translator' => $job->getTranslator()->label(), '@source' => $wrapper->source_language->label(), '@target' => $wrapper->target_language->label())),
    );
  }
  if ($settings = $job->checkoutSettingsForm(array(), $form_state)) {
    return $settings;
  }
  return array();
}

/**
 * Helper function for retrieving the translator settings form.
 */
function tmgmt_ui_plugin_settings_form(&$form_state, TMGMTTranslator $translator, $busy = FALSE) {
  if ($settings = $translator->pluginSettingsForm(array(), $form_state, $busy)) {
    return $settings;
  }
  return array();
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
 * Helper function for listing a set of job items.
 */
function tmgmt_ui_job_list_items($items) {
  $links['#theme'] = 'links';
  foreach ($items as $item) {
    $uri = $item->uri();
    $links['#links'][$item->tjiid] = array(
      'title' => $item->label(),
      'href' => $uri['path'],
    );
  }
  return $links;
}

/**
 * Ajax callback for loading the translator plugin settings form for the
 * currently selected translator plugin.
 */
function tmgmt_ui_ajax_callback_translator_plugin($form, &$form_state) {
  return $form['plugin_wrapper'];
}

/**
 * Ajax callback to fetch the supported translator services.
 */
function tmgmt_ui_ajax_callback_supported_translators($form, &$form_state) {
  return $form['translator_wrapper'];
}

/**
 * Ajax callback to fetch the options provided by a translator.
 */
function tmgmt_ui_ajax_callback_translator_settings($form, &$form_state) {
  return $form['translator_wrapper']['settings'];
}
