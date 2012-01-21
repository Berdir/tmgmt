<?php

/**
 * @file
 * Please supply a file description.
 */

class TMGMTTranslatorUIController extends EntityDefaultUIController {

  public function hook_menu() {
    $items = parent::hook_menu();
    // We don't need the entire entity label here.
    $items[$this->path]['title'] = 'Translators';
    $items[$this->path . '/add']['title'] = 'Add Translator';
    unset($items[$this->path . '/add']['title callback']);
    unset($items[$this->path . '/add']['title arguments']);
    if (!empty($this->entityInfo['exportable'])) {
      $items[$this->path . '/import']['title'] = 'Import Translator';
      unset($items[$this->path . '/import']['title callback']);
      unset($items[$this->path . '/import']['title arguments']);
    }
    return $items;
  }
}
