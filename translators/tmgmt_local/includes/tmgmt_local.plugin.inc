<?php

/**
 * @file
 * Provides the user translator plugin controller.
 */

/**
 * Local translator plugin controller.
 */
class TMGMTLocalTranslatorPluginController extends TMGMTDefaultTranslatorPluginController {

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::requestTranslation().
   */
  public function requestTranslation(TMGMTJob $job) {
    $tuid = $job->getSetting('translator');

    // Create local task for this job.
    $local_task = tmgmt_local_task_create(array(
      'uid' => $job->uid,
      'tuid' => $tuid,
      'tjid' => $job->tjid,
      'title' => t('Task for !label', array('!label' => $job->defaultLabel())),
    ));
    // If we have translator then switch to pending state.
    if ($tuid) {
      $local_task->status = TMGMT_LOCAL_TASK_STATUS_PENDING;
    }
    $local_task->save();

    // The translation job has been successfully submitted.
    $job->submitted();
  }

  /**
   * Overrides TMGMTDefaultTranslatorPluginController::getSupportedTargetLanguages().
   */
  public function getSupportedTargetLanguages(TMGMTTranslator $translator, $source_language) {
    $languages = drupal_map_assoc(tmgmt_local_translation_capabilities($source_language));
    if ($translator->getSetting('allow_all')) {
      $languages += parent::getSupportedTargetLanguages($translator, $source_language);
    }
    return $languages;
  }

}

/**
 * Local translator plugin UI controller.
 */
class TMGMTLocalTranslatorUIController extends TMGMTDefaultTranslatorUIController {

  /**
   * Overrides TMGMTDefaultTranslatorPluginController::checkoutSettingsForm().
   */
  public function checkoutSettingsForm($form, &$form_state, TMGMTJob $job) {
    if ($translators = tmgmt_local_translators($job->source_language, $job->target_language)) {
      $form['translator'] = array(
        '#title' => t('Select translator for this job'),
        '#type' => 'select',
        '#options' => array('' => t('Select user')) + $translators,
        '#default_value' => $job->getSetting('translator'),
      );
    }
    else {
      $form['message'] = array(
        '#markup' => t('There are no translators available.'),
      );
    }

    return $form;
  }

  /**
   * Overrides TMGMTTranslatorUIControllerInterface::checkoutInfo().
   */
  public function checkoutInfo(TMGMTJob $job) {
    $label = $job->getTranslator()->label();
    $form['#title'] = t('@translator translation job information', array('@translator' => $label));
    $form['#type'] = 'fieldset';

    $tuid = $job->getSetting('translator');
    if ($tuid && $translator = user_load($tuid)) {
      $form['job_status'] = array(
        '#type' => 'item',
        '#title' => t('Job status'),
        '#markup' => t('Translation job is assigned to %name.', array('%name' => entity_label('user', $translator))),
      );
    }
    else {
      $form['job_status'] = array(
        '#type' => 'item',
        '#title' => t('Job status'),
        '#markup' => t('Translation job is not assigned to any translator.'),
      );
    }

    if ($job->getSetting('job_comment')) {
      $form['job_comment'] = array(
        '#type' => 'item',
        '#title' => t('Job comment'),
        '#markup' => check_plain($job->getSetting('job_comment')),
      );
    }

    return $form;
  }

  public function pluginSettingsForm($form, &$form_state, TMGMTTranslator $translator, $busy = FALSE) {
    $form['allow_all'] = array(
      '#title' => t('Allow translations for enabled languages even if no translator has the necessary capabilities'),
      '#type' => 'checkbox',
      '#default_value' => $translator->getSetting('allow_all'),
    );
    return $form;
  }

}
