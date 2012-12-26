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
    // The translation job has been successfully submitted.
    $job->submitted();

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

    // @todo - also connect this task with all job items from this job.
  }

  /**
   * Overrides TMGMTDefaultTranslatorPluginController::getSupportedTargetLanguages().
   */
  public function getSupportedTargetLanguages(TMGMTTranslator $translator, $language) {
    return drupal_map_assoc(tmgmt_local_translation_capabilities($language));
  }

  /**
   * Overrides TMGMTDefaultTranslatorPluginController::hasCheckoutSettings().
   */
  public function hasCheckoutSettings(TMGMTJob $job) {
    return FALSE;
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
    if ($translators = tmgmt_local_translators()) {
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

    $uid = $job->getSetting('translator');
    if ($translator = user_load($uid)) {
      $form['#description'] = t('Translation job is assigned to %name.', array('%name' => $translator->name));
    }
    else {
      $form['#description'] = t('Translation job is not assigned to any translator.');
    }
    return $form;
  }

}
