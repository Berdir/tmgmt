<?php

/**
 * @file
 * Provides page and form callbacks for the Translation Management Tool Entity
 * Source User Interface module.
 */

use Drupal\tmgmt\TMGMTException;
use Drupal\Core\Language\Language;

/**
 * Entity translation overview form.
 */
function tmgmt_entity_ui_translate_form($form, &$form_state, $build) {
  // Store the entity in the form state so we can easily create the job in the
  // submit handler.
  $form_state['entity'] = $build['#entity'];

  $overview = $build['content_translation_overview'];

  // Inject our additional column into the header.
  array_splice($overview['#header'], -1, 0, array(t('Pending Translations')));
  // Make this a tableselect form.
  $form['languages'] = array(
    '#type' => 'tableselect',
    '#header' => $overview['#header'],
    '#options' => array(),
  );
  $languages = language_list();
  // Check if there is a job / job item that references this translation.
  $entity_langcode = $form_state['entity']->language()->id;
  $items = tmgmt_job_item_load_latest('entity', $form_state['entity']->entityType(), $form_state['entity']->id(), $entity_langcode);
  foreach ($languages as $langcode => $language) {
    if ($langcode == Language::LANGCODE_DEFAULT) {
      // Never show language neutral on the overview.
      continue;
    }
    // Since the keys are numeric and in the same order we can shift one element
    // after the other from the original non-form rows.
    $option = array_shift($overview['#rows']);
    if ($langcode == $entity_langcode) {
      $additional = '<strong>' . t('Source') . '</strong>';
      // This is the source object so we disable the checkbox for this row.
      $form['languages'][$langcode] = array(
        '#type' => 'checkbox',
        '#disabled' => TRUE,
      );
    }
    elseif (isset($items[$langcode])) {
      $item = $items[$langcode];
      $uri = $item->uri();
      $states = tmgmt_job_item_states();
      $additional = l($states[$item->state], $uri['path'], array('query' => array('destination' => current_path())));
      // Disable the checkbox for this row since there is already a translation
      // in progress that has not yet been finished. This way we make sure that
      // we don't stack multiple active translations for the same item on top
      // of each other.
      $form['languages'][$langcode] = array(
        '#type' => 'checkbox',
        '#disabled' => TRUE,
      );
    }
    else {
      // There is no translation job / job item for this target language.
      $additional = t('None');
    }
    // Inject the additional column into the array.

    // The generated form structure has changed, support both an additional
    // 'data' key (that is not supported by tableselect) and the old version
    // without.
    if (isset($option['data'])) {
      array_splice($option['data'], -1, 0, array($additional));
      // Append the current option array to the form.
      $form['languages']['#options'][$langcode] = $option['data'];
    }
    else {
      array_splice($option, -1, 0, array($additional));
      // Append the current option array to the form.
      $form['languages']['#options'][$langcode] = $option;
    }
  }
  $form['actions']['#type'] = 'actions';
  $form['actions']['request'] = array(
    '#type' => 'submit',
    '#value' => t('Request translation'),
    '#submit' => array('tmgmt_entity_ui_translate_form_submit'),
    '#validate' => array('tmgmt_entity_ui_translate_form_validate'),
  );
  return $form;
}

/**
 * Validation callback for the entity translation overview form.
 */
function tmgmt_entity_ui_translate_form_validate($form, &$form_state) {
  $selected = array_filter($form_state['values']['languages']);
  if (empty($selected)) {
    form_set_error('languages', t('You have to select at least one language for requesting a translation.'));
  }
}

/**
 * Submit callback for the entity translation overview form.
 */
function tmgmt_entity_ui_translate_form_submit($form, &$form_state) {
  /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
  $entity = $form_state['entity'];
  $values = $form_state['values'];
  $jobs = array();
  foreach (array_keys(array_filter($values['languages'])) as $langcode) {
    // Create the job object.
    $job = tmgmt_job_create($entity->language()->id, $langcode, $GLOBALS['user']->id());
    try {
      // Add the job item.
      $job->addItem('entity', $entity->entityType(), $entity->id());
      // Append this job to the array of created jobs so we can redirect the user
      // to a multistep checkout form if necessary.
      $jobs[$job->tjid] = $job;
    }
    catch (TMGMTException $e) {
      watchdog_exception('tmgmt', $e);
      $languages = language_list();
      $target_lang_name = $languages[$langcode]->language;
      drupal_set_message(t('Unable to add job item for target language %name. Make sure the source content is not empty.', array('%name' => $target_lang_name)), 'error');
    }
  }
  tmgmt_ui_job_checkout_and_redirect($form_state, $jobs);
}
