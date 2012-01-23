<?php

/**
 * @file
 * Please supply a file description.
 */

/**
 * Entity API form for the translator entity.
 */
function tmgmt_translator_form($form, &$form_state, TMGMTTranslator $translator, $op = 'edit') {
  // We don't want the terrible long title that Entity API generates for us.
  if (in_array($op, array('import', 'add'))) {
    drupal_set_title($op == 'import' ? t('Add Translator') : t('Import Translator'), PASS_THROUGH);
  }
  $available_plugins = tmgmt_translator_plugin_labels();
  // Initialize some of the default values.
  foreach (array('description', 'label', 'name', 'translator_plugin') as $key) {
    $translator->{$key} = isset($form_state['values'][$key]) ? $form_state['values'][$key] : $translator->{$key};
  }
  // If the translator plugin is not set, pick the first available plugin as the
  // default.
  $translator->translator_plugin = empty($translator->translator_plugin) ? key($available_plugins) : $translator->translator_plugin;
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
    '#disabled' => empty($translator->is_new) && $op != 'clone',
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
  if (!empty($available_plugins)) {
    $controller = tmgmt_translator_plugin_controller($translator->translator_plugin);
    $info = $controller->pluginInfo();
    $element = &$form['translator_plugin_wrapper'];
    $element = array(
      '#type' => 'container',
      '#prefix' => '<div id="tmgmt-translator-plugin-wrapper">',
      '#suffix' => '</div>',
    );
    if (!empty($translator->is_new)) {
      $element['translator_plugin'] = array(
        '#type' => 'select',
        '#title' => t('Translator plugin'),
        '#description' => check_plain($info['description']),
        '#options' => $available_plugins,
        '#default_value' => $translator->translator_plugin,
        '#required' => TRUE,
        '#ajax' => array(
          'callback' => 'tmgmt_ui_ajax_callback_translator_plugin',
          'wrapper' => 'tmgmt-translator-plugin-wrapper',
        ),
      );
    }
    else {
      $element['translator_plugin'] = array(
        '#type' => 'item',
        '#title' => t('Translator plugin'),
        '#description' => check_plain($info['label'] . ' - ' . $info['description']),
        '#required' => TRUE,
      );
    }
    $element = &$element['translator_plugin_settings'];
    $element = array(
      '#type' => 'fieldset',
      '#title' => t('@plugin plugin settings', array('@plugin' => $info['label'])),
      '#collapsible' => TRUE,
    );
    if ($settings = $controller->translatorSettingsForm(array(), $form_state, $translator)) {
      $element['#description'] = t('These settings apply to the @plugin plugin when this translator is used.', array('@plugin' => $info['label']));
      $element['settings'] = $settings;
      $element['settings']['#tree'] = TRUE;
    }
    else {
      $element['#description'] = t("The @plugin plugin doesn't provide any settings.", array('@plugin' => $info['label']));
    }
  }
  else {
    // Fallback for when no translator plugins are available.
    $form['translator_plugin'] = array(
      '#type' => 'item',
      '#title' => t('Translator plugin'),
      '#description' => t('There are no available translator plugins.'),
      '#required' => TRUE,
    );
  }
  $form['actions']['#type'] = 'actions';
  $form['actions']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save translator'),
    '#disabled' => empty($available_plugins),
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
  $backup = clone $form_state['tmgmt_translator'];
  $translator = entity_ui_form_submit_build_entity($form, $form_state);
  $form_state['tmgmt_translator'] = $backup;
  if (empty($translator->translator_plugin)) {
    form_set_error('translator_plugin', t('You have to select a translator plugin.'));
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
function tmgmt_job_form($form, &$form_state, TMGMTJob $job) {
  // Look up the items on the job.
  $items = $job->getItems();
  // Add a nice title that describes the form.
  drupal_set_title(format_plural(count($items), 'Translation job including @count translatable item', 'Translation job including @count translatable items'));
  // Find pre-selected values and get all available languages.
  // @todo Make sure that our ajax callback also replaces the opposite language
  // options list so we never have the same source and target language.
  $available = tmgmt_available_languages();
  $preselected['target_language'] = !empty($job->target_language) ? $job->target_language : FALSE;
  $preselected['source_language'] = !empty($job->source_language) ? $job->source_language : FALSE;
  $preselected['translator'] = !empty($job->translator) ? $job->translator : FALSE;
  // Show a list of translators tagged by availability for the selected source
  // and target language combination
  $translators = tmgmt_translator_filtered_by_language($job->source_language, $job->target_language);
  $job->translator = isset($form_state['values']['translator']) ? $form_state['values']['translator'] : key($translators);
  $job->target_language = $preselected['target_language'] ? $preselected['target_language'] : (isset($form_state['values']['target_language']) ? $form_state['values']['target_language'] : key($available));
  $job->source_language = $preselected['source_language'] ? $preselected['source_language'] : (isset($form_state['values']['source_language']) ? $form_state['values']['source_language'] : key($available));
  // Show the items that are attached to this job if there are not more than 20.
  $form['label'] = array(
    '#type' => 'textfield',
    '#title' => t('Label'),
    '#description' => t('You can provide a label for this job in order to identify it easily later on.'),
  );
  if (count($items) <= 20) {
    $form['items'] = array(
      '#type' => 'fieldset',
      '#title' => t('Attached source objects'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
    );
    $form['items']['links']['#theme'] = 'links';
    foreach ($job->getItems() as $item) {
      $uri = $item->uri();
      $form['items']['links']['#links'][$item->tjiid] = array(
        'title' => $item->label(),
        'href' => $uri['path'],
      );
    }
  }
  // Show the source and target language in a fieldset.
  $form['languages'] = array(
    '#type' => 'fieldset',
    '#title' => t('Source and target language'),
  );
  // Make the source and target language flexible by showing either a select
  // dropdown or the plain string (if preselected).
  $form['languages']['source_language'] = array(
    '#title' => t('Source language'),
    '#required' => TRUE,
    '#prefix' => '<div class="tmgmt-ui-source-language">',
    '#suffix' => '</div>',
  );
  if (!empty($preselected['source_language'])) {
    $form['languages']['source_language'] += array(
      '#type' => 'item',
      '#markup' => tmgmt_language_label($preselected['source_language']),
    );
  }
  else {
    $form['languages']['source_language'] += array(
      '#type' => 'select',
      '#options' => $available,
      '#default_value' => $job->source_language,
      '#ajax' => array(
        'callback' => 'tmgmt_ui_ajax_callback_get_supported_translators',
        'wrapper' => 'tmgmt-translator-wrapper',
      ),
    );
  }
  $form['languages']['target_language'] = array(
    '#title' => t('Source language'),
    '#required' => TRUE,
    '#prefix' => '<div class="tmgmt-ui-source-language">',
    '#suffix' => '</div>',
  );
  if (!empty($preselected['target_language'])) {
    $form['languages']['target_language'] += array(
      '#type' => 'item',
      '#markup' => tmgmt_language_label($preselected['target_language']),
    );
  }
  else {
    $form['languages']['target_language'] += array(
      '#type' => 'select',
      '#options' => $available,
      '#default_value' => $job->target_language,
      '#ajax' => array(
        'callback' => 'tmgmt_ui_ajax_callback_supported_translators',
        'wrapper' => 'tmgmt-translator-wrapper',
      ),
    );
  }
  $form['translator_wrapper'] = array(
    '#type' => 'container',
    '#prefix' => '<div id="tmgmt-translator-wrapper">',
    '#suffix' => '</div>',
  );
  if (!empty($translators)) {
    $form['translator_wrapper']['translator'] = array(
      '#type' => 'select',
      '#title' => t('Translator'),
      '#options' => $translators,
      '#default_value' => $job->translator,
      '#required' => TRUE,
      '#ajax' => array(
        'callback' => 'tmgmt_ui_ajax_callback_translator_settings',
        'wrapper' => 'tmgmt-translator-settings',
      ),
    );
    $form['translator_wrapper']['settings'] = array(
      '#type' => 'fieldset',
      '#title' => t('Translator checkout settings'),
      '#prefix' => '<div id="tmgmt-translator-settings">',
      '#suffix' => '</div>',
      '#tree' => TRUE,
    );
    if ($translator = tmgmt_translator_load($job->translator)) {
      switch (tmgmt_translator_check($translator, $job->source_language, $job->target_language)) {
        case TMTGT_TRANSLATOR_NOT_AVAILABLE:
          $form['translator_wrapper']['settings']['#markup'] = t('The selected translator is not available: @reason.', array('@reason' => $translator->getNotAvailableReason()));
          break;
        case TMGMT_TRANSLATOR_NOT_AVAILABLE_FOR_LANGUAGE:
          $form['translator_wrapper']['settings']['#markup'] = t('The selected translator is not available for translations from @source.', array('@source' => tmgmt_language_label($job->target_language)));
          break;
        default:
          if ($settings = $translator->jobSettingsForm(array(), $form_state, $job)) {
            $form['translator_wrapper']['settings']['settings'] = $settings;
          }
          else {
            $form['#description'] = t('The @translator translator does not provide any per job settings.', array('@translator' => $translator->identifier()));
          }
      }
    }
  }
  else {
    $form['translator_wrapper']['settings'] = array(
      '#markup' => t('None of the available translators supports the selected source and target language combination.'),
    );
  }
  $form['translator_wrapper']['actions']['#type'] = 'actions';
  $form['translator_wrapper']['actions']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Submit'),
  );
  $form['translator_wrapper']['actions']['checkout'] = array(
    '#type' => 'submit',
    '#value' => t('Checkout job'),
  );
  if (!$job->isSubmitted()) {
    $form['translator_wrapper']['actions']['delete'] = array(
      '#type' => 'link',
      '#title' => t('Delete'),
      '#href' => 'admin/config/regional/tmgmt/jobs/' . $job->tjid . '/delete',
    );
  }
  else {
    $form['translator_wrapper']['actions']['cancel'] = array(
      '#type' => 'link',
      '#title' => t('Cancel'),
      '#href' => 'admin/config/regional/tmgmt/jobs/' . $job->tjid . '/cancel',
    );
  }
  // We need some CSS for showing the source and target language in one row.
  $form['#attached']['css'][] = drupal_get_path('module', 'tmgmt_ui') . '/css/tmgmt_ui.admin.css';
  return $form;
}

/**
 * Submit function, will set a job to request translation status.
 */
function tmgmt_job_form_submit($form, &$form_state) {
  // Per default we want to redirect the user to the overview.
  $form_state['redirect'] = 'admin/config/regional/tmgmt';
  // Build up the job from the submitted form values.
  $job = entity_ui_form_submit_build_entity($form, $form_state);
  // Save here so we got the plain 'Submit' case covered. Also, if the
  // translator fails it won't save on its own.
  $job->save();
  // Everything below this line is only invoked if the 'Checkout job' button
  // was clicked.
  if ($form_state['triggering_element']['#value'] == t('Checkout job')) {
    // Request the job to be translated.
    $job->requestTranslation();
    // Print all messages to the screen and redirect the user.
    foreach ($job->getMessagesSince(REQUEST_TIME) as $message) {
      // Ignore debug messages.
      if ($message->type == 'debug') {
        continue;
      }
      if ($message->type == 'error') {
        // There was an error, so redirect the user to the job. This is the
        // default behavior, so we just need to clear the previously set redirect.
        $form_state['redirect'] = NULL;
      }
      drupal_set_message(filter_xss($message->getMessage()), $message->type);
    }
  }
}

/**
 * Validates the checkout form.
 */
function tmgmt_job_form_validate($form, &$form_state) {
  // We need to clone this here so we don't change the job entity in the form
  // state. Otherwise the form will break.
  $backup = clone $form_state['tmgmt_job'];
  $job = entity_ui_form_submit_build_entity($form, $form_state);
  $form_state['tmgmt_job'] = $backup;
  // Load the selected translator.
  $translator = tmgmt_translator_load($job->translator);
  // Load the metadata wrapper so we can display the selected language
  $wrapper = entity_metadata_wrapper('tmgmt_job', $job);
  // @todo Also make this a little bit nicer... The constants look wonky.
  switch (tmgmt_translator_check($translator, $job->source_language, $job->target_language)) {
    case TMTGT_TRANSLATOR_NOT_AVAILABLE:
      form_set_error('translator', t('Translator is not available: @reason.', array('@reason' => $translator->getNotAvailableReason())));
      break;
    case TMGMT_TRANSLATOR_NOT_AVAILABLE_FOR_LANGUAGE:
      form_set_error('translator', t('Test selected translator can not translate from @source to @language.', array('@source' => $wrapper->source_language->label(), '@target' => $wrapper->target_language->label())));
      break;
  }
  if (!$job->isTranslatable()) {
    // @todo Add a generic error message.
    form_set_error('translator', t('Something went wrong.'));
  }
}

/**
 * Ajax callback for loading the translator plugin settings form for the
 * currently selected translator plugin.
 */
function tmgmt_ui_ajax_callback_translator_plugin($form, &$form_state) {
  return $form['translator_plugin_wrapper'];
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
