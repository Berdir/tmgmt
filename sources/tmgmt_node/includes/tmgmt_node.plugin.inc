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
      $field_structure = module_invoke($info['module'], 'tmgmt_source_translation_structure', $field_name, $node, $info, $field_instance);
      if ($field_structure) {
        $structure[$field_name] = $field_structure;
      }
    }
    $structure['node_title'][0]['value']['#label'] = 'Title';
    $structure['node_title'][0]['value']['#text'] = $node->title;
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
    return new TMGMTNodeSourceContentTranslation();
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
    if (empty($node->tnid)) {
      // We have no translation source nid, this is a new set, so create it.
      $node->tnid = $node->nid;
      node_save($node);
    }
    $translations = translation_node_get_translations($node->tnid);

    if (isset($translations[$job->target_language])) {
      // We have already a translation for the source node for the target
      // language, so load it.
      $tnode = node_load($translations[$job->target_language]->nid);
    }
    // If the $tnode is still NULL, we have to create a new node.
    if (is_null($tnode)) {
      $tnode = clone $node;
      unset($tnode->nid, $tnode->vid);
      $tnode->language = $job->target_language;
      $tnode->translation_source = $node;
    }
    // Time to put the translated data into the node.
    tmgmt_node_update_node_translation($tnode, $job_item->translated_data, $job->target_language);
  }
  
}