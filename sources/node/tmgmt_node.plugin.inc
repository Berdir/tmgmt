<?php

/**
 * @file
 * Provides the node source controller.
 */
class TMGMTNodeSourcePluginController extends TMGMTDefaultSourcePluginController {

  /**
   * Returns the data from the fields as a structure that can be processed by
   * the Translation Management system.
   */
  public function getData(TMGMTJobItem $job_item) {
    $node = node_load($job_item->item_id);
    $job = tmgmt_job_load($job_item->tjid);
    // Get all the fields that can be translated and arrange their values into
    // a specific structure.
    $structure = array();
    $structure['#label'] = node_type_get_name($node);
    $structure['node_title'][0]['value']['#label'] = t('Node title', array(), array('langcode' => $job->source_language));
    $structure['node_title'][0]['value']['#text'] = $node->title;
    foreach (field_info_instances('node', $node->type) as $field_name => $field_instance) {
      $info = field_info_field($field_name);
      $field_structure = module_invoke($info['module'], 'tmgmt_source_translation_structure', $field_name, $node, $info, $field_instance, $job_item);
      if ($field_structure) {
        $structure[$field_name] = $field_structure;
      }
    }
    return $structure;
  }

  /**
   * Overriden.
   */
  public function saveTranslation(TMGMTJobItem $job_item) {
    $node = node_load($job_item->item_id);
    $job = tmgmt_job_load($job_item->tjid);
    if (is_object($node) && is_object($job)) {
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
      tmgmt_node_update_node_translation($tnode, $job_item->translation, $job->target_language);
    }
  }

  /**
   * Implements TMGMTSourcePluginControllerInterface::getLabel().
   */
  public function getLabel(TMGMTJobItem $job_item) {
    if ($node = node_load($job_item->item_id)) {
      return entity_label('node', $node);
    }
    return parent::getLabel($job_item);
  }

  /**
   * Implements TMGMTSourcePluginControllerInterface::getUri().
   */
  public function getUri(TMGMTJobItem $job_item) {
    if ($node = node_load($job_item->item_id)) {
      return entity_uri('node', $node);
    }
    return parent::getUri($job_item);
  }

}
