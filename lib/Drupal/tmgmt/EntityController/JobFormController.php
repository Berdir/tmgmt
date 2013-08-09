<?php

/**
 * @file
 * Contains \Drupal\tmgmt\EntityController\JobFormController.
 */

namespace Drupal\tmgmt\EntityController;

use Drupal\tmgmt\Plugin\Core\Entity\Job;

/**
 * Form controller for the job edit forms.
 *
 * @ingroup tmgmt_job
 */
class JobFormController extends TmgmtFormControllerBase {

  /**
   * Overrides Drupal\Core\Entity\EntityFormController::form().
   */
  public function form(array $form, array &$form_state) {
    $form = parent::form($form, $form_state);
    $job = $this->entity;
    // Handle source language.
    $available['source_language'] = tmgmt_available_languages();
    $job->source_language = isset($form_state['values']['source_language']) ? $form_state['values']['source_language'] : $job->source_language;

    // Handle target language.
    $available['target_language'] = tmgmt_available_languages();
    $job->target_language = isset($form_state['values']['target_language']) ? $form_state['values']['target_language'] : $job->target_language;

    // Remove impossible combinations so we don't end up with the same source and
    // target language in the dropdowns.
    foreach (array('source_language' => 'target_language', 'target_language' => 'source_language') as $key => $opposite) {
      if (!empty($job->{$key})) {
        unset($available[$opposite][$job->{$key}]);
      }
    }

    $source = tmgmt_language_label($job->source_language) ?: '?';
    if (empty($job->target_language)) {
      $job->target_language = key($available['target_language']);
      $target = '?';
    }
    else {
      $target = tmgmt_language_label($job->target_language);
    }

    $states = tmgmt_job_states();
    // Set the title of the page to the label and the current state of the job.
    drupal_set_title(t('@title (@source to @target, @state)', array(
      '@title' => $job->label(),
      '@source' => $source,
      '@target' => $target,
      '@state' => $states[$job->state],
    )));

    $form['info'] = array(
      '#type' => 'container',
      '#attributes' => array('class' => array('tmgmt-ui-job-info', 'clearfix')),
      '#weight' => 0,
    );

    // Check for label value and set for dynamically change.
    if (isset($form_state['values']['label']) && $form_state['values']['label'] == $job->label()) {
      $job->label = NULL;
      $form_state['values']['label'] = $job->label = $job->label();
    }
    $form['info']['label'] = array(
      '#type' => 'textfield',
      '#title' => t('Label'),
      '#description' => t('You can provide a label for this job in order to identify it easily later on. Or leave it empty to use default one.'),
      '#default_value' => $job->label(),
      '#prefix' => '<div id="tmgmt-ui-label">',
      '#suffix' => '</div>',
    );

    // Make the source and target language flexible by showing either a select
    // dropdown or the plain string (if preselected).
    if (!empty($job->source_language) || !$job->isSubmittable()) {
      $form['info']['source_language'] = array(
        '#title' => t('Source language'),
        '#type' =>  'item',
        '#markup' => isset($available['source_language'][$job->source_language]) ? $available['source_language'][$job->source_language] : '',
        '#prefix' => '<div id="tmgmt-ui-source-language" class="tmgmt-ui-source-language tmgmt-ui-info-item">',
        '#suffix' => '</div>',
        '#value' => $job->source_language,
      );
    }
    else {
      $form['info']['source_language'] = array(
        '#title' => t('Source language'),
        '#type' => 'select',
        '#options' => $available['source_language'],
        '#default_value' => $job->source_language,
        '#required' => TRUE,
        '#prefix' => '<div id="tmgmt-ui-source-language" class="tmgmt-ui-source-language tmgmt-ui-info-item">',
        '#suffix' => '</div>',
        '#ajax' => array(
          'callback' => 'tmgmt_ui_ajax_callback_language_select',
        ),
      );
    }
    if (!$job->isSubmittable()) {
      $form['info']['target_language'] = array(
        '#title' => t('Target language'),
        '#type' => 'item',
        '#markup' => isset($available['target_language'][$job->target_language]) ? $available['target_language'][$job->target_language] : '',
        '#prefix' => '<div id="tmgmt-ui-target-language" class="tmgmt-ui-target-language tmgmt-ui-info-item">',
        '#suffix' => '</div>',
        '#value' => $job->target_language,
      );
    }
    else {
      $form['info']['target_language'] = array(
        '#title' => t('Target language'),
        '#type' => 'select',
        '#options' => $available['target_language'],
        '#default_value' => $job->target_language,
        '#required' => TRUE,
        '#prefix' => '<div id="tmgmt-ui-target-language" class="tmgmt-ui-target-language tmgmt-ui-info-item">',
        '#suffix' => '</div>',
        '#ajax' => array(
          'callback' => 'tmgmt_ui_ajax_callback_language_select',
          'wrapper' => 'tmgmt-ui-target-language',
        ),
      );
    }

    // Display selected translator for already submitted jobs.
    if (!$job->isSubmittable()) {
      $translators = tmgmt_translator_labels();
      $form['info']['translator'] = array(
        '#type' => 'item',
        '#title' => t('Translator'),
        '#markup' => isset($translators[$job->translator]) ? check_plain($translators[$job->translator]) : t('Missing translator'),
        '#prefix' => '<div class="tmgmt-ui-translator tmgmt-ui-info-item">',
        '#suffix' => '</div>',
        '#value' => $job->translator,
      );
    }

    $form['info']['word_count'] = array(
      '#type' => 'item',
      '#title' => t('Total word count'),
      '#markup' => number_format($job->getWordCount()),
      '#prefix' => '<div class="tmgmt-ui-word-count tmgmt-ui-info-item">',
      '#suffix' => '</div>',
    );

    // Display created time only for jobs that are not new anymore.
    if (!$job->isUnprocessed()) {
      $form['info']['created'] = array(
        '#type' => 'item',
        '#title' => t('Created'),
        '#markup' => format_date($job->created),
        '#prefix' => '<div class="tmgmt-ui-created tmgmt-ui-info-item">',
        '#suffix' => '</div>',
        '#value' => $job->created,
      );
    }

    // Wrapper on the right side which holds the jobs and the suggestions.
    $form['translator_wrapper_right'] = array(
      '#type' => 'container',
      '#prefix' => '<div class="tmgmt-ui-translator-wrapper-right">',
      '#suffix' => '</div>',
      '#weight' => 25,
    );
    if ($view =  entity_load('view', 'tmgmt_ui_job_items')) {
      // Translation jobs.
      $form['translator_wrapper_right']['items'] = array(
        '#type' => 'item',
        '#title' => $view->get_title(),
        '#prefix' => '<div class="' . 'tmgmt-ui-job-items ' . ($job->isSubmittable() ? 'tmgmt-ui-job-submit' : 'tmgmt-ui-job-manage') . '">',
        '#markup' => $view->preview($job->isSubmittable() ? 'submit' : 'block', array($job->tjid)),
        '#attributes' => array('class' => array('tmgmt-ui-job-items', $job->isSubmittable() ? 'tmgmt-ui-job-submit' : 'tmgmt-ui-job-manage')),
        '#suffix' => '</div>',
        '#weight' => $job->isSubmittable() ? 30 : 10,
      );
    }

    // A Wrapper for a button and a table with all suggestions.
    $form['translator_wrapper_right']['suggestions'] = array(
      '#type' => 'fieldset',
      '#title' => '',
      '#prefix' => '<div class="tmgmt-ui-job-items-suggestions">',
      '#suffix' => '</div>',
      '#weight' => 35,
      '#access' => $job->isSubmittable(),
    );

    // Button to load all translation suggestions with AJAX.
    $form['translator_wrapper_right']['suggestions']['load'] = array(
      '#type' => 'submit',
      '#value' => t('Load suggestions'),
      '#submit' => array('tmgmt_ui_ajax_submit_load_suggestions'),
      '#limit_validation_errors' => array(),
      '#attributes' => array(
        'class' => array('tmgmt-ui-job-suggestions-load')
      ),
      '#ajax' => array(
        'callback' => 'tmgmt_ui_ajax_callback_load_suggestions',
        'wrapper' => 'tmgmt-ui-job-items-suggestions',
        'method' => 'replace',
        'effect' => 'fade',
      ),
      '#weight' => 10,
    );

    // Create the suggestions table.
    $suggestions_table = array(
      '#type' => 'tableselect',
      '#header' => array(),
      '#options' => array(),
      '#multiple' => TRUE,
      '#weight' => 30,
    );

    // If this is an AJAX-Request, load all related nodes and fill the table.
    if ($form_state['rebuild'] && !empty($form_state['rebuild_suggestions'])) {
      _tmgmt_ui_translation_suggestions($suggestions_table, $form_state);

      // A save button on bottom of the table is needed.
      $suggestions_table = array(
        'suggestions_table' => $suggestions_table,
        'suggestions_add' => array(
          '#type' => 'submit',
          '#value' => t('Add suggestions'),
          '#submit' => array('tmgmt_ui_submit_add_suggestions'),
          '#limit_validation_errors' => array(array('suggestions_table')),
          '#attributes' => array(
            'class' => array('tmgmt-ui-job-suggestions-add')
          ),
          '#weight' => 40,
        ),
      );
    }
    $form['translator_wrapper_right']['suggestions']['suggestions_list'] = array(
        '#type' => 'container',
        '#prefix' => '<div id="tmgmt-ui-job-items-suggestions" class="tmgmt-ui-job-items-suggestions">',
        '#suffix' => '</div>',
        '#weight' => 20,
    ) + $suggestions_table;


    $form['translator_wrapper'] = array(
      '#type' => 'container',
      '#prefix' => '<div class="tmgmt-ui-translator-wrapper">',
      '#suffix' => '</div>',
      '#weight' => 20,
    );

    // Display the checkout settings form if the job can be checked out.
    if ($job->isSubmittable()) {
      // Show a list of translators tagged by availability for the selected source
      // and target language combination.
      if (!$translators = tmgmt_translator_labels_flagged($job)) {
        drupal_set_message(t('There are no translators available. Before you can checkout you need to !configure at least one translator.', array('!configure' => l(t('configure'), 'admin/config/regional/tmgmt_translator'))), 'warning');
      }
      $preselected_translator = !empty($job->translator) && isset($translators[$job->translator]) ? $job->translator : key($translators);
      $job->translator = isset($form_state['values']['translator']) ? $form_state['values']['translator'] : $preselected_translator;

      $form['translator_wrapper']['translator'] = array(
        '#type' => 'select',
        '#title' => t('Translator'),
        '#description' => t('The configured translator plugin that will process of the translation.'),
        '#options' => $translators,
        '#default_value' => $job->translator,
        '#required' => TRUE,
        '#ajax' => array(
          'callback' => 'tmgmt_ui_ajax_callback_translator_select',
          'wrapper' => 'tmgmt-ui-translator-settings',
        ),
      );

      $settings = $this->checkoutSettingsForm($form_state, $job);
      if(!is_array($settings)){
        $settings = array();
      }
      $form['translator_wrapper']['settings'] = array(
          '#type' => 'fieldset',
          '#title' => t('Checkout settings'),
          '#prefix' => '<div id="tmgmt-ui-translator-settings">',
          '#suffix' => '</div>',
          '#tree' => TRUE,
        ) + $settings;
    }
    // Otherwise display the checkout info.
    elseif (!empty($job->translator)) {
      $form['translator_wrapper']['info'] = $this->checkoutInfo($job);
    }

    $form['clearfix'] = array(
      '#markup' => '<div class="clearfix"></div>',
      '#weight' => 45,
    );

    if ($output = tmgmt_ui_embed_view('tmgmt_ui_job_messages', 'block', array($job->tjid))) {
      $form['messages'] = array(
        '#type' => 'fieldset',
        '#title' => t('Messages'),
        '#collapsible' => TRUE,
        '#weight' => 50,
      );
      $form['messages']['view'] = array(
        '#type' => 'item',
        '#markup' => $output,
      );
    }

    $form['#attached']['css'][] = drupal_get_path('module', 'tmgmt_ui') . '/css/tmgmt_ui.admin.css';
    return $form;
  }

  protected function actions(array $form, array &$form_state) {
    $job = $this->entity;

    $actions['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Save job'),
      '#validate' => array(
        array($this, 'validate'),
      ),
      '#submit' => array(
        array($this, 'submit'),
        array($this, 'save'),
      ),
    );
    if ($job->access('submit')) {
      $actions['checkout'] = array(
        '#type' => 'submit',
        '#value' => tmgmt_ui_redirect_queue_count() == 0 ? t('Submit to translator') : t('Submit to translator and continue'),
        '#access' => $job->isSubmittable(),
        '#disabled' => empty($job->translator),
        '#validate' => array(
          array($this, 'validate'),
        ),
        '#submit' => array(
          array($this, 'submit'),
          array($this, 'save'),
        ),
      );
    }
    if (!$job->isNew()) {
      $actions['delete'] = array(
        '#type' => 'submit',
        '#value' => t('Delete'),
        '#submit' => array('tmgmt_ui_submit_redirect'),
        '#redirect' => 'admin/tmgmt/jobs/' . $job->id() . '/delete',
        // Don't run validations, so the user can always delete the job.
        '#limit_validation_errors' => array(),
      );
    }
    // Only show the 'Cancel' button if the job has been submitted to the
    // translator.
    $actions['cancel'] = array(
      '#type' => 'button',
      '#value' => t('Cancel'),
      '#submit' => array('tmgmt_ui_submit_redirect'),
      '#redirect' => 'admin/tmgmt/jobs',
      '#access' => $job->isActive(),
    );
    return $actions;
  }


  /**
   * {@inheritdoc}
   */
  public function validate(array $form, array &$form_state) {
    parent::validate($form, $form_state);
    $job = $this->buildEntity($form, $form_state);
    // Load the selected translator.
    $translator = tmgmt_translator_load($job->translator);
    // Check translator availability.
    if (!$translator->isAvailable()) {
      form_set_error('translator', $translator->getNotAvailableReason());
    }
    elseif (!$translator->canTranslate($job)) {
      form_set_error('translator', $translator->getNotCanTranslateReason($job));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function buildEntity(array $form, array &$form_state) {
    $job = parent::buildEntity($form, $form_state);
    // If requested custom job settings handling, copy values from original job.
    if (tmgmt_job_settings_custom_handling($job->getTranslator())) {
      $original_job = entity_load_unchanged('tmgmt_job', $job->tjid);
      $job->settings = $original_job->settings;
    }

    // Make sure that we always store a label as it can be a slow operation to
    // generate the default label.
    if (empty($job->label)) {
      $job->label = $job->label();
    }
    return $job;
  }

  /**
   * Overrides Drupal\Core\Entity\EntityFormController::save().
   */
  public function save(array $form, array &$form_state) {
    $entity = $this->entity;
    $status = $entity->save();

    // Per default we want to redirect the user to the overview.
    $form_state['redirect'] = 'admin/tmgmt';
    // Everything below this line is only invoked if the 'Submit to translator'
    // button was clicked.
    if ($form_state['triggering_element']['#value'] == $form['actions']['checkout']['#value']) {
      if (!tmgmt_ui_job_request_translation($entity)) {
        // Don't redirect the user if the translation request failed but retain
        // existing destination parameters so we can redirect once the request
        // finished successfully.
        // @todo: Change this to stay on the form in case of an error instead of
        // doing a redirect.
        $form_state['redirect'] = array(current_path(), array('query' => drupal_get_destination()));
        unset($_GET['destination']);
      }
      else if ($redirect = tmgmt_ui_redirect_queue_dequeue()) {
        // Proceed to the next redirect queue item, if there is one.
        $form_state['redirect'] = $redirect;
      }
      else {
        // Proceed to the defined destination if there is one.
        $form_state['redirect'] = tmgmt_ui_redirect_queue_destination($form_state['redirect']);
      }
    }
  }

  /**
   * Helper function for retrieving the job settings form.
   *
   * @todo Make use of the response object here.
   */
  function checkoutSettingsForm(&$form_state, Job $job) {
    $form = array();
    $translator = $job->getTranslator();
    if (!$translator) {
      return $form;
    }
    if (!$translator->isAvailable()) {
      $form['#description'] = filter_xss($job->getTranslator()->getNotAvailableReason());
    }
    // @todo: if the target language is not defined, the check will not work if the first language in the list is not available.
    elseif ($job->target_language && !$translator->canTranslate($job)) {
      $form['#description'] = filter_xss($job->getTranslator()->getNotCanTranslateReason($job));
    }
    else {
      $plugin_ui = $this->translatorManager->createUIInstance($translator->plugin);
      $form = $plugin_ui->checkoutSettingsForm($form, $form_state, $job);
    }
    return $form;
  }

  /**
   * Helper function for retrieving the rendered job checkout information.
   */
  function checkoutInfo(Job $job) {
    $translator = $job->getTranslator();
    // The translator might have been disabled or removed.
    if (!$translator) {
      return array();
    }
    $plugin_ui = $this->translatorManager->createUIInstance($translator->plugin);
    return $plugin_ui->checkoutInfo($job);
  }

  /**
   * {@inheritdoc}
   */
  public function delete(array $form, array &$form_state) {
    $entity = $this->entity;
    $form_state['redirect'] = 'admin/tmgmt/jobs/' . $entity->id() . '/delete';
  }

}
