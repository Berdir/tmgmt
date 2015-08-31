<?php

/**
 * @file
 * Contains \Drupal\tmgmt_local\Form\LocalTaskForm.
 */

namespace Drupal\tmgmt_local\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Url;
use Drupal\tmgmt_local\Entity\LocalTask;
use Drupal\tmgmt_local\Entity\LocalTaskItem;
use Drupal\tmgmt_local\LocalTaskInterface;
use Drupal\views\Views;

/**
 * Form controller for the localTask edit forms.
 *
 * @ingroup tmgmt_local_task
 */
class LocalTaskForm extends ContentEntityForm {

  /**
   * @var \Drupal\tmgmt_local\LocalTaskInterface
   */
  protected $entity;

  /**
   * Overrides Drupal\Core\Entity\EntityForm::form().
   */
  public function form(array $form, FormStateInterface $form_state) {

    $localTask = $this->entity;

    $form['label'] = array(
      '#type' => 'markup',
      '#markup' => $localTask->defaultLabel(),
    );

    $form['title'] = array(
      '#title' => t('Title'),
      '#type' => 'textfield',
      '#default_value' => $localTask->getTitle(),
      '#required' => TRUE,
    );
    return $form;
    /*$states = LocalTask::getStates();
    // Set the title of the page to the label and the current state of the localTask.
    $form['#title'] = (t('@title (@source to @target, @state)', array(
      '@title' => $localTask->label(),
      '@source' => $source,
      '@target' => $target,
      '@state' => $states[$localTask->getState()],
    )));

    $form = parent::form($form, $form_state);

    $form['info'] = array(
      '#type' => 'container',
      '#attributes' => array('class' => array('tmgmt-ui-localTask-info', 'clearfix')),
      '#weight' => 0,
    );

    // Check for label value and set for dynamically change.
    if ($form_state->getValue('label') && $form_state->getValue('label') == $localTask->label()) {
      $localTask->label = NULL;
      $localTask->label = $localTask->label();
      $form_state->setValue('label', $localTask->label());
    }

    $form['label']['widget'][0]['value']['#description'] = t('You can provide a label for this localTask in order to identify it easily later on. Or leave it empty to use default one.');
    $form['label']['#group'] = 'info';
    $form['label']['#prefix'] = '<div id="tmgmt-ui-label">';
    $form['label']['#suffix'] = '</div>';

    // Make the source and target language flexible by showing either a select
    // dropdown or the plain string (if preselected).
    if ($localTask->getSourceLangcode() || !$localTask->isSubmittable()) {
      $form['info']['source_language'] = array(
        '#title' => t('Source language'),
        '#type' =>  'item',
        '#markup' => isset($available['source_language'][$localTask->getSourceLangcode()]) ? $available['source_language'][$localTask->getSourceLangcode()] : '',
        '#prefix' => '<div id="tmgmt-ui-source-language" class="tmgmt-ui-source-language tmgmt-ui-info-item">',
        '#suffix' => '</div>',
        '#value' => $localTask->getSourceLangcode(),
      );
    }
    else {
      $form['info']['source_language'] = array(
        '#title' => t('Source language'),
        '#type' => 'select',
        '#options' => $available['source_language'],
        '#default_value' => $localTask->getSourceLangcode(),
        '#required' => TRUE,
        '#prefix' => '<div id="tmgmt-ui-source-language" class="tmgmt-ui-source-language tmgmt-ui-info-item">',
        '#suffix' => '</div>',
        '#ajax' => array(
          'callback' => array($this, 'ajaxLanguageSelect'),
        ),
      );
    }
    if (!$localTask->isSubmittable()) {
      $form['info']['target_language'] = array(
        '#title' => t('Target language'),
        '#type' => 'item',
        '#markup' => isset($available['target_language'][$localTask->getTargetLangcode()]) ? $available['target_language'][$localTask->getTargetLangcode()] : '',
        '#prefix' => '<div id="tmgmt-ui-target-language" class="tmgmt-ui-target-language tmgmt-ui-info-item">',
        '#suffix' => '</div>',
        '#value' => $localTask->getTargetLangcode(),
      );
    }
    else {
      $form['info']['target_language'] = array(
        '#title' => t('Target language'),
        '#type' => 'select',
        '#options' => $available['target_language'],
        '#default_value' => $localTask->getTargetLangcode(),
        '#required' => TRUE,
        '#prefix' => '<div id="tmgmt-ui-target-language" class="tmgmt-ui-target-language tmgmt-ui-info-item">',
        '#suffix' => '</div>',
        '#ajax' => array(
          'callback' => array($this, 'ajaxLanguageSelect'),
          'wrapper' => 'tmgmt-ui-target-language',
        ),
      );
    }

    // Display selected translator for already submitted localTasks.
    if (!$localTask->isSubmittable()) {
      $translators = tmgmt_translator_labels();
      $form['info']['translator'] = array(
        '#type' => 'item',
        '#title' => t('Translator'),
        '#markup' => isset($translators[$localTask->getTranslatorId()]) ? Html::escape($translators[$localTask->getTranslatorId()]) : t('Missing translator'),
        '#prefix' => '<div class="tmgmt-ui-translator tmgmt-ui-info-item">',
        '#suffix' => '</div>',
        '#value' => $localTask->getTranslatorId(),
      );
    }

    $form['info']['word_count'] = array(
      '#type' => 'item',
      '#title' => t('Total word count'),
      '#markup' => number_format($localTask->getWordCount()),
      '#prefix' => '<div class="tmgmt-ui-word-count tmgmt-ui-info-item">',
      '#suffix' => '</div>',
    );

    // Display created time only for localTasks that are not new anymore.
    if (!$localTask->isUnprocessed()) {
      $form['info']['created'] = array(
        '#type' => 'item',
        '#title' => t('Created'),
        '#markup' => format_date($localTask->getCreatedTime()),
        '#prefix' => '<div class="tmgmt-ui-created tmgmt-ui-info-item">',
        '#suffix' => '</div>',
        '#value' => $localTask->getCreatedTime(),
      );
    }

    if ($view = Views::getView('tmgmt_localTask_items')) {
      $form['localTask_items_wrapper'] = array(
        '#type' => 'details',
        '#title' => t('LocalTask items'),
        '#open' => FALSE,
        '#weight' => 10,
        '#prefix' => '<div class="tmgmt-ui-localTask-checkout-details">',
        '#suffix' => '</div>',
      );

      // Translation localTasks.
      $output = $view->preview($localTask->isSubmittable() ? 'checkout' : 'submitted', array($localTask->id()));
      $form['localTask_items_wrapper']['items'] = array(
        '#type' => 'markup',
        '#title' => $view->storage->label(),
        '#prefix' => '<div class="' . 'tmgmt-ui-localTask-items ' . ($localTask->isSubmittable() ? 'tmgmt-ui-localTask-submit' : 'tmgmt-ui-localTask-manage') . '">',
        'view' => ['#markup' => $this->renderer->render($output)],
        '#attributes' => array('class' => array('tmgmt-ui-localTask-items', $localTask->isSubmittable() ? 'tmgmt-ui-localTask-submit' : 'tmgmt-ui-localTask-manage')),
        '#suffix' => '</div>',
      );
    }

    // A Wrapper for a button and a table with all suggestions.
    $form['localTask_items_wrapper']['suggestions'] = array(
      '#type' => 'container',
      '#access' => $localTask->isSubmittable(),
    );

    // Button to load all translation suggestions with AJAX.
    $form['localTask_items_wrapper']['suggestions']['load'] = array(
      '#type' => 'submit',
      '#value' => t('Load suggestions'),
      '#submit' => array('::loadSuggestionsSubmit'),
      '#limit_validation_errors' => array(),
      '#attributes' => array(
        'class' => array('tmgmt-ui-localTask-suggestions-load'),
      ),
      '#ajax' => array(
        'callback' => '::ajaxLoadSuggestions',
        'wrapper' => 'tmgmt-ui-localTask-items-suggestions',
        'method' => 'replace',
        'effect' => 'fade',
      ),
    );

    $form['localTask_items_wrapper']['suggestions']['container'] = array(
      '#type' => 'container',
      '#prefix' => '<div id="tmgmt-ui-localTask-items-suggestions">',
      '#suffix' => '</div>',
    );

    // Create the suggestions table.
    $suggestions_table = array(
      '#type' => 'tableselect',
      '#header' => array(),
      '#options' => array(),
      '#multiple' => TRUE,
    );

    // If this is an AJAX-Request, load all related nodes and fill the table.
    if ($form_state->isRebuilding() && $form_state->get('rebuild_suggestions')) {
      $this->buildSuggestions($suggestions_table, $form_state);

      // A save button on bottom of the table is needed.
      $suggestions_table = array(
        'suggestions_table' => $suggestions_table,
        'suggestions_add' => array(
          '#type' => 'submit',
          '#value' => t('Add suggestions'),
          '#submit' => array('::addSuggestionsSubmit'),
          '#limit_validation_errors' => array(array('suggestions_table')),
          '#attributes' => array(
            'class' => array('tmgmt-ui-localTask-suggestions-add'),
          ),
          '#access' => !empty($suggestions_table['#options']),
        ),
      );
      $form['localTask_items_wrapper']['suggestions']['container']['suggestions_list'] = array(
        '#type' => 'details',
        '#title' => t('Suggestions'),
        '#prefix' => '<div id="tmgmt-ui-localTask-items-suggestions">',
        '#suffix' => '</div>',
        '#open' => FALSE,
      ) + $suggestions_table;
    }

    // Display the checkout settings form if the localTask can be checked out.
    if ($localTask->isSubmittable()) {

      $form['translator_wrapper'] = array(
        '#type' => 'fieldset',
        '#title' => t('Configure translator'),
        '#weight' => 20,
        '#prefix' => '<div id="tmgmt-ui-translator-wrapper">',
        '#suffix' => '</div>',
      );

      // Show a list of translators tagged by availability for the selected source
      // and target language combination.
      if (!$translators = tmgmt_translator_labels_flagged($localTask)) {
        drupal_set_message(t('There are no translators available. Before you can checkout you need to !configure at least one translator.', array('!configure' => \Drupal::l(t('configure'), Url::fromRoute('entity.tmgmt_translator.collection')))), 'warning');
      }
      $preselected_translator = $localTask->getTranslatorId() && isset($translators[$localTask->getTranslatorId()]) ? $localTask->getTranslatorId() : key($translators);
      $localTask->translator = $form_state->getValue('translator') ?: $preselected_translator;

      $form['translator_wrapper']['translator'] = array(
        '#type' => 'select',
        '#title' => t('Translator'),
        '#description' => t('The configured translator plugin that will process of the translation.'),
        '#options' => $translators,
        '#default_value' => $localTask->getTranslatorId(),
        '#required' => TRUE,
        '#ajax' => array(
          'callback' => array($this, 'ajaxTranslatorSelect'),
          'wrapper' => 'tmgmt-ui-translator-settings',
        ),
      );

      $settings = $this->checkoutSettingsForm($form_state, $localTask);
      if(!is_array($settings)){
        $settings = array();
      }
      $form['translator_wrapper']['settings'] = array(
          '#type' => 'details',
          '#title' => t('Checkout settings'),
          '#prefix' => '<div id="tmgmt-ui-translator-settings">',
          '#suffix' => '</div>',
          '#tree' => TRUE,
          '#open' => TRUE,
        ) + $settings;
    }
    // Otherwise display the checkout info.
    elseif ($localTask->getTranslatorId()) {

      $form['translator_wrapper'] = array(
        '#type' => 'details',
        '#title' => t('Translator information'),
        '#open' => FALSE,
        '#weight' => 20,
      );

      $form['translator_wrapper']['info'] = $this->checkoutInfo($localTask);
    }

    if (!$localTask->isSubmittable() && empty($form['translator_wrapper']['info'])) {
      $form['translator_wrapper']['info'] = array(
        '#type' => 'markup',
        '#markup' => t('The translator does not provide any information.'),
      );
    }

    $form['clearfix'] = array(
      '#markup' => '<div class="clearfix"></div>',
      '#weight' => 45,
    );

    if ($view = Views::getView('tmgmt_localTask_messages')) {
      $form['messages'] = array(
        '#type' => 'details',
        '#title' => $view->storage->label(),
        '#open' => FALSE,
        '#weight' => 50,
      );
      $output = $view->preview('embed', array($localTask->id()));
      $form['messages']['view']['#markup'] = drupal_render($output);
    }

    $form['#attached']['library'][] = 'tmgmt/admin';*/
    return $form;
  }

  protected function actions(array $form, FormStateInterface $form_state) {
    $localTask = $this->entity;

    $actions['save'] = array(
      '#type' => 'submit',
      '#value' => t('Save task'),
      '#submit' => array('::submitForm', '::save'),
      '#weight' => 5,
      '#button_type' => 'primary',
    );

    if (!$localTask->isNew()) {
      $actions['delete'] = array(
        '#type' => 'submit',
        '#value' => t('Delete'),
        '#submit' => array('tmgmt_submit_redirect'),
        '#redirect' => 'admin/tmgmt/localTasks/' . $localTask->id() . '/delete',
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
    $localTask = $this->buildEntity($form, $form_state);
    // Load the selected translator.
    $translator = $localTask->getTranslator();
    // Check translator availability.
    if (!empty($translator)) {
      if (!$translator->isAvailable()) {
        $form_state->setErrorByName('translator', $translator->getNotAvailableReason());
      }
      elseif (!$translator->canTranslate($localTask)) {
        $form_state->setErrorByName('translator', $translator->getNotCanTranslateReason($localTask));
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildEntity(array $form, FormStateInterface $form_state) {
    $localTask = parent::buildEntity($form, $form_state);

    $translator = $localTask->getTranslator();
    if (!empty($translator)) {
      // If requested custom localTask settings handling, copy values from original localTask.
      if ($translator->hasCustomSettingsHandling()) {
        $original_localTask = entity_load_unchanged('tmgmt_localTask', $localTask->id());
        $localTask->settings = $original_localTask->settings;
      }
    }
    // Make sure that we always store a label as it can be a slow operation to
    // generate the default label.
    if (empty($localTask->label)) {
      $localTask->label = $localTask->label();
    }
    return $localTask;
  }

  /**
   * Overrides Drupal\Core\Entity\EntityForm::save().
   */
  public function save(array $form, FormStateInterface $form_state) {
    parent::save($form, $form_state);

    // Everything below this line is only invoked if the 'Submit to translator'
    // button was clicked.
    if ($form_state->getTriggeringElement()['#value'] == $form['actions']['submit']['#value']) {
      if (!tmgmt_localTask_request_translation($this->entity)) {
        // Don't redirect the user if the translation request failed but retain
        // existing destination parameters so we can redirect once the request
        // finished successfully.
        unset($_GET['destination']);
      }
      else if ($redirect = tmgmt_redirect_queue_dequeue()) {
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
    $form_state->setRedirectUrl($this->entity->urlInfo('delete-form'));
  }
}
