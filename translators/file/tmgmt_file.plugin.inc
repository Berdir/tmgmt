<?php

/**
 * @file
 * Provides the file translator plugin controller.
 */

/**
 * File translator plugin controller.
 */
class TMGMTFileTranslatorPluginController extends TMGMTDefaultTranslatorPluginController {

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::canTranslate().
   */
  function canTranslate(TMGMTJob $job) {
    // Anything can be exported.
    return TRUE;
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::requestTranslation().
   */
  function requestTranslation(TMGMTJob $job) {
    $name = "JobID" . $job->tjid;

    $export = tmgmt_file_format_controller($job->getSetting('export_format'));

    // @todo: Make the path configurable.
    $path = 'public://tmgmt_file/' . $name . '.' .  $job->getSetting('export_format');
    if (file_prepare_directory(dirname($path), FILE_CREATE_DIRECTORY)) {
      file_put_contents($path, $export->export($job));
      $job->submitted('Exported file can be downloaded <a href="!link">here</a>.', array('!link' => file_create_url($path)));
    }
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::hasCheckoutSettings().
   */
  public function hasCheckoutSettings(TMGMTJob $job) {
    return $job->getTranslator()->getSetting('allow_override');
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::defaultSettings().
   */
  public function defaultSettings() {
    return array(
      'export_format' => 'xlf',
      'allow_override' => TRUE,
    );
  }

}
