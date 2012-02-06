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
    $extension = 'html';
    $file_content = theme("file_template", array(
        'tjid' => $tjid,
        'source_language' => $source_language,
        'target_language' => $target_language,
        'items' => $items));

    if ($job->getTranslator()->getSetting('export_format') == 'xliff') {
      $html = new DOMDocument();
      @$html->loadXML($file_content);

      $xsl = new DOMDocument;
      $xsl->load(drupal_get_path('module', 'tmgmt_file') . '/xsl/xml2xliff.xsl');

      $proc = new XSLTProcessor();
      $proc->ImportStyleSheet($xsl);

      $file_content = $proc->transformToXML($html);
      $extension = 'xlf';
    }

    // @todo: Make this configurable
    $path = 'public://tmgmt_file/' . $name . $extension;
    if (file_prepare_directory(dirname($path), FILE_CREATE_DIRECTORY)) {
      file_put_contents($path, $file_content);
      $job->submitted('Exported file can be downloaded <a href"!link">here</a>', array('!link', file_create_url($path)));
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
