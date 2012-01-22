<?php

/**
 * @file
 * Provides the user translator plugin controller.
 */
class TMGMTHTMLTranslator extends TMGMTDefaultTranslatorPluginController {

  /**
   * It can 'translate' any job
   */
  function canTranslate(TMGMTJob $job) {
    return TRUE;
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::requestTranslation().
   */
  function requestTranslation(TMGMTJob $job) {
    $items = array();
    foreach ($job->getItems() as $item) {
      $SourceDataArray = tmgmt_flatten_data($item->getSourceData());
      foreach ($SourceDataArray as $key => $SourceData) {
        if (isset($SourceData['#translate']) && $SourceData['#translate'] !== TRUE) {
          unset($SourceDataArray[$key]);
        }
      }
      $items[$item->tjid] = $SourceDataArray;
    }

    $tjid = $job->tjid;
    $source_language = $job->source_language;
    $target_language = $job->target_language;

    switch ($this->getOption('export_format', 'html')) {
      case 'xliff':
        $name = "JobID" . $tjid . '.xml';
        $file_content = theme("xliff_template", array(
            'tjid' => $tjid,
            'origin_file_name' => $name,
            'source_language' => $source_language,
            'target_language' => $target_language,
            'items' => $items));

        drupal_add_http_header('Content-Type', 'text/xml; name="' . $name . '"');
        drupal_add_http_header('Content-Length', mb_strlen($file_content));
        drupal_add_http_header('Content-Disposition', 'attachment; filename="' . $name . '"');
        drupal_add_http_header('Cache-Control', 'private');
        break;

      default:
        $name = "JobID" . $tjid . '.html';
        $file_content = theme("html_template", array(
            'tjid' => $tjid,
            'source_language' => $source_language,
            'target_language' => $target_language,
            'items' => $items));

        drupal_add_http_header('Content-Type', 'text/html; name="' . $name . '"');
        drupal_add_http_header('Content-Length', mb_strlen($file_content));
        drupal_add_http_header('Content-Disposition', 'attachment; filename="' . $name . '"');
        drupal_add_http_header('Cache-Control', 'private');
        break;
    }

    print $file_content;
    $job->submitted('Translation exported');
    drupal_exit();
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::requestTranslation().
   */
  public function getSupportedTargetLanguages($source_language) {
    $languages = entity_metadata_language_list();
    unset($languages[LANGUAGE_NONE], $languages[$source_language]);
    return drupal_map_assoc(array_keys($languages));
  }

  /**
   * The settings form.
   */
  public function settingsForm($form, &$form_state, TMGMTTranslator $translator) {
    $form['export_format'] = array(
      '#type' => 'radios',
      '#title' => t('Export to'),
      '#options' => array(
          'html' => t('HTML'),
          'xliff' => t('XLIFF')),
      '#default_value' => $translator->getSetting('export_format'),
      '#description' => t('Please select the format you want to export data.'),
    );
    return $form;
  }

}
