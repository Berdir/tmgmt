<?php

/**
 * @file
 * Provides the Entity source controller.
 */

/**
 * @ingroup source
 */
class TMGMTEntitySourcePluginController extends TMGMTDefaultSourcePluginController {

  public function __construct($plugin) {
    parent::__construct($plugin);
  }
  /**
   * Returns the data from the fields as a structure that can be processed by
   * the Translation Management system.
   * 
   */
  public function getData(TMGMTJobItem $job_item) {
    $entity = current(entity_load($job_item->item_type, array($job_item->item_id)));
    list(, , $bundle) = entity_extract_ids($job_item->item_type, $entity);
    $job = tmgmt_job_load($job_item->tjid);
    // Get all the fields that can be translated and arrange their values into
    // a specific structure.
    $structure = array();
    foreach (field_info_instances($job_item->item_type, $bundle) as $field_name => $field_instance) {
      $info = field_info_field($field_name);
      if ($info['translatable']) {
        $field_structure = module_invoke($info['module'], 'tmgmt_source_translation_structure', $field_name, $entity, $info, $field_instance, $job_item);
        if ($field_structure) {
          $structure[$field_name] = $field_structure;
        }
      }
    }
    return $structure;
  }
  
  /**
   * Overriden.
   */
  public function saveTranslation(TMGMTJobItem $job_item) {
    $entity = current(entity_load($job_item->item_type, array($job_item->item_id)));
    $job = tmgmt_job_load($job_item->tjid);
    tmgmt_entity_update_entity_translation($entity, $job_item->item_type, $job_item->translated_data, $job->target_language);
  }

}