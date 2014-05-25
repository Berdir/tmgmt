<?php

/**
 * @file
 * Contains Drupal\tmgmt_config\ConfigEntitySource.
 */

namespace Drupal\tmgmt_config\Plugin\tmgmt\Source;

use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\TypedData\AllowedValuesInterface;
use Drupal\Core\TypedData\Type\StringInterface;
use Drupal\Core\TypedData\PrimitiveInterface;
use Drupal\tmgmt\SourcePluginBase;
use Drupal\tmgmt\Entity\JobItem;
use Drupal\tmgmt\TMGMTException;

/**
 * Content entity source plugin controller.
 *
 * @SourcePlugin(
 *   id = "config",
 *   label = @Translation("Config Entity"),
 *   description = @Translation("Source handler for config entities."),
 *   ui = "Drupal\tmgmt_content\ContentEntitySourcePluginUi"
 * )
 */
class ConfigEntitySource extends SourcePluginBase {

  public function getLabel(JobItem $job_item) {
    if ($entity = entity_load($job_item->getItemType(), $job_item->getItemId())) {
      return $entity->label();
    }
  }

  public function getUri(JobItem $job_item) {
    if ($entity = entity_load($job_item->getItemType(), $job_item->getItemId())) {
      // @todo: Use routes.
      $uri['path'] = $entity->getSystemPath();
      $uri += $entity->urlInfo()->toArray();
      return $uri;
    }
  }

  /**
   * Implements TMGMTEntitySourcePluginController::getData().
   *
   * Returns the data from the fields as a structure that can be processed by
   * the Translation Management system.
   */
  public function getData(JobItem $job_item) {
    $entity = entity_load($job_item->getItemType(), $job_item->getItemId());
    if (!$entity) {
      throw new TMGMTException(t('Unable to load entity %type with id %id', array('%type' => $job_item->getItemType(), $job_item->getItemId())));
    }

    return $data;
  }

  /**
   * {@inheritdoc}
   */
  public function saveTranslation(JobItem $job_item) {

  }

  /**
   * {@inheritdoc}
   */
  public function getItemTypes() {
    $entity_types = \Drupal::entityManager()->getDefinitions();
    $types = array();
    foreach ($entity_types as $entity_type_name => $entity_type) {
      if ($entity_type->isSubclassOf('Drupal\Core\Config\Entity\ConfigEntityInterface')) {
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
  public function getType(JobItem $job_item) {
    return \Drupal::entityManager()->getDefinition($job_item->getItemType())->getLabel();
  }

  /**
   * {@inheritdoc}
   */
  public function getSourceLangCode(JobItem $job_item) {
    $entity = entity_load($job_item->getItemType(), $job_item->getItemId());
    return $entity->language()->id;
  }

  /**
   * {@inheritdoc}
   */
  public function getExistingLangCodes(JobItem $job_item) {
    // @todo
    return array();
  }


}
