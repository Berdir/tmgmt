<?php

/**
 * @file
 * Provides the node source controller.
 */

/**
 * @ingroup source
 */
class TMGMTNodeSourcePluginController extends TMGMTDefaultSourcePluginController {

  public function __construct($plugin) {
    parent::__construct($plugin);
  }
  /**
   * @todo: comments about the returned structure.
   */
  public function getData(TMGMTJobItem $job_item) {
    $node = node_load($job_item->item_id);
    // Get all the fields that can be translated and arrange their values into
    // a specific structure.
    $structure = array();
    foreach (field_info_instances('node', $node->type) as $field_name => $field_instance) {
      $info = field_info_field($field_name);
      if ($info['translatable']) {
        $field_structure = module_invoke($info['module'], 'tmgmt_source_translation_structure', $field_name, $node, $info, $field_instance);
        if ($field_structure) {
          $structure[$field_name] = $field_structure;
        }
      }
    }
    return $structure;
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
