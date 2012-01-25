<?php

/**
 * @file
 * Provides the user translator plugin controller.
 */
class TMGMTFileTranslatorPluginController extends TMGMTDefaultTranslatorPluginController {

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

    $name = "JobID" . $tjid;
    $file_template = theme("file_template", array(
        'tjid' => $tjid,
        'source_language' => $source_language,
        'target_language' => $target_language,
        'items' => $items));

    if ($job->getTranslator()->getSetting('export_format') == 'xliff') {
      $html = new DOMDocument();
      @$html->loadXML($file_template);

      $xsl = new DOMDocument;
      $xsl->load(drupal_get_path('module', 'tmgmt_file') . '/xsl/xml2xliff.xsl');

      $proc = new XSLTProcessor();
      $proc->ImportStyleSheet($xsl);

      $file_content = $proc->transformToXML($html);

      drupal_add_http_header('Content-Type', 'text/xml; name="' . $name . '.xlf"');
      drupal_add_http_header('Content-Length', mb_strlen($file_content));
      drupal_add_http_header('Content-Disposition', 'attachment; filename="' . $name . '.xlf"');
      drupal_add_http_header('Cache-Control', 'private');

      print $file_content;
    }
    else {
      drupal_add_http_header('Content-Type', 'text/html; name="' . $name . '.html"');
      drupal_add_http_header('Content-Length', mb_strlen($file_template));
      drupal_add_http_header('Content-Disposition', 'attachment; filename="' . $name . '.html"');
      drupal_add_http_header('Cache-Control', 'private');

      print $file_template;
    }

    $job->submitted('Translation exported');
    drupal_exit();
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::requestTranslation().
   */
  public function getSupportedTargetLanguages(TMGMTTranslator $translator, $source_language) {
    $languages = entity_metadata_language_list();
    unset($languages[LANGUAGE_NONE], $languages[$source_language]);
    return drupal_map_assoc(array_keys($languages));
  }

  /**
   * The settings form.
   */
  public function pluginSettingsForm($form, &$form_state, TMGMTTranslator $translator) {
    $form['export_format'] = array(
      '#type' => 'radios',
      '#title' => t('Export to'),
      '#options' => array(
        'html' => t('HTML'),
        'xliff' => t('XLIFF'),
      ),
      '#default_value' => $translator->getSetting('export_format'),
      '#description' => t('Please select the format you want to export data.'),
    );
    return parent::pluginSettingsForm($form, $form_state, $translator);
  }

}
