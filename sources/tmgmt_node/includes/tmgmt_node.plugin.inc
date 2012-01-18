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
   * Returns the data from the fields as a structure that can be processed by
   * the Translation Management system.
   * 
   * A real example could be:
   * array(
   *   'field_text_example' => array(
   *      'values' => array(
   *        0 => array(
   *          'value' => array(
   *            '#text' => "Some example text",
   *            '#label' => "Value nr. 0",
   *          ),
   *          'summary' => array(
   *            '#text' => "Some example summary",
   *            '#label' => "Summary nr. 0",
   *          ),
   *        ),
   *        1 => array(
   *          'value' => array(
   *            '#text' => "A second example text",
   *            '#label' => "Value nr. 1",
   *          )
   *        ),
   *      )
   *   )
   * )
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
    $node = node_load($job_item->item_id);
    if (is_object($node)) {
      $locale_setting = variable_get('language_content_type_' . $node->type, 0);
      $translation_saver = TMGMTNodeSourceTranslationFactory::getTranslationSaver($locale_setting);
      if ($translation_saver) {
        $translation_saver->saveTranslation($job_item);
      }
    }
  }

}

/**
 * Class that has as its own purpose to implement a static function that can
 * return a Translation Saver object.
 */
class TMGMTNodeSourceTranslationFactory {
  public static function getTranslationSaver($setting) {
    switch ($setting) {
      case 2:{
        return new TMGMTNodeSourceContentTranslation();
      }
      case 4: {
        return new TMGMTNodeSourceFieldTranslation();
      }
    default :
      return NULL;
    }
  }
}

/**
 * Interface for classes that can save a translation.
 */
interface TMGMTNodeSourceTranslationInterface {
  public function saveTranslation(TMGMTJobItem $job_item);
}

/**
 * Class for saving content translations.
 */
class TMGMTNodeSourceContentTranslation implements TMGMTNodeSourceTranslationInterface {
  
  public function saveTranslation(TMGMTJobItem $job_item) {
    $node = node_load($job_item->item_id);
    $job = tmgmt_job_load($job_item->tjid);
    // in $tnode we will have the translated node.
    $tnode = NULL;
    if (!empty($node->tnid)) {
      $translations = translation_node_get_translations($node->tnid);
      if (isset($translations[$job->target_language])) {
        // We have already a translation for the source node for the target
        // language, so load it.
        $tnode = node_load($translations[$job->target_language]->nid);
      }
    }
    // If the $tnode is still NULL, we have to create a new node.
    if (is_null ($tnode)) {
      $tnode = new stdClass();
      $tnode->language = $job->target_language;
      $tnode->translation_source = $node;
      // @todo: The title field will not probably be shared.
      $shared_fields = array('title', 'type', 'uid', 'status', 'comment', 'promote', 'sticky');
      foreach ($shared_fields as $shared_field) {
        $tnode->{$shared_field} = $node->{$shared_field};
      }
    }
    // Time to put the translated data into the node.
    // Do we need to unflat the data here?
    $translated_data = tmgmt_unflatten_data($job_item->translated_data);
    tmgmt_node_update_node_translation($tnode, $translated_data, $job->target_language);
  }
  
}

/**
 * Class for saving field translations.
 */
class TMGMTNodeSourceFieldTranslation implements TMGMTNodeSourceTranslationInterface {

  public function saveTranslation(TMGMTJobItem $job_item) {
    $node = node_load($job_item->item_id);
    $job = tmgmt_job_load($job_item->tjid);
    // Do we need to unflat the data here?
    $translated_data = tmgmt_unflatten_data($job_item->translated_data);
    tmgmt_node_update_node_translation($node, $translated_data, $job->target_language);
    // @todo: refactor this.
    if (module_exists('entity_translation')) {
      db_delete('entity_translation')
        ->condition('entity_type', 'node')
        ->condition('entity_id', $node->nid)
        ->condition('language', $job->target_language)
        ->execute();

      $columns = array('entity_type', 'entity_id', 'language', 'source', 'uid', 'status', 'translate', 'created', 'changed');
      $query = db_insert('entity_translation')->fields($columns);
      $values = array(
        'entity_type' => 'node',
        'entity_id' => $node->nid,
        'language' => $job->target_language,
        'source' => $job->source_language,
        'uid' => $node->uid,
        'status' => $node->status,
        'translate' => 0,
        'created' => REQUEST_TIME,
        'changed' => REQUEST_TIME,
      );
      $query->values($values)->execute();
    }
  }

}