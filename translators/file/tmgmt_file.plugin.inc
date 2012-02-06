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
    if ($job->getSetting('export_format') == 'html') {

      $items = array();
      foreach ($job->getItems() as $item) {
        $items[$item->tjiid] = array_filter(tmgmt_flatten_data($item->getSourceData()), '_tmgmt_filter_data');
      }

      $extension = 'html';
      $file_content = theme("tmgmt_file_html_template", array(
          'tjid' => $job->tjid,
          'source_language' => $job->source_language,
          'target_language' => $job->target_language,
          'items' => $items,
      ));
    }
    else if ($job->getSetting('export_format') == 'xliff') {
      $extension = 'xlf';
      $xliff = simplexml_load_file(drupal_get_path('module', 'tmgmt_file') . '/template.xlf');

      if (!$xliff) {
        $job->rejected('Failed to create xliff export.');
        return;
      }

      /* @var $xliff SimpleXMLElement */

      // Set meta-data.
      $xliff->file['source-language'] = $job->source_language;
      $xliff->file['target-language'] = $job->target_language;
      $xliff->file['original'] = 'TMGMT Translation Job #' . $job->tjid;
      // Date needs to be in ISO-8601 UTC
      $xliff->file->header->{'phase-group'}->phase['date'] = date('Y-m-d\Th:m:i\Z');
      $xliff->file->header->{'phase-group'}->phase['job-id'] = $job->tjid;

      $group_id = 0;
      foreach ($job->getItems() as $item) {
        /* @var $item TMGMTJobItem */

        $xliff->file->body->group[$group_id]['id'] = $item->tjiid;
        $group = $xliff->file->body->group[$group_id];
        $group->note = $item->getSourceLabel();

        // @todo: Group data instead of flattening it.
        $data = array_filter(tmgmt_flatten_data($item->getSourceData()), '_tmgmt_filter_data');
        $unit_id = 0;
        foreach ($data as $id => $value) {
          $group->{'trans-unit'}[$unit_id]['id'] = $item->item_id . '][' . $id;

          $group->{'trans-unit'}[$unit_id]->source = $value['#text'];
          $group->{'trans-unit'}[$unit_id]->source['xml:lang'] = $job->source_language;
          $group->{'trans-unit'}[$unit_id]->target = $value['#text'];
          $group->{'trans-unit'}[$unit_id]->target['xml:lang'] = $job->target_language;

          if (isset($value['#label'])) {
            $group->{'trans-unit'}[$unit_id]->note = $value['#label'];
          }
          $unit_id++;
        }
        $group_id++;
      }

      $file_content = $xliff->asXML();
    }

    // @todo: Make this configurable
    $path = 'public://tmgmt_file/' . $name . '.' .  $extension;
    if (file_prepare_directory(dirname($path), FILE_CREATE_DIRECTORY)) {
      file_put_contents($path, $file_content);
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
