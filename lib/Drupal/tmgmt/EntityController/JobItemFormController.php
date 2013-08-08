<?php

/**
 * @file
 * Contains \Drupal\tmgmt\EntityController\JobItemFormController.
 */

namespace Drupal\tmgmt\EntityController;

/**
 * Form controller for the job item edit forms.
 *
 * @ingroup tmgmt_job
 */
class JobItemFormController extends TMGMTFormControllerBase {

  /**
   * Overrides Drupal\Core\Entity\EntityFormController::form().
   */
  public function form(array $form, array &$form_state) {
    $form = parent::form($form, $form_state);
    $item = $this->entity;
    $form['info'] = array(
      '#type' => 'container',
      '#attributes' => array('class' => array('tmgmt-ui-job-info', 'clearfix')),
      '#weight' => 0,
    );

    $uri = $item->getSourceUri();
    $form['info']['source'] = array(
      '#type' => 'item',
      '#title' => t('Source'),
      '#markup' => l($item->getSourceLabel(), $uri['path'], $uri['options']),
      '#prefix' => '<div class="tmgmt-ui-source tmgmt-ui-info-item">',
      '#suffix' => '</div>',
    );

    $form['info']['sourcetype'] = array(
      '#type' => 'item',
      '#title' => t('Source type'),
      '#markup' => $item->getSourceType(),
      '#prefix' => '<div class="tmgmt-ui-source-type tmgmt-ui-info-item">',
      '#suffix' => '</div>',
    );

    $form['info']['changed'] = array(
      '#type' => 'item',
      '#title' => t('Last change'),
      '#markup' => format_date($item->changed),
      '#prefix' => '<div class="tmgmt-ui-changed tmgmt-ui-info-item">',
      '#suffix' => '</div>',
    );
    $states = tmgmt_job_item_states();
    $form['info']['state'] = array(
      '#type' => 'item',
      '#title' => t('State'),
      '#markup' => $states[$item->state],
      '#prefix' => '<div class="tmgmt-ui-item-state tmgmt-ui-info-item">',
      '#suffix' => '</div>',
      '#value' => $item->state,
    );
    $job = $item->getJob();
    $uri = $job->uri();
    $form['info']['job'] = array(
      '#type' => 'item',
      '#title' => t('Job'),
      '#markup' => l($job->label(), $uri['path']),
      '#prefix' => '<div class="tmgmt-ui-job tmgmt-ui-info-item">',
      '#suffix' => '</div>',
    );

    // Display selected translator for already submitted jobs.
    if (!$item->getJob()->isSubmittable()) {
      $translators = tmgmt_translator_labels();
      $form['info']['translator'] = array(
        '#type' => 'item',
        '#title' => t('Translator'),
        '#markup' => isset($translators[$item->getJob()->translator]) ? check_plain($translators[$item->getJob()->translator]) : t('Missing translator'),
        '#prefix' => '<div class="tmgmt-ui-translator tmgmt-ui-info-item">',
        '#suffix' => '</div>',
      );
    }

    // Actually build the review form elements...
    $form['review'] = array(
      '#type' => 'container',
    );
    // Build the review form.
    $data = $item->getData();
    // Need to keep the first hierarchy. So flatten must take place inside
    // of the foreach loop.
    $zebra = 'even';
    foreach (element_children($data) as $key) {
      $form['review'][$key] = _tmgmt_ui_review_form_element($form_state, tmgmt_flatten_data($data[$key], $key), $item, $zebra, $key);
    }

    if ($output = tmgmt_ui_embed_view('tmgmt_ui_job_item_messages', 'block', array($item->tjiid))) {
      $form['messages'] = array(
        '#type' => 'fieldset',
        '#title' => t('Messages'),
        '#collapsible' => TRUE,
        '#collapsed' => TRUE,
        '#weight' => 50,
      );
      $form['messages']['view'] = array(
        '#type' => 'item',
        '#markup' => $output,
      );
    }


    $form['#attached']['css'][] = drupal_get_path('module', 'tmgmt_ui') . '/css/tmgmt_ui.admin.css';
    // The reject functionality has to be implement by the translator plugin as
    // that process is completely unique and custom for each translation service.

    // Give the source ui controller a chance to affect the review form.
    $source = $this->sourceManager->createUIInstance($item->plugin);
    $form = $source->reviewForm($form, $form_state, $item);
    // Give the translator ui controller a chance to affect the review form.
    if ($item->getTranslator()) {
      $plugin_ui = $this->translatorManager->createUIInstance($item->getTranslator()->plugin);
      $form = $plugin_ui->reviewForm($form, $form_state, $item);
    }

    return $form;
  }

  protected function actions(array $form, array &$form_state) {
    $item = $this->entity;

    // Add the form actions as well.
    $actions['accept'] = array(
      '#type' => 'submit',
      '#value' => t('Save as completed'),
      '#access' => $item->isNeedsReview(),
      '#validate' => array(
        array($this, 'validate'),
      ),
      '#submit' => array(
        array($this, 'submit'),
        array($this, 'save'),
      ),
    );
    $actions['save'] = array(
      '#type' => 'submit',
      '#value' => t('Save'),
      '#access' => !$item->isAccepted(),
      '#validate' => array(
        array($this, 'validate'),
      ),
      '#submit' => array(
        array($this, 'submit'),
        array($this, 'save'),
      ),
    );
    $uri = $item->getJob()->uri();
    $url = isset($_GET['destination']) ? $_GET['destination'] : $uri['path'];
    $actions['cancel'] = array(
      '#type' => 'link',
      '#title' => t('Cancel'),
      '#href' => $url,
    );
    return $actions;
  }


  /**
   * {@inheritdoc}
   */
  public function validate(array $form, array &$form_state) {
    parent::validate($form, $form_state);
    $item = $this->buildEntity($form, $form_state);
    // First invoke the validation method on the source controller.
    $source_ui = $this->sourceManager->createUIInstance($item->plugin);
    $source_ui->reviewFormValidate($form, $form_state, $item);
    // Invoke the validation method on the translator controller (if available).
    if($item->getTranslator()){
      $translator_ui = $this->translatorManager->createUIInstance($item->getTranslator()->plugin);
      $translator_ui->reviewFormValidate($form, $form_state, $item);
    }
  }

  /**
   * Overrides Drupal\Core\Entity\EntityFormController::save().
   */
  public function save(array $form, array &$form_state) {
    $item = $this->entity;
    // First invoke the submit method on the source controller.
    $source_ui = $this->sourceManager->createUIInstance($item->plugin);
    $source_ui->reviewFormSubmit($form, $form_state, $item);
    // Invoke the submit method on the translator controller (if available).
    if ($item->getTranslator()){
      $translator_ui = $this->translatorManager->createUIInstance($item->getTranslator()->plugin);
      $translator_ui->reviewFormSubmit($form, $form_state, $item);
    }
    // Write changes back to item.
    foreach ($form_state['values'] as $key => $value) {
      if (is_array($value) && isset($value['translation'])) {
        // Update the translation, this will only update the translation in case
        // it has changed.
        $data = array(
          '#text' => $value['translation'],
          '#origin' => 'local',
        );
        $item->addTranslatedData($data, $key);
      }
    }
    // Check if the user clicked on 'Accept', 'Submit' or 'Reject'.
    if (!empty($form['actions']['accept']) && $form_state['triggering_element']['#value'] == $form['actions']['accept']['#value']) {
      $item->acceptTranslation();
      // Check if the item could be saved and accepted successfully and redirect
      // to the job item view if that is the case.
      if ($item->isAccepted()) {
        $uri = $item->uri();
        $form_state['redirect'] = $uri['path'];
      }
      // Print all messages that have been saved while accepting the reviewed
      // translation.
      foreach ($item->getMessagesSince() as $message) {
        // Ignore debug messages.
        if ($message->type == 'debug') {
          continue;
        }
        if ($text = $message->getMessage()) {
          drupal_set_message(filter_xss($text), $message->type);
        }
      }
    }
    $item->save();
  }

  /**
   * {@inheritdoc}
   */
  public function delete(array $form, array &$form_state) {
    $entity = $this->entity;
    $form_state['redirect'] = 'admin/tmgmt/items/' . $entity->id() . '/delete';
  }

}
