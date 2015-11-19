<?php

/**
 * @file
 * Contains Drupal\tmgmt_content\EntitySourcePluginController.
 */

namespace Drupal\tmgmt_content\Plugin\tmgmt\Source;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\Plugin\DataType\EntityReference;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\TypedData\OptionsProviderInterface;
use Drupal\Core\TypedData\Type\StringInterface;
use Drupal\Core\TypedData\PrimitiveInterface;
use Drupal\tmgmt\JobItemInterface;
use Drupal\tmgmt\SourcePluginBase;
use Drupal\tmgmt\TMGMTException;
use Drupal\Core\Render\Element;

/**
 * Content entity source plugin controller.
 *
 * @SourcePlugin(
 *   id = "content",
 *   label = @Translation("Content Entity"),
 *   description = @Translation("Source handler for entities."),
 *   ui = "Drupal\tmgmt_content\ContentEntitySourcePluginUi"
 * )
 */
class ContentEntitySource extends SourcePluginBase {

  /**
   * {@inheritdoc}
   */
  public function getLabel(JobItemInterface $job_item) {
    if ($entity = entity_load($job_item->getItemType(), $job_item->getItemId())) {
      return $entity->label();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getUrl(JobItemInterface $job_item) {
    if ($entity = entity_load($job_item->getItemType(), $job_item->getItemId())) {
      return $entity->urlInfo();
    }
  }

  /**
   * Implements TMGMTEntitySourcePluginController::getData().
   *
   * Returns the data from the fields as a structure that can be processed by
   * the Translation Management system.
   */
  public function getData(JobItemInterface $job_item) {
    $entity = entity_load($job_item->getItemType(), $job_item->getItemId());
    if (!$entity) {
      throw new TMGMTException(t('Unable to load entity %type with id %id', array('%type' => $job_item->getItemType(), $job_item->getItemId())));
    }
    $languages = \Drupal::languageManager()->getLanguages();
    $id = $entity->language()->getId();
    if (!isset($languages[$id])) {
      throw new TMGMTException(t('Entity %entity could not be translated because the language %language is not applicable', array('%entity' => $entity->language()->getId(), '%language' => $entity->language()->getName())));
    }

    if (!$entity->hasTranslation($job_item->getJob()->getSourceLangcode())) {
      throw new TMGMTException(t('The entity %id with translation %lang does not exist.', array('%id' => $entity->id(), '%lang' => $job_item->getJob()->getSourceLangcode())));
    }

    $translation = $entity->getTranslation($job_item->getJob()->getSourceLangcode());
    $data = $this->extractTranslatableData($translation);
    return $data;
  }

  /**
   * Extracts translatable data from an entity.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity to get the translatable data from.
   *
   * @return array $data
   *   Translatable data.
   */
  public function extractTranslatableData(ContentEntityInterface $entity) {

    // @todo Expand this list or find a better solution to exclude fields like
    //   content_translation_source.

    $field_definitions = $entity->getFieldDefinitions();
    $exclude_field_types = ['language'];
    $translatable_fields = array_filter($field_definitions, function (FieldDefinitionInterface $field_definition) use ($exclude_field_types) {
        return $field_definition->isTranslatable() && !in_array($field_definition->getType(), $exclude_field_types);
    });

    $data = array();
    foreach ($translatable_fields as $key => $field_definition) {
      $field = $entity->get($key);
      foreach ($field as $index => $field_item) {
        $format = NULL;
        /* @var FieldItemInterface $field_item */
        foreach ($field_item->getProperties() as $property_key => $property) {
          // Ignore computed values.
          $property_definition = $property->getDataDefinition();
          // Ignore values that are not primitives.
          if (!($property instanceof PrimitiveInterface)) {
            continue;
          }
          $translate = TRUE;
          // Ignore properties with limited allowed values or if they're not strings.
          if ($property instanceof OptionsProviderInterface || !($property instanceof StringInterface)) {
            $translate = FALSE;
          }
          // All the labels are here, to make sure we don't have empty labels in
          // the UI because of no data.
          if ($translate == TRUE) {
            $data[$key]['#label'] = $field_definition->getLabel();
            $data[$key][$index]['#label'] = t('Delta #@delta', array('@delta' => $index));
          }
          $data[$key][$index][$property_key] = array(
            '#label' => $property_definition->getLabel(),
            '#text' => $property->getValue(),
            '#translate' => $translate,
          );
          if ($translate && ($field_item->getFieldDefinition()->getFieldStorageDefinition()->getSetting('max_length') != 0)) {
            $data[$key][$index][$property_key]['#max_length'] = $field_item->getFieldDefinition()->getFieldStorageDefinition()->getSetting('max_length');
          }

          if ($property_definition->getDataType() == 'filter_format') {
            $format = $property->getValue();
          }
        }
        // Add the format to the translatable properties.
        if (!empty($format)) {
          foreach ($data[$key][$index] as $name => $value) {
            if (is_array($value) && isset($value['#translate']) && $value['#translate'] == TRUE) {
              $data[$key][$index][$name]['#format'] = $format;
            }
          }
        }
      }
    }

    $embeddable_field_names = \Drupal::config('tmgmt_content.settings')->get('embedded_fields');
    $embeddable_fields = array_filter($field_definitions, function (FieldDefinitionInterface $field_definition) use ($embeddable_field_names) {
      return !$field_definition->isTranslatable() && isset($embeddable_field_names[$field_definition->getTargetEntityTypeId()][$field_definition->getName()]);
    });
    foreach ($embeddable_fields as $key => $field_definition) {
      $field = $entity->get($key);
      foreach ($field as $index => $field_item) {
        /* @var FieldItemInterface $field_item */
        foreach ($field_item->getProperties(TRUE) as $property_key => $property) {
          // If the property is a content entity reference and it's value is
          // defined, than we call this method again to get all the data.
          if ($property instanceof EntityReference && $property->getValue() instanceof ContentEntityInterface) {
            // All the labels are here, to make sure we don't have empty
            // labels in the UI because of no data.
            $data[$key]['#label'] = $field_definition->getLabel();
            $data[$key][$index]['#label'] = t('Delta #@delta', array('@delta' => $index));
            $data[$key][$index][$property_key] = $this->extractTranslatableData($property->getValue());
          }
        }
      }
    }
    return $data;
  }

  /**
   * {@inheritdoc}
   */
  public function saveTranslation(JobItemInterface $job_item, $target_langcode) {
    /* @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $entity = entity_load($job_item->getItemType(), $job_item->getItemId());
    if (!$entity) {
      $job_item->addMessage('The entity %id of type %type does not exist, the job can not be completed.', array(
        '%id' => $job_item->getItemId(),
        '%type' => $job_item->getItemType(),
      ), 'error');
      return FALSE;
    }

    $data = $job_item->getData();
    $this->doSaveTranslations($entity, $data, $target_langcode);
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function getItemTypes() {
    $entity_types = \Drupal::entityManager()->getDefinitions();
    $types = array();
    $content_translation_manager = \Drupal::service('content_translation.manager');
    foreach ($entity_types as $entity_type_name => $entity_type) {
      if ($content_translation_manager->isEnabled($entity_type->id())) {
        $types[$entity_type_name] = $entity_type->getLabel();
      }
    }
    return $types;
  }

  /**
   * {@inheritdoc}
   */
  public function getItemTypeLabel($type) {
    return \Drupal::entityManager()->getDefinition($type)->getLabel();
  }

  /**
   * {@inheritdoc}
   */
  public function getType(JobItemInterface $job_item) {
    if ($entity = entity_load($job_item->getItemType(), $job_item->getItemId())) {
      $bundles = entity_get_bundles($job_item->getItemType());
      $entity_type = $entity->getEntityType();
      $bundle = $entity->bundle();
      // Display entity type and label if we have one and the bundle isn't
      // the same as the entity type.
      if (isset($bundles[$bundle]) && $bundle != $job_item->getItemType()) {
        return t('@type (@bundle)', array('@type' => $entity_type->getLabel(), '@bundle' => $bundles[$bundle]['label']));
      }
      // Otherwise just display the entity type label.
      return $entity_type->getLabel();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getSourceLangCode(JobItemInterface $job_item) {
    $entity = entity_load($job_item->getItemType(), $job_item->getItemId());
    return $entity->getUntranslated()->language()->getId();
  }

  /**
   * {@inheritdoc}
   */
  public function getExistingLangCodes(JobItemInterface $job_item) {
    if ($entity = entity_load($job_item->getItemType(), $job_item->getItemId())) {
      return array_keys($entity->getTranslationLanguages());
    }

    return array();
  }

  /**
   * Saves translation data in an entity translation.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity for which the translation should be saved.
   * @param array $data
   *   The translation data for the fields.
   * @param string $target_langcode
   *   The target language.
   */
  protected function doSaveTranslations(ContentEntityInterface $entity, array $data, $target_langcode) {
    // If the translation for this language does not exist yet, initialize it.
    if (!$entity->hasTranslation($target_langcode)) {
      $entity->addTranslation($target_langcode, $entity->toArray());
    }

    $embeded_fields = \Drupal::config('tmgmt_content.settings')->get('embedded_fields');

    $translation = $entity->getTranslation($target_langcode);

    foreach ($data as $name => $field_data) {
      foreach (Element::children($field_data) as $delta) {
        $field_item = $field_data[$delta];
        foreach (Element::children($field_item) as $property) {
          $property_data = $field_item[$property];
          // If there is translation data for the field property, save it.
          if (isset($property_data['#translation']['#text'])) {
            $translation->get($name)
              ->offsetGet($delta)
              ->set($property, $property_data['#translation']['#text']);
          }
          // If the field is an embeddable reference, we assume that the
          // property is a field reference.
          elseif (isset($embeded_fields[$entity->getEntityTypeId()][$name])) {
            $this->doSaveTranslations($translation->get($name)->offsetGet($delta)->$property, $property_data, $target_langcode);
          }
        }
      }
    }
    $translation->save();
  }
}
