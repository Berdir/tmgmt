<?php

/**
 * @file
 * tmgmt page callbacks.
 */

/**
 * Creates and adds a jobitem to a certain job. If job is not defined, a new job
 * will be created. Destinatoin will be needed to return to the submitters page.
 */
function tmgmt_job_add_item($source_language, $source_plugin, $item_type, $item_ids, $tjid = NULL) {
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
      $job->addItem($source_plugin, $item_type, $item_id);
    }
  }
  drupal_goto('admin/config/regional/tmgmt/jobs/' . $job->tjid . '/checkout');
}

/**
 * The checkout form.
 */
function tmgmt_job_checkout_form($form, &$form_state, TMGMTJob $job) {
  // Add the job to the form state so we can use it in the submit handlers.
  $form_state['job'] = $job;
  // Look up the items on the job.
  $job_items = $job->getItems();
  $job_items_count = count($job_items);
  // Add a nice title that describes the form.
  drupal_set_title(format_plural($job_items_count, 'Translation job checkout for @count translatable item', 'Translation job checkout for @count translatable items'));
  // Get all available languages from core translation module.
  $available_languages = tmgmt_available_languages(array($job->source_language));
  if (isset($form_state['values']['target_language'])) {
    $selected_target_language = $form_state['values']['target_language'];
  }
  elseif (!empty($job->target_language)) {
    $selected_target_language = $job->target_language;
  }
  else {
    $selected_target_language = key($available_languages);
  }
  // Get the translator plugin that is currently selected.
  $translators = tmgmt_translator_filtered_by_language($job->source_language, $selected_target_language);
  $selected_translator_name = isset($form_state['values']['translator']) ? $form_state['values']['translator'] : key($translators);
  $selected_translator = tmgmt_translator_load($selected_translator_name);
  // Show the items that are attached to this job if there are not more than 20.
  if ($job_items_count <= 20) {
    $form['job_items'] = array(
      '#type' => 'fieldset',
      '#title' => t('Attached source objects'),
      '#collapsible' => TRUE,
      '#collapsed' => TRUE,
    );
    $form['job_items']['links']['#theme'] = 'links';
    foreach ($job->getItems() as $job_item) {
      $uri = $job_item->uri();
      $form['job_items']['links']['#links'][$job_item->tjiid] = array(
        'title' => $job_item->label(),
        'href' => $uri['path'],
      );
    }
  }
  // Show the source and target language in a fieldset.
  $form['languages'] = array(
    '#type' => 'fieldset',
    '#title' => t('Source and target language'),
  );
  foreach (array('source', 'target') as $item) {
    $key = $item . '_language';
    if (!empty($job->{$key})) {
      $form['languages'][$key] = array(
        '#type' => 'item',
        '#title' => t(ucfirst($item) . ' language'),
        '#markup' => tmgmt_language_label($job->{$key}),
        '#required' => TRUE,
        '#prefix' => '<div class="tmgmt-ui-' . $item . '-language">',
        '#suffix' => '</div>',
      );
    }
    else {
      $form['languages'][$key] = array(
        '#type' => 'select',
        '#title' => t(ucfirst($item) . ' language'),
        '#options' => $available_languages,
        '#required' => TRUE,
        '#ajax' => array(
          'callback' => 'tmgmt_ajax_callback_get_supported_translators',
          'wrapper' => 'tmgmt-translator-wrapper',
        ),
        '#attributes' => array('class' => array('tmgmt-ui-' . $item . '-language')),
      );
    }
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
      '#default_value' => $selected_translator_name,
      '#required' => TRUE,
      '#ajax' => array(
        'callback' => 'tmgmt_ajax_callback_get_translator_options',
        'wrapper' => 'tmgmt-translator-options',
      ),
    );
    $form['translator_wrapper']['options'] = array(
      '#type' => 'fieldset',
      '#title' => t('Translator checkout settings'),
      '#prefix' => '<div id="tmgmt-translator-options">',
      '#suffix' => '</div>',
    );
    switch (tmgmt_translator_check($selected_translator, $job->source_language, $selected_target_language)) {
      case TMTGT_TRANSLATOR_NOT_AVAILABLE:
        $form['translator_wrapper']['options']['settings']['#markup'] = t('Translator is not available: @reason.', array('@reason' => $selected_translator->getNotAvailableReason()));
        break;
      case TMGMT_TRANSLATOR_NOT_AVAILABLE_FOR_LANGUAGE:
        $form['translator_wrapper']['options']['settings']['#markup'] = t('@plugin_label is not available for @language.', array('@language' => tmgmt_language_label($selected_target_language), '@plugin_label' => $selected_translator->label));
        break;
      default:
        if ($settings = $selected_translator->jobSettingsForm(array(), $form_state, $job)) {
          $form['translator_wrapper']['options']['settings'] = $settings;
          $form['translator_wrapper']['options']['settings']['#tree'] = TRUE;
        }
        else {
          $form['translator_wrapper']['options']['#description'] = t('The @translator translator does not provide any per job settings.', array('@translator' => $selected_translator->identifier()));
        }
    }
  }
  else {
    $form['translator_wrapper']['translator'] = array(
      '#markup' => t('None of the available translators supports the selected source and target language combination.'),
    );
  }
  $form['translator_wrapper']['actions']['#type'] = 'actions';
  $form['translator_wrapper']['actions']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Checkout'),
    '#weight' => 50,
  );
  // We need some CSS for showing the source and target language in one row.
  $form['#attached']['css'][] = drupal_get_path('module', 'tmgmt_ui') . '/css/tmgmt_ui.admin.css';
  return $form;
}

/**
 * Submit function, will set a job to request translation status.
 */
function tmgmt_job_checkout_form_submit($form, &$form_state) {
  // Get job from form_state
  $job = $form_state['job'];
  if ($job->isNew()) {
    $job->prepared();
  }
  $job->requestTranslation();

  // Print a message to the screen and redirect the user.
  $messages = $job->getMessagesSince(REQUEST_TIME);
  $had_error = FALSE;
  foreach ($messages as $message) {
    // Ignore debug messages.
    if ($message->type == 'debug') {
      continue;
    }

    $text = $message->getMessage();
    if ($message->type == 'error') {
      $had_error = TRUE;
    }
    drupal_set_message(filter_xss($text), $message->type);
  }

  // redirect to job overview page, if no error happened.
  if (!$had_error) {
    drupal_goto('admin/config/regional/tmgmt');
  }
}

/**
 * Validates the checkout form.
 */
function tmgmt_job_checkout_form_validate($form, &$form_state) {
  $values = $form_state['values'];
  $job = $form_state['job'];
  $translator = tmgmt_translator_load($values['translator']);
  $available_languages = entity_metadata_language_list();
  switch (tmgmt_translator_check($translator, $job->source_language, $values['target_language'])) {
    case TMTGT_TRANSLATOR_NOT_AVAILABLE:
      form_set_error('translator', t('Translator is not available: @reason.', array('@reason' => $translator->getNotAvailableReason())));
      break;
    case TMGMT_TRANSLATOR_NOT_AVAILABLE_FOR_LANGUAGE:
      form_set_error('translator', t('Test translator is not available for @language.', array('@language' => $available_languages[$values['target_language']])));
      break;
  }
  $job->target_language = $values['target_language'];
  $job->translator = $values['translator'];
  $job->settings = $values['settings'];
  if (!$job->isTranslatable()) {
    form_set_error('translator', t('Fail: todo add generic message'));
  }
}

/**
 * Ajax callback to fetch the supported translator services.
 */
function tmgmt_ajax_callback_get_supported_translators($form, &$form_state) {
  return $form['translator_wrapper'];
}

/**
 * Ajax callback to fetch the options provided by a translator.
 */
function tmgmt_ajax_callback_get_translator_options($form, &$form_state) {
  return $form['translator_wrapper']['options'];
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

/**
 * Page callback to view a job.
 */
function tmgmt_job_view(TMGMTJob $job) {
  drupal_set_title(entity_label('tmgmt_job', $job));
  return entity_view('tmgmt_job', array($job));
}
