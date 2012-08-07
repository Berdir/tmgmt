<?php

/**
 * @file
 * Contains Drupal\tmgmt_entity\EntitySourcePluginController.
 */

namespace Drupal\tmgmt_entity\Plugin\tmgmt\Source;

use Drupal\Core\TypedData\AllowedValuesInterface;
use Drupal\Core\TypedData\Type\StringInterface;
use Drupal\Core\TypedData\PrimitiveInterface;
use Drupal\tmgmt\SourcePluginBase;
use Drupal\tmgmt\Plugin\Core\Entity\JobItem;
use Drupal\tmgmt\TMGMTException;
use Drupal\tmgmt\Annotation\SourcePlugin;
use Drupal\Core\Annotation\Translation;

/**
 * Entity source plugin controller.
 *
 * @SourcePlugin(
 *   id = "entity",
 *   label = @Translation("Entity"),
 *   description = @Translation("Source handler for entities."),
 *   ui = "Drupal\tmgmt_entity_ui\EntityUiSourcePluginUi"
 * )
 */
class EntitySource extends SourcePluginBase {

  public function getLabel(JobItem $job_item) {
    if ($entity = entity_load($job_item->item_type, $job_item->item_id)) {
      return $entity->label();
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
    $translatable_fields = array_filter($properties, function ($definition) {
      return !empty($definition['translatable']);
    });

    $data = array();
    $translation = $entity->getTranslation($job_item->getJob()->source_language);
    foreach ($translatable_fields as $key => $field_definition) {
      $field = $translation->get($key);
      $data[$key]['#label'] = $field->getFieldDefinition() ? $field->getFieldDefinition()->getFieldLabel() : $field_definition['label'];
      foreach ($field as $index => $property_item) {
        $data[$key][$index]['#label'] = t('Delta #@delta', array('@delta' => $index));
        foreach ($property_item->getProperties() as $property_key => $property) {
          // Ignore computed values.
          $property_definition = $property->getDefinition();
          if (!empty($property_definition['computed'])) {
            continue;
          }
          // Ignore values that are not primitves.
          if (!($property instanceof PrimitiveInterface)) {
            continue;
          }

          $translate = TRUE;
          // Ignore properties with limited allowed values or if they're not strings.
          if ($property instanceof AllowedValuesInterface || !($property instanceof StringInterface)) {
            $translate = FALSE;
          }
          $data[$key][$index][$property_key] = array(
            '#label' => $property_definition['label'],
            '#text' => $property->getValue(),
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

    $translation = $entity->getTranslation($job->target_language);
    $data = $job_item->getData();
    foreach ($data as $name => $field_data) {
      foreach (element_children($field_data) as $delta) {
        $field_item = $field_data[$delta];
        foreach (element_children($field_item) as $property) {
          $property_data = $field_item[$property];
          if (isset($property_data['#translation']['#text'])) {
            $translation->get($name)->offsetGet($delta)->set($property, $property_data['#translation']['#text']);
          }
        }
      }
    }

    $translation->save();
    $job_item->accepted();
  }

  /**
   * {@inheritdoc}
   */
  public function getItemTypes() {
    return array('node' => t('Content'));
  }

  /**
   * Implements TMGMTEntitySourcePluginControllerInterface::getType().
   */
  public function getType(JobItem $job_item) {
    if ($entity = entity_load($job_item->item_type, $job_item->item_id)) {
      $bundles = entity_get_bundles($job_item->item_type);
      $info = $entity->entityInfo();
      $bundle = $entity->bundle();
      // Display entity type and label if we have one and the bundle isn't
      // the same as the entity type.
      if (isset($bundles[$bundle]) && $bundle != $job_item->item_type) {
        return t('@type (@bundle)', array('@type' => $info['label'], '@bundle' => $bundles[$bundle]['label']));
      }
      // Otherwise just display the entity type label.
      elseif (isset($info['label'])) {
        return $info['label'];
      }
      return parent::getType($job_item);
    }
  }

}
