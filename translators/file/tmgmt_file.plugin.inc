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

    $export = tmgmt_file_export_factory($job->getSetting('export_format'));

    // @todo: Make this configurable
    $path = 'public://tmgmt_file/' . $name . '.' .  $export->getFileExtension();
    if (file_prepare_directory(dirname($path), FILE_CREATE_DIRECTORY)) {
      file_put_contents($path, $export->export($job));
      $job->submitted('Exported file can be downloaded <a href="!link">here</a>.', array('!link' => file_create_url($path)));
    }
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::checkoutInfo().
   */
  public function hasCheckoutSettings(TMGMTJob $job) {
    return $job->getTranslator()->getSetting('allow_override');
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::defaultSettings().
   */
  public function defaultSettings() {
    return array(
      'export_format' => 'xliff',
      'allow_override' => TRUE,
    );
  }

}
