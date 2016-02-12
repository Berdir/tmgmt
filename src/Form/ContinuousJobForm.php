<?php

/**
 * @file
 * Contains \Drupal\tmgmt\Form\ContinuousJobForm.
 */

namespace Drupal\tmgmt\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Url;
use Drupal\tmgmt\Entity\Job;
use Drupal\views\Views;

/**
 * Form controller for the job edit forms.
 *
 * @ingroup tmgmt_job
 */
class ContinuousJobForm extends JobForm {

  /**
   * @var \Drupal\tmgmt\JobInterface
   */
  protected $entity;

  /**
   * Overrides Drupal\Core\Entity\EntityForm::form().
   */
  public function form(array $form, FormStateInterface $form_state) {

    $job = $this->entity;
    // Handle source language.
    $available['source_language'] = tmgmt_available_languages();

    // Handle target language.
    $available['target_language'] = tmgmt_available_languages();

    $this->entity->set('job_type', Job::TYPE_CONTINUOUS);

    $form = parent::form($form, $form_state);
    // Set the title of the page to the label and the current state of the job.
    $form['#title'] = (t('@title', array(
      '@title' => 'New Continuous Job',
    )));

    $form['label']['widget'][0]['value']['#description'] = t('You need to provide a label for this job in order to identify it later on.');
    $form['label']['widget'][0]['value']['#required'] = TRUE;

    // Make the source and target language flexible by showing either a select
    // dropdown or the plain string (if preselected).
    $form['info']['source_language'] = array(
      '#title' => t('Source language'),
      '#type' => 'select',
      '#options' => $available['source_language'],
      '#default_value' => $job->getSourceLangcode(),
      '#required' => TRUE,
      '#prefix' => '<div id="tmgmt-ui-source-language" class="tmgmt-ui-source-language tmgmt-ui-info-item">',
      '#suffix' => '</div>',
      '#ajax' => array(
        'callback' => array($this, 'ajaxLanguageSelect'),
      ),
    );

    $form['info']['target_language'] = array(
      '#title' => t('Target language'),
      '#type' => 'select',
      '#options' => $available['target_language'],
      '#default_value' => $job->getTargetLangcode(),
      '#required' => TRUE,
      '#prefix' => '<div id="tmgmt-ui-target-language" class="tmgmt-ui-target-language tmgmt-ui-info-item">',
      '#suffix' => '</div>',
      '#ajax' => array(
        'callback' => array($this, 'ajaxLanguageSelect'),
        'wrapper' => 'tmgmt-ui-target-language',
      ),
    );

    return $form;
  }

  protected function actions(array $form, FormStateInterface $form_state) {
    $actions['save'] = array(
      '#type' => 'submit',
      '#value' => t('Save job'),
      '#submit' => array('::submitForm','::save'),
      '#weight' => 5,
      '#button_type' => 'primary',
    );
    return $actions;
  }

  /**
   * Overrides Drupal\Core\Entity\EntityForm::save().
   */
  public function save(array $form, FormStateInterface $form_state) {
    parent::save($form, $form_state);
    // Per default we want to redirect the user to the overview.
    $form_state->setRedirect('view.tmgmt_continuous_job_overview.page_1');
  }

}
