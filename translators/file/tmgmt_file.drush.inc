<?php

/**
 * @file
 * Drush integration.
 */

/**
 * Implements hook_drush_command().
 */
function tmgmt_file_drush_command() {
  $items = array();

  $items['tmgmt_translate_import'] = array(
    'description' => 'Import XLIFF translation files',
    'arguments' => array(
      'name'    => 'Directory path.',
    ),
    'aliases' => array('tmti'),
  );

  return $items;
}

/**
 * Import XLIFF files from a directory.
 */
function drush_tmgmt_file_tmgmt_translate_import($dir = NULL) {
  if (!$dir) {
    return drush_set_error(dt('You need to provide a directory path.'));
  }

  if (!file_exists($dir)) {
    return drush_set_error(dt('The directory does not exists or is not accessible.'));
  }

  drush_log('Scanning dir ' . $dir, 'success');
  $files = file_scan_directory($dir, '/.*\.xlf$/');
  $controller = tmgmt_file_format_controller('xlf');
  foreach ($files as $path => $info) {
    $job = $controller->validateImport($path);
    if (empty($job)) {
      drush_log('Job id ' . $info->name . ' does not exists', 'error');
      continue;
    }
    drush_log('Loading job id ' . $job->tjid . ' from file ' . $info->name, 'success');
    try {
      // Validation successful, start import.
      $job->addTranslatedData($controller->import($path));
      drush_log(dt('Successfully imported file ' . $info->name), 'success');
    }
    catch (Exception $e) {
      drush_log(dt('Failed importing file ' . $info->name . ': ' . $e->getMessage()), 'error');
    }
  }
}
?>
