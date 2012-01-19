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
   * @todo: Implement this function.
   */
  public function saveTranslation(TMGMTJobItem $job_item) {
    $entity = current(entity_load($job_item->item_type, array($job_item->item_id)));
    $job = tmgmt_job_load($job_item->tjid);
    tmgmt_entity_update_entity_translation($entity, $job_item->item_type, $job_item->translated_data, $job->target_language);
    // @todo: refactor this.
    /*if (module_exists('entity_translation')) {
      db_delete('entity_translation')
        ->condition('entity_type', $job_item->item_type)
        ->condition('entity_id', $job_item->item_id)
        ->condition('language', $job->target_language)
        ->execute();

      $columns = array('entity_type', 'entity_id', 'language', 'source', 'uid', 'status', 'translate', 'created', 'changed');
      $query = db_insert('entity_translation')->fields($columns);
      $values = array(
        'entity_type' => $job_item->item_type,
        'entity_id' => $job_item->item_id,
        'language' => $job->target_language,
        'source' => $job->source_language,
        'uid' => 1,
        'status' => 1,
        'translate' => 0,
        'created' => REQUEST_TIME,
        'changed' => REQUEST_TIME,
      );
      $query->values($values)->execute();
    }*/
  }

}