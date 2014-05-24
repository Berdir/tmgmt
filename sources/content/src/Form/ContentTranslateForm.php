<?php

/**
 * @file
 * Contains \Drupal\tmgmt_content\Form\ContentTranslateForm.
 */

namespace Drupal\tmgmt_content\Form\ContentTranslateForm;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Language\LanguageInterface;
use Drupal\tmgmt\TMGMTException;

class ContentTranslateForm extends FormBase {
  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'tmgmt_content_translate_form';
  }

  /**
   * {@inheritdoc}
   */
  function buildForm(array $form, array &$form_state, array $build = NULL) {
    // Store the entity in the form state so we can easily create the job in the
    // submit handler.

    $form_state['entity'] = $build['#entity'];

    $overview = $build['content_translation_overview'];

    $form['top_actions']['#type'] = 'actions';
    $form['top_actions']['#weight'] = -10;
    tmgmt_ui_add_cart_form($form['top_actions'], $form_state, 'content', $form_state['entity']->getEntityTypeId(), $form_state['entity']->id());

    // Inject our additional column into the header.
    array_splice($overview['#header'], -1, 0, array(t('Pending Translations')));
    // Make this a tableselect form.
    $form['languages'] = array(
      '#type' => 'tableselect',
      '#header' => $overview['#header'],
      '#options' => array(),
    );
    $languages = \Drupal::languageManager()->getLanguages();
    // Check if there is a job / job item that references this translation.
    $entity_langcode = $form_state['entity']->language()->id;
    $items = tmgmt_job_item_load_latest('content', $form_state['entity']->getEntityTypeId(), $form_state['entity']->id(), $entity_langcode);
    foreach ($languages as $langcode => $language) {
      if ($langcode == LanguageInterface::LANGCODE_DEFAULT) {
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
        $states = tmgmt_job_item_states();
        $additional = \Drupal::l($states[$item->getState()], $item->urlInfo()->getRouteName(), $item->urlInfo()->getRouteParameters() + array('destination' => current_path()));
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
      '#value' =>$this->t('Request translation'),
      '#validate' => array(array($this, 'validateForm')),
      '#submit' => array(array($this, 'submitForm')),
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  function validateForm(array &$form, array &$form_state) {
    $selected = array_filter($form_state['values']['languages']);
    if (empty($selected)) {
      \Drupal::formBuilder()->setErrorByName('languages', $form_state,$this->t('You have to select at least one language for requesting a translation.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  function submitForm(array &$form, array &$form_state) {
    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $entity = $form_state['entity'];
    $values = $form_state['values'];
    $jobs = array();
    foreach (array_keys(array_filter($values['languages'])) as $langcode) {
      // Create the job object.
      $job = tmgmt_job_create($entity->language()->id, $langcode, $GLOBALS['user']->id());
      debug($job->toArray());
      try {
        // Add the job item.
        $job->addItem('content', $entity->getEntityTypeId(), $entity->id());
        // Append this job to the array of created jobs so we can redirect the user
        // to a multistep checkout form if necessary.
        $jobs[$job->id()] = $job;
      }
      catch (TMGMTException $e) {
        watchdog_exception('tmgmt', $e);
        $languages = \Drupal::languageManager()->getLanguages();
        $target_lang_name = $languages[$langcode]->language;
        drupal_set_message(t('Unable to add job item for target language %name. Make sure the source content is not empty.', array('%name' => $target_lang_name)), 'error');
      }
    }
    tmgmt_ui_job_checkout_and_redirect($form_state, $jobs);
  }

}
