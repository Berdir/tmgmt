<?php

/**
 * @file
 * Contains Drupal\tmgmt_file\FileTranslatorPluginController.
 */

namespace Drupal\tmgmt_file;

use Drupal\tmgmt\DefaultTranslatorPluginController;
use Drupal\tmgmt\Plugin\Core\Entity\Job;
use Drupal\tmgmt\Plugin\Core\Entity\Translator;

/**
 * File translator plugin controller.
 */
class FileTranslatorPluginController extends DefaultTranslatorPluginController {

  /**
   * Implements TranslatorPluginControllerInterface::canTranslate().
   */
  public function canTranslate(Translator $translator, Job $job) {
    // Anything can be exported.
    return TRUE;
  }

  /**
   * Implements TranslatorPluginControllerInterface::requestTranslation().
   */
  public function requestTranslation(Job $job) {
    $name = "JobID" . $job->tjid . '_' . $job->source_language . '_' . $job->target_language;

    $export = tmgmt_file_format_controller($job->getSetting('export_format'));

    $path = $job->getSetting('scheme') . '://tmgmt_file/' . $name . '.' .  $job->getSetting('export_format');
    $dirname = dirname($path);
    if (file_prepare_directory($dirname, FILE_CREATE_DIRECTORY)) {
      $file = file_save_data($export->export($job), $path);
      file_usage()->add($file, 'tmgmt_file', 'tmgmt_job', $job->tjid);
      $job->submitted('Exported file can be downloaded <a href="!link">here</a>.', array('!link' => file_create_url($path)));
    }
  }

  /**
   * Implements TranslatorPluginControllerInterface::hasCheckoutSettings().
   */
  public function hasCheckoutSettings(Job $job) {
    return $job->getTranslator()->getSetting('allow_override');
  }

  /**
   * Implements TranslatorPluginControllerInterface::defaultSettings().
   */
  public function defaultSettings() {
    return array(
      'export_format' => 'xlf',
      'allow_override' => TRUE,
      'scheme' => 'public',
    );
  }

}
