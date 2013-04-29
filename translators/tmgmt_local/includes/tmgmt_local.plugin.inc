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

    // Create task items.
    foreach ($job->getItems() as $item) {
      $local_task->addTaskItem($item);
    }

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
