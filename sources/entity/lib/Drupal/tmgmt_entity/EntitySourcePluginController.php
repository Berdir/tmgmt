<?php

/**
 * @file
 * Contains Drupal\tmgmt_entity\EntitySourcePluginController.
 */

namespace Drupal\tmgmt_entity;

use Drupal\tmgmt\DefaultSourcePluginController;
use Drupal\tmgmt\Plugin\Core\Entity\JobItem;
use Drupal\tmgmt\TMGMTException;

/**
 * Entity source plugin controller.
 */
class EntitySourcePluginController extends DefaultSourcePluginController {

  public function getLabel(JobItem $job_item) {
    if ($entity = entity_load($job_item->item_type, $job_item->item_id)) {
      return $entity->labe();
    }
  }

  public function getUri(JobItem $job_item) {
    if ($entity = entity_load($job_item->item_type, $job_item->item_id)) {
      return $entity->uri();
    }
  }

  /**
   * Implements TMGMTEntitySourcePluginController::getData().
   *
   * Returns the data from the fields as a structure that can be processed by
   * the Translation Management system.
   */
  public function getData(JobItem $job_item) {
    $entity = entity_load($job_item->item_type, $job_item->item_id);
    if (!$entity) {
      throw new TMGMTException(t('Unable to load entity %type with id %id', array('%type' => $job_item->item_type, $job_item->item_id)));
    }
    $properties = $entity->getPropertyDefinitions();
    $translatable_text_properties = array_filter($properties, function ($definition) {
      // @todo: What other types can be translated?
      return !empty($definition['translatable']) && in_array($definition['type'], array('string_field', 'text_field', 'text_with_summary_field'));
    });

    $data = array();
    $translation = $entity->getTranslation($job_item->getJob()->source_language);
    foreach ($translatable_text_properties as $key => $property_definition) {
      $property = $translation->get($key);
      $data[$key]['#label'] = $property_definition['label'];
      foreach ($property as $index => $property_item) {
        $data[$key][$index]['#label'] = t('Delta #@delta', array('@delta' => $index));
        $item_definitions = $property_item->getPropertyDefinitions();
        foreach ($item_definitions as $item_key => $item_definition) {
          // Ignore computed values.
          if (!empty($item_definition['computed'])) {
            continue;
          }

          $translate = TRUE;
          // @todo: Make this pluggable.
          if ($item_key == 'format') {
            $translate = FALSE;
          }
          $data[$key][$index][$item_key] = array(
            '#label' =>$item_definition['label'],
            '#text' => $property[$index]->$item_key,
            '#translate' => $translate,
          );
        }
      }
    }
    return $data;
  }

  /**
   * Implements TMGMTEntitySourcePluginController::saveTranslation().
   */
  public function saveTranslation(JobItem $job_item) {
    $entity = entity_load($job_item->item_type, $job_item->item_id);
    $job = tmgmt_job_load($job_item->tjid);

    tmgmt_field_populate_entity($entity, $job->target_language, $job_item->getData());
    $entity->save();
    $job_item->accepted();
  }

}
