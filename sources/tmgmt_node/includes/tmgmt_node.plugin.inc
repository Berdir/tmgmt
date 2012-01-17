<?php

/**
 * @file
 * Provides the node source controller.
 */

class TMGMTNodeSourcePluginController extends TMGMTDefaultSourcePluginController {
  
  public function __construct($plugin) {
    parent::__construct($plugin);
  }
  
  public function getTranslationStructure($node) {
    // For the moment, return only the title.
    // @todo: iterate over all the fields and return the ones that have to be
    // translated.
    return array(
      'node_field_title' => array(
        '#value' => $node->title,
      ),
    );
  }
  
  public function saveTranslation(TMGMTJobItem $job_item) {
    // @todo: Save the translation.
    // There are a few cases here:
    // 1. If we have content translation enabled, we have to create a new node
    // (or update an existing translation).
    // 2. If we have field translation, we have to update only some fields of
    // the node. There is an issue here with the title.
  }
  
}
