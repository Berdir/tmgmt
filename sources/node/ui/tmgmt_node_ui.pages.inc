<?php

/**
 * @file
 * Please supply a file description.
 */

/*
* Ebeds the view.
*/
function tmgmt_node_ui_translation_history() {
  return views_embed_view('tmgmt_node_ui_job_history');
}

/**
 * Node translation overview form. This form overrides the Drupal core or i18n
 * Content Translation page with a tableselect form.
 */
function tmgmt_node_ui_node_form($form, &$form_state, $node, $original) {
  // Store the node in the form state so we can easily create the job in the
  // submit handler.
  $form_state['node'] = $node;
  // Inject our additional column into the header.
  array_splice($original['#header'], -1, 0, array(t('Latest translation job')));
  // Make this a tableselect form.
  $form['languages'] = array(
    '#type' => 'tableselect',
    '#header' => $original['#header'],
    '#options' => array(),
    '#js_select' => TRUE,
  );
  $languages = module_exists('i18n_node') ? i18n_node_language_list($node) : language_list();
  // Check if there is a job / job item that references this translation.
  $items = tmgmt_job_item_load_latest('node', 'node', $node->nid, $node->language);
  // Collect possibly outdated translation jobs.
  foreach ($languages as $langcode => $language) {
    if ($langcode == LANGUAGE_NONE) {
      // Never show language neutral on the overview.
      continue;
    }
    // Since the keys are numeric and in the same order we can shift one element
    // after the other from the original non-form rows.
    $option = array_shift($original['#rows']);
    if ($langcode == $node->language) {
      $additional = t('Source');
      // This is the source object so we disable the checkbox for this row.
      $form['languages'][$langcode] = array(
        '#type' => 'checkbox',
        '#disabled' => TRUE,
      );
    }
    elseif (isset($items[$langcode])) {
      // Load the job that hosts this item.
      $item = $items[$langcode];
      $job = $item->getJob();
      // Prepare the
      $item_uri = $item->uri();
      $item_wrapper = entity_metadata_wrapper('tmgmt_job_item', $item);
      $job_uri = $job->uri();
      $job_wrapper = entity_metadata_wrapper('tmgmt_job', $job);
      $additional = t('!item (@item-state) in !job (@job-state)', array('!item' => l(t('Job item'), $item_uri['path']), '@item-state' => $item_wrapper->state->label(), '!job' => l($job->label(), $job_uri['path']), '@job-state' => $job_wrapper->state->label()));
      // Disable the checkbox for this row since there is already a translation
      // in progress that has not yet been accepted. This way we make sure that
      // we don't stack multiple active translations for the same item on top
      // of each other.
      if (!$item->isAccepted()) {
        $form['languages'][$langcode] = array(
          '#type' => 'checkbox',
          '#disabled' => TRUE,
        );
      }
    }
    else {
      // There is no translation job / job item for this target language.
      $additional = t('None');
    }
    // Inject the additional column into the array.
    array_splice($option, -1, 0, array($additional));
    // Append the current option array to the form.
    $form['languages']['#options'][$langcode] = $option;
  }
  $form['actions']['#type'] = 'actions';
  $form['actions']['request'] = array(
    '#type' => 'submit',
    '#value' => t('Request translation'),
  );
  $form['#submit'][] = 'tmgmt_node_ui_translate_form_submit';
  $form['#validate'][] = 'tmgmt_node_ui_translate_form_validate';
  return $form;
}

/**
 * Validation callback for the node translation overview form.
 */
function tmgmt_node_ui_translate_form_validate($form, &$form_state) {
  $selected = array_filter($form_state['values']['languages']);
  if (empty($selected)) {
    form_set_error('languages', t('You have to select at least one language for requesting a translation.'));
  }
}

/**
 * Submit callback for the node translation overview form.
 */
function tmgmt_node_ui_translate_form_submit($form, &$form_state) {
  $node = $form_state['node'];
  $values = $form_state['values'];
  foreach (array_keys(array_filter($values['languages'])) as $langcode) {
    // Create the job object.
    $job = tmgmt_job_create($node->language, $langcode, $GLOBALS['user']->uid);
    // Add the job item.
    $job->addItem('node', 'node', $node->nid);
    // Append this job to the array of created jobs so we can redirect the user
    // to a multistep checkout form if necessary.
    $jobs[$job->tjid] = $job;
  }
  if (count($jobs) > 1) {
    // @todo We need to redirect to a multistep form here.
  }
  else {
    // We only got one job.
    $job = reset($jobs);
    // Append a destination query so that the user gets redirected to this very
    // page after pressing the checkout button.
    $form_state['redirect'] = array('admin/config/regional/tmgmt/jobs/' . $job->tjid . '/manage', array('query' => array('destination' => current_path())));
  }
}
