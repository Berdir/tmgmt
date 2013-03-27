<?php

/**
 * @file
 * Provides page and forms callbacks.
 */

/**
 * Simple page callback for viewing a task.
 *
 * @param TMGMTJob $task
 *   The viewed task.
 *
 * @return array
 *   A renderable array.
 */
function tmgmt_local_task_view(TMGMTLocalTask $task) {
  return entity_view($task->entityType(), array($task), 'full', NULL, TRUE);
}

/**
 * Entity API form the local task entity.
 */
function tmgmt_local_task_form($form, &$form_state, TMGMTLocalTask $task, $op = 'edit') {
  $wrapper = entity_metadata_wrapper('tmgmt_local_task', $task);

  // Set the title of the page to the label and the current status of the task.
  drupal_set_title(t('@label (@status)', array('@label' => $task->label(), '@status' => $wrapper->status->label())));

  // Check if the translator entity is completely new or not.
  $old = empty($task->is_new) && $op != 'clone';

  $form['title'] = array(
    '#type' => 'textfield',
    '#title' => t('Title'),
    '#default_value' => $task->title,
  );

  $form['status'] = array(
    '#type' => 'select',
    '#title' => t('Status'),
    '#options' => tmgmt_local_task_statuses(),
    '#default_value' => $wrapper->status->value(),
  );

  $translators = tmgmt_local_translators($task->getJob()->source_language, $task->getJob()->target_language);
  $form['tuid'] = array(
    '#title' => t('Assigned'),
    '#type' => 'select',
    '#options' => $translators,
    '#empty_option' => t('- Select user -'),
    '#default_value' => $task->tuid,
  );

  // Add the buttons and action links.
  $form['actions']['#type'] = 'actions';
  $form['actions']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Save task'),
  );

  if ($old) {
    $form['actions']['delete'] = array(
      '#type' => 'submit',
      '#value' => t('Delete'),
      '#redirect' => 'admin/config/regional/tmgmt/local-tasks/' . $task->tltid . '/delete',
      // Don't run validations, so the user can always delete the job.
      '#limit_validation_errors' => array(),
    );
  }

  return $form;
}

/**
 * Submit callback for the local task form.
 */
function tmgmt_local_task_form_submit($form, &$form_state) {
  $task = entity_ui_form_submit_build_entity($form, $form_state);
  $task->save();
}

/**
 * Assign task to current user.
 *
 * @param TMGMTLocalTask $task
 */
function tmgmt_local_translation_assign_to_me(TMGMTLocalTask $task) {
  $task->assign($GLOBALS['user']);
  $task->save();
  drupal_goto('translate');
}

/**
 * Form callback for translating a job item.
 */
function tmgmt_local_translation_form($form, &$form_state, TMGMTLocalTask $task, TMGMTJobItem $item) {
  $form_state['task'] = $task;
  $form_state['item'] = $item;

  if ($item->getJob()->getSetting('job_comment')) {
    $form['job_comment'] = array(
      '#type' => 'item',
      '#title' => t('Job comment'),
      '#markup' => $item->getJob()->getSetting('job_comment'),
    );
  }

  $form['translation'] = array(
    '#type' => 'container',
  );

  // Build the translation form.
  $data = $item->getData();

  // Need to keep the first hierarchy. So flatten must take place inside
  // of the foreach loop.
  $zebra = 'even';
  // Reverse the order to get the correct order.
  foreach (array_reverse(element_children($data)) as $key) {
    $flattened = tmgmt_flatten_data($data[$key], $key);
    $form['translation'][$key] = tmgmt_local_translation_form_element($flattened, $item, $zebra);
  }

  // Add the form actions as well.
  $form['actions']['#type'] = 'actions';
  $form['actions']['save_as_completed'] = array(
    '#type' => 'submit',
    '#validate' => array('tmgmt_local_translation_form_save_as_completed_validate'),
    '#submit' => array('tmgmt_local_translation_form_save_submit', 'tmgmt_local_translation_form_save_as_completed_submit'),
    '#value' => t('Save as completed'),
  );
  $form['actions']['save'] = array(
    '#type' => 'submit',
    '#submit' => array('tmgmt_local_translation_form_save_submit'),
    '#value' => t('Save'),
  );

  return $form;
}

/**
 * Form validate callback for save as completed submit action.
 *
 * Verify that all items are translated.
 */
function tmgmt_local_translation_form_save_as_completed_validate($form, &$form_state) {
  /**
   * @var TMGMTJobItem $item.
   */
  $item = $form_state['item'];

  // Loop over all data items and verify that there is a translation in there.
  foreach ($form_state['values'] as $key => $value) {
    if (is_array($value) && isset($value['translation'])) {
      if (empty($value['translation'])) {
        form_set_error($key . '[translation]', t('Missing translation.'));
      }
    }
  }
}

/**
 * Form submit callback for save as completed submit action.
 *
 * Change items to needs review state and task to completed status.
 */
function tmgmt_local_translation_form_save_as_completed_submit($form, &$form_state) {
  /**
   * @var TMGMTLocalTask $task.
   */
  $task = $form_state['task'];

  /**
   * @var TMGMTJobItem $item.
   */
  $item = $form_state['item'];

  // Set the job item to 'needs review' and task to finised states.
  $item->needsReview();
  // Mark the task as completed if all assigned job items are at needs done.
  $all_done = TRUE;
  foreach ($task->getItems() as $task_item) {
    if ($task_item->isActive()) {
      $all_done = FALSE;
      break;
    }
  }
  if ($all_done) {
    $task->setStatus(TMGMT_LOCAL_TASK_STATUS_COMPLETED);
  }

  // Once the job item is set to 'needs review' it is no longer accessible
  // through the translation user interface.
  $form_state['redirect'] = 'translate';
}

/**
 * Form submit callback for save action.
 *
 * Saves all items.
 */
function tmgmt_local_translation_form_save_submit($form, &$form_state) {
  /**
   * @var TMGMTJobItem $item.
   */
  $item = $form_state['item'];

  // Write the translated data into the job item.
  $values = $form_state['values'];
  foreach ($values as $key => $value) {
    if (isset($value['translation'])) {
      $update['#translation'] = array(
        '#text' => $value['translation'],
      );
      $item->updateData($key, $update);
    }
  }
  $item->save();
}

/**
 * Builds a translation form element.
 */
function tmgmt_local_translation_form_element($data, TMGMTJobItem $item, &$zebra) {
  static $flip = array(
    'even' => 'odd',
    'odd' => 'even',
  );

  $form = array();

  $job = $item->getJob();
  $language_list = language_list();

  foreach (element_children($data) as $key) {
    if (isset($data[$key]['#text']) && _tmgmt_filter_data($data[$key])) {
      // The char sequence '][' confuses the form API so we need to replace it.
      $target_key = str_replace('][', '|', $key);
      $zebra = $flip[$zebra];
      $form[$target_key] = array(
        '#tree' => TRUE,
        '#ajaxid' => drupal_html_id('tmgmt-local-element-' . $key),
        '#theme' => 'tmgmt_local_translation_form_element',
        '#parent_label' => $data[$key]['#parent_label'],
        '#zebra' => $zebra,
      );

      $source_language = $language_list[$job->source_language];
      $target_language = $language_list[$job->target_language];

      $form[$target_key]['source'] = array(
        '#type' => 'textarea',
        '#title' => $source_language->name,
        '#value' => $data[$key]['#text'],
        '#disabled' => TRUE,
      );

      $form[$target_key]['translation'] = array(
        '#type' => 'textarea',
        '#title' => $target_language->name,
        '#default_value' => isset($data[$key]['#translation']['#text']) ? $data[$key]['#translation']['#text'] : '',
        //'#required' => TRUE,
      );

      $form[$target_key]['actions'] = array(
        '#type' => 'container',
      );
      $completed = !empty($data[$key]['#status']) && $data[$key]['#status'] == TMGMT_DATA_ITEM_STATE_TRANSLATED;
      if ($completed) {
        $form[$target_key]['actions']['reject-' . $target_key] = array(
          '#type' => 'submit',
          // Unicode character &#x2717 BALLOT X
          '#value' => '✗',
          '#attributes' => array('title' => t('Reject')),
          '#name' => 'reject-' . $target_key,
          '#submit' => array('tmgmt_local_translation_form_update_state_submit'),
          '#ajax' => array(
            'callback' => 'tmgmt_local_translation_form_update_state_ajax',
            'wrapper' => $form[$target_key]['#ajaxid'],
          ),
          '#tmgmt_local_action' => 'reject',
          '#tmgmt_local_key' => str_replace('][', '|', $key),
        );
      }
      else {
        $form[$target_key]['actions']['finish-' . $target_key] = array(
          '#type' => 'submit',
          // Unicode character &#x2713 CHECK MARK
          '#value' => '✓',
          '#attributes' => array('title' => t('Finish')),
          '#name' => 'finish-' . $target_key,
          '#submit' => array('tmgmt_local_translation_form_update_state_submit'),
          '#ajax' => array(
            'callback' => 'tmgmt_local_translation_form_update_state_ajax',
            'wrapper' => $form[$target_key]['#ajaxid'],
          ),
          '#tmgmt_local_action' => 'finish',
          '#tmgmt_local_key' => str_replace('][', '|', $key),
        );
      }
    }
  }

  return $form;
}

/**
 * Form submit callback for the translation state update button.
 */
function tmgmt_local_translation_form_update_state_submit($form, &$form_state) {
  $values = $form_state['values'];

  /**
   * @var TMGMTJobItem $item.
   */
  $item = $form_state['item'];

  $action = $form_state['triggering_element']['#tmgmt_local_action'];
  $key = $form_state['triggering_element']['#tmgmt_local_key'];

  // Write the translated data into the job item.
  if (isset($values[$key]) && is_array($values[$key]) && isset($values[$key]['translation'])) {
    $update['#status'] = $action == 'finish' ? TMGMT_DATA_ITEM_STATE_TRANSLATED : TMGMT_DATA_ITEM_STATE_PENDING;
    $update['#translation'] = array(
      '#text' => $values[$key]['translation'],
    );

    $item->updateData($key, $update);
    $item->save();

    // We need to rebuild form so we get updated action button state.
    $form_state['rebuild'] = TRUE;
  }
}

/**
 * Ajax callback for the job item review form.
 */
function tmgmt_local_translation_form_update_state_ajax($form, &$form_state) {
  $key = array_slice($form_state['triggering_element']['#array_parents'], 0, 3);
  $commands = array();
  $render_data = drupal_array_get_nested_value($form, $key);
  $commands[] = ajax_command_replace(NULL, drupal_render($render_data));
  tmgmt_ui_write_request_messages($form_state['item']->getJob());
  $commands[] = ajax_command_html('#tmgmt-status-messages-' . strtolower($render_data['#parent_label'][0]), theme('status_messages'));
  return array('#type' => 'ajax', '#commands' => $commands);
}

/**
 * Form for assigning multiple tasks to translator.
 *
 * @param array $form
 * @param array $form_state
 * @param string $tasks
 *  Comma separated tasks ids.
 *
 * @return array
 *   Drupal form definition.
 */
function tmgmt_local_translation_assign_form($form, &$form_state, $tasks) {
  $form_state['tasks'] = explode(',', $tasks);

  $form['tuid'] = array(
    '#title' => t('Assign to'),
    '#type' => 'select',
    '#empty_option' => t('Select user'),
    '#options' => tmgmt_local_get_translators_for_tasks($form_state['tasks']),
    '#required' => TRUE,
  );

  $form['actions']['#type'] = 'actions';
  $form['actions']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Assign tasks'),
  );

  return $form;
}

/**
 * Submit handler for form for assigning multiple tasks to translator.
 */
function tmgmt_local_translation_assign_form_submit($form, &$form_state) {
  $translator = user_load($form_state['values']['tuid']);

  $how_many = 0;
  foreach ($form_state['tasks'] as $task_id) {
    $task = tmgmt_local_task_load($task_id);
    if ($task) {
      $task->assign($translator);
      $task->save();
      ++$how_many;
    }
  }

  drupal_set_message(t('Assigned @how_many to translator @translator_name.', array('@how_many' => $how_many, '@translator_name' => $translator->name)));

  $form_state['redirect'] = 'manage-translate';
}

/**
 * Similar to form for assigning multiple tasks to translator, but here we also
 * allow no user selection.
 *
 * @see tmgmt_local_translation_assign_form().
 */
function tmgmt_local_translation_reassign_form($form, &$form_state, $tasks) {
  $form_state['tasks'] = explode(',', $tasks);

  $form['tuid'] = array(
    '#title' => t('Assign to'),
    '#type' => 'select',
    '#empty_option' => t('Select user'),
    '#options' => tmgmt_local_get_translators_for_tasks($form_state['tasks']),
  );

  $form['actions']['#type'] = 'actions';
  $form['actions']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Reassign tasks'),
  );

  return $form;
}

/**
 * Submit handler for form for reassigning multiple tasks to translator.
 */
function tmgmt_local_translation_reassign_form_submit($form, &$form_state) {

  if (!empty($form_state['values']['tuid'])) {
    $translator = user_load($form_state['values']['tuid']);
  }
  else {
    $translator = (object) array('uid' => 0, 'name' => t('none'));
  }

  $how_many = 0;
  foreach ($form_state['tasks'] as $task_id) {
    $task = tmgmt_local_task_load($task_id);
    if ($task) {
      if ($translator->uid) {
        $task->assign($translator);
      }
      else {
        $task->unassign();
      }
      $task->save();
      ++$how_many;
    }
  }

  drupal_set_message(t('Reassigned @how_many to translator @translator_name.', array('@how_many' => $how_many, '@translator_name' => $translator->name)));

  $form_state['redirect'] = 'manage-translate';
}
