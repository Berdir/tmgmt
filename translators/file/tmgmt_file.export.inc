<?php

/**
 * Interface for exporting to a given file format.
 */
interface TMGMTFileExport {

  function getExtension();

  function export(TMGMTJob $job);
}

/**
 * Export to XLIFF format.
 */
class TMGMTFileExportXLIFF extends XMLWriter implements TMGMTFileExport {

  protected $job;

  protected function addItem(TMGMTJobItem $item) {
    $this->startElement('group');
    $this->writeAttribute('id', $item->tjiid);

    // Add a note.
    $this->writeElement('note', $item->getSourceLabel());

    // @todo: Write in nested groups instead of flattening it.
    $data = array_filter(tmgmt_flatten_data($item->getSourceData()), '_tmgmt_filter_data');
    foreach ($data as $key => $element) {
      $this->addTransUnit($item->tjiid . '][' . $key, $element);
    }
    $this->endElement();
  }

  protected function addTransUnit($key, $element) {
    $this->startElement('trans-unit');
    $this->writeAttribute('id', $key);
    $this->startElement('source');
    $this->writeAttribute('xml:lang', $this->job->source_language);
    $this->text($element['#text']);
    $this->endElement();
    $this->startElement('target');
    $this->writeAttribute('xml:lang', $this->job->target_language);
    $this->text($element['#text']);
    $this->endElement();
    if (isset($element['#label'])) {
      $this->writeElement('note', $element['#label']);
    }
    $this->endElement();
  }

  public function export(TMGMTJob $job) {

    $this->job = $job;

    $this->openMemory();
    $this->setIndent(true);
    $this->setIndentString(' ');
    $this->startDocument('1.0', 'UTF-8');

    // Root element with schema definition.
    $this->startElement('xliff');
    $this->writeAttribute('version', '1.2');
    $this->writeAttribute('xmlns', 'urn:oasis:names:tc:xliff:document:1.2');
    $this->writeAttribute('xmlns:xsi', 'http://www.w3.org/2001/XMLSchema-instance');
    $this->writeAttribute('xsi:schemaLocation', 'urn:oasis:names:tc:xliff:document:1.2 xliff-core-1.2-strict.xsd');

    // File element.
    $this->startElement('file');
    $this->writeAttribute('original', 'xliff-core-1.2-strict.xsd');
    $this->writeAttribute('source-language', $job->source_language);
    $this->writeAttribute('target-language', $job->target_language);
    $this->writeAttribute('datatype', 'plaintext');
    // Date needs to be in ISO-8601 UTC
    $this->writeAttribute('date', date('Y-m-d\Th:m:i\Z'));

    $this->startElement('header');
    $this->startElement('phase-group');
    $this->startElement('phase');
    $this->writeAttribute('tool-id', 'tmgmt');
    $this->writeAttribute('phase-name', 'extraction');
    $this->writeAttribute('process-name', 'extraction');
    $this->endElement();
    $this->endElement();
    $this->startElement('tool');
    $this->writeAttribute('tool-id', 'tmgmt');
    $this->writeAttribute('tool-name', 'Drupal Translation Management Tools');
    $this->endElement();
    $this->endElement();

    $this->startElement('body');

    foreach ($job->getItems() as $item) {
      $this->addItem($item);
    }

    $this->endElement();
    $this->endElement();
    $this->endElement();
    $this->endDocument();
    return $this->outputMemory();
  }

  public function getExtension() {
    return 'xlf';
  }

}

class TMGMTFileExportHTML implements TMGMTFileExport {

  public function export(TMGMTJob $job) {
    $items = array();
    foreach ($job->getItems() as $item) {
      $items[$item->tjiid] = array_filter(tmgmt_flatten_data($item->getSourceData()), '_tmgmt_filter_data');
    }
    return theme("tmgmt_file_html_template", array(
        'tjid' => $job->tjid,
        'source_language' => $job->source_language,
        'target_language' => $job->target_language,
        'items' => $items,
      ));
  }

  public function getExtension() {
    return 'html';
  }

}