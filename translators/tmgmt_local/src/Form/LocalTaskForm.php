<?php

/**
 * @file
 * Contains \Drupal\tmgmt_local\Form\LocalTaskForm.
 */

namespace Drupal\tmgmt_local\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\tmgmt_local\Entity\LocalTask;
use Drupal\views\Views;

/**
 * Form controller for the localTask edit forms.
 *
 * @ingroup tmgmt_local_task
 */
class LocalTaskForm extends ContentEntityForm {

  /**
   * The local task.
   *
   * @var \Drupal\tmgmt_local\LocalTaskInterface
   */
  protected $entity;

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $local_task = $this->entity;

    $states = tmgmt_local_task_statuses();
    // Set the title of the page to the label and the current state of the
    // localTask.
    $form['#title'] = (t('@title (@source to @target, @state)', array(
      '@title' => $local_task->label(),
      '@source' => $local_task->getJob()->getSourceLanguage()->getName(),
      '@target' => $local_task->getJob()->getTargetLanguage()->getName(),
      '@state' => $states[$local_task->getStatus()],
    )));

    $form['status'] = array(
      '#type' => 'select',
      '#title' => t('Status'),
      '#options' => $states,
      '#default_value' => $local_task->getStatus(),
      '#access' => \Drupal::currentUser()->hasPermission('administer tmgmt') || \Drupal::currentUser()->hasPermission('administer translation tasks'),
    );

    $translators = tmgmt_local_translators($local_task->getJob()->getSourceLangcode(), array($local_task->getJob()->getTargetLangcode()));
    $form['tuid'] = array(
      '#title' => t('Assigned'),
      '#type' => 'select',
      '#options' => $translators,
      '#empty_option' => t('- Select user -'),
      '#default_value' => $local_task->getJob()->getTranslatorId(),
      '#access' => \Drupal::currentUser()->hasPermission('administer tmgmt') || \Drupal::currentUser()->hasPermission('administer translation tasks'),
    );

    $form['title'] = array(
      '#title' => t('Title'),
      '#type' => 'textfield',
      '#default_value' => $local_task->label(),
      '#required' => TRUE,
    );

    $form['info'] = array(
      '#type' => 'container',
      '#attributes' => array('class' => array('tmgmt-ui-localTask-info', 'clearfix')),
      '#weight' => 0,
    );

    // Check for label value and set for dynamically change.
    if ($form_state->getValue('label') && $form_state->getValue('label') == $local_task->label()) {
      $form_state->setValue('label', $local_task->label());
    }

    $form['label']['widget'][0]['value']['#description'] = t('You can provide a label for this localTask in order to identify it easily later on. Or leave it empty to use default one.');
    $form['label']['#group'] = 'info';
    $form['label']['#prefix'] = '<div id="tmgmt-ui-label">';
    $form['label']['#suffix'] = '</div>';

    $form['info']['source_language'] = array(
      '#title' => t('Source language'),
      '#type' => 'item',
      '#markup' => $local_task->getJob()->getSourceLanguage()->getName(),
      '#prefix' => '<div id="tmgmt-ui-source-language" class="tmgmt-ui-source-language tmgmt-ui-info-item">',
      '#suffix' => '</div>',
      '#value' => $local_task->getJob()->getSourceLangcode(),
    );

    $form['info']['target_language'] = array(
      '#title' => t('Target language'),
      '#type' => 'item',
      '#markup' => $local_task->getJob()->getTargetLanguage()->getName(),
      '#prefix' => '<div id="tmgmt-ui-target-language" class="tmgmt-ui-target-language tmgmt-ui-info-item">',
      '#suffix' => '</div>',
      '#value' => $local_task->getJob()->getTargetLangcode(),
    );

    $form['info']['word_count'] = array(
      '#type' => 'item',
      '#title' => t('Total word count'),
      '#markup' => number_format($local_task->getWordCount()),
      '#prefix' => '<div class="tmgmt-ui-word-count tmgmt-ui-info-item">',
      '#suffix' => '</div>',
    );

    // Display created time only for localTasks that are not new anymore.
    if (!$local_task->getJob()->isUnprocessed()) {
      $form['info']['created'] = array(
        '#type' => 'item',
        '#title' => t('Created'),
        '#markup' => \Drupal::service('date.formatter')->format($local_task->getJob()->getCreatedTime()),
        '#prefix' => '<div class="tmgmt-ui-created tmgmt-ui-info-item">',
        '#suffix' => '</div>',
        '#value' => $local_task->getJob()->getCreatedTime(),
      );
    }

    if ($view = Views::getView('tmgmt_local_task_items')) {
      $block = $view->preview('block_1', [$local_task->id()]);
      $form['items'] = array(
        '#type' => 'item',
        '#title' => $view->getTitle(),
        '#prefix' => '<div class="tmgmt-local-task-items">',
        '#markup' => \Drupal::service('renderer')->render($block),
        '#attributes' => array('class' => array('tmgmt-local-task-items')),
        '#suffix' => '</div>',
        '#weight' => 10,
      );
    }

    // Add the buttons and action links.
    $form['actions']['#type'] = 'actions';
    $form['actions']['#access'] = \Drupal::currentUser()->hasPermission('administer tmgmt') || \Drupal::currentUser()->hasPermission('administer translation tasks');
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Save task'),
    );

    // Check if the translator entity is completely new or not.
    $old = empty($local_task->isNew()) && $form_state->getBuildInfo()['callback_object']->operation != 'clone';
    if ($old) {
      $form['actions']['delete'] = array(
        '#type' => 'submit',
        '#value' => t('Delete'),
        '#redirect' => 'translate/' . $local_task->id() . '/delete',
        // Don't run validations, so the user can always delete the job.
        '#limit_validation_errors' => array(),
      );
    }

    $form['#attached']['library'][] = 'tmgmt/admin';
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function actions(array $form, FormStateInterface $form_state) {
    $local_task = $this->entity;

    $actions['save'] = array(
      '#type' => 'submit',
      '#value' => t('Save task'),
      '#submit' => array('::submitForm', '::save'),
      '#weight' => 5,
      '#button_type' => 'primary',
    );

    if (!$local_task->isNew()) {
      $actions['delete'] = array(
        '#type' => 'submit',
        '#value' => t('Delete'),
        '#submit' => array('tmgmt_submit_redirect'),
        '#redirect' => 'admin/tmgmt/localTasks/' . $local_task->id() . '/delete',
        // Don't run validations, so the user can always delete the localTask.
        '#limit_validation_errors' => array(),
      );
    }
    return $actions;
  }


  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    /** @var LocalTask $local_task */
    $local_task = $this->buildEntity($form, $form_state);
    // Load the selected translator.
    $translator = $local_task->getJob()->getTranslator();
    // Check translator availability.
    if (!empty($translator)) {
      if (!$result = $translator->checkAvailable()) {
        $form_state->setErrorByName('translator', $result->getReason());
      }
      elseif (!$result = $translator->checkTranslatable($local_task->getJob())) {
        $form_state->setErrorByName('translator', $result->getReason());
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildEntity(array $form, FormStateInterface $form_state) {
    /** @var LocalTask $local_task */
    $local_task = parent::buildEntity($form, $form_state);

    $translator = $local_task->getJob()->getTranslator();
    if (!empty($translator)) {
      // If requested custom localTask settings handling, copy values from
      // original localTask.
      if ($translator->hasCustomSettingsHandling()) {
        /** @var LocalTask $original_local_task */
        $original_local_task = \Drupal::entityTypeManager()->getStorage('tmgmt_local_task')->loadUnchanged($local_task->id());
        $local_task->settings = $original_local_task->settings;
      }
    }
    // Make sure that we always store a label as it can be a slow operation to
    // generate the default label.
    if (empty($local_task->label)) {
      $local_task->label = $local_task->label();
    }
    return $local_task;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    parent::save($form, $form_state);

    // Everything below this line is only invoked if the 'Submit to translator'
    // button was clicked.
    if ($form_state->getTriggeringElement()['#value'] == $form['actions']['submit']['#value']) {
      if (!tmgmt_job_request_translation($this->entity->getJob())) {
        // Don't redirect the user if the translation request failed but retain
        // existing destination parameters so we can redirect once the request
        // finished successfully.
        unset($_GET['destination']);
      }
      elseif ($redirect = tmgmt_redirect_queue_dequeue()) {
        // Proceed to the next redirect queue item, if there is one.
        $form_state->setRedirectUrl(Url::fromUri('base:' . $redirect));
      }
      elseif ($destination = tmgmt_redirect_queue_destination()) {
        // Proceed to the defined destination if there is one.
        $form_state->setRedirectUrl(Url::fromUri('base:' . $destination));
      }
      else {
        // Per default we want to redirect the user to the overview.
        $form_state->setRedirect('view.tmgmt_localTask_overview.page_1');
      }
    }
    else {
      // Per default we want to redirect the user to the overview.
      $form_state->setRedirect('view.tmgmt_localTask_overview.page_1');
    }
  }

  /**
   * {@inheritdoc}
   */
  public function delete(array $form, FormStateInterface $form_state) {
    $form_state->setRedirectUrl($this->entity->toUrl('delete-form'));
  }

}
