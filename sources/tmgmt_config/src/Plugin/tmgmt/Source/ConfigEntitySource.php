<?php

/**
 * @file
 * Contains Drupal\tmgmt_config\ConfigEntitySource.
 */

namespace Drupal\tmgmt_config\Plugin\tmgmt\Source;

use Drupal\config_translation\Form\ConfigTranslationFormBase;
use Drupal\Core\Config\Schema\Mapping;
use Drupal\Core\Config\Schema\Sequence;
use Drupal\tmgmt\JobItemInterface;
use Drupal\tmgmt\SourcePluginBase;
use Drupal\tmgmt\TMGMTException;
use Drupal\Core\Render\Element;

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

  public function getLabel(JobItemInterface $job_item) {
    if ($entity = entity_load($job_item->getItemType(), $job_item->getItemId())) {
      return $entity->label();
    }
  }

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
    /* @var \Drupal\config_translation\ConfigMapperInterface $config_mapper */
    $config_mapper = \Drupal::service('plugin.manager.config_translation.mapper')->createInstance($job_item->getItemType());
    $config_mapper->setEntity($entity);

    $id = $entity->getEntityType()->getConfigPrefix() . '.' . $entity->id();
    $schema = \Drupal::service('config.typed')->get($id);
    return $this->extractTranslatables($schema, $config_mapper->getConfigData()[$id]);
  }

  /**
   * {@inheritdoc}
   */
  public function saveTranslation(JobItemInterface $job_item) {

    $entity = entity_load($job_item->getItemType(), $job_item->getItemId());
    if (!$entity) {
      throw new TMGMTException(t('Unable to load entity %type with id %id', array('%type' => $job_item->getItemType(), $job_item->getItemId())));
    }
    /* @var \Drupal\config_translation\ConfigMapperInterface $config_mapper */
    $config_mapper = \Drupal::service('plugin.manager.config_translation.mapper')->createInstance($job_item->getItemType());
    $config_mapper->setEntity($entity);

    $data = $job_item->getData();

    foreach ($config_mapper->getConfigNames() as $name) {
      $schema = \Drupal::service('config.typed')->get($name);

      // Set configuration values based on form submission and source values.
      $base_config = \Drupal::configFactory()->getEditable($name);
      $config_translation = \Drupal::languageManager()->getLanguageConfigOverride($job_item->getJob()->getTargetLangcode(), $name);

      $element = ConfigTranslationFormBase::createFormElement($schema);
      $element->setConfig($base_config, $config_translation, $this->convertToTranslation($data));

      // If no overrides, delete language specific configuration file.
      $saved_config = $config_translation->get();
      if (empty($saved_config)) {
        $config_translation->delete();
      }
      else {
        $config_translation->save();
      }
    }

  }

  /**
   * Converts a translated data structure. We convert it.
   *
   * @param array $data
   *   The translated data structure.
   *
   * @return array
   *   Returns a translation array as expected by
   *   \Drupal\config_translation\FormElement\ElementInterface::setConfig().
   *
   */
  public function convertToTranslation($data) {
    $children = Element::children($data);
    if ($children) {
      $translation = array();
      foreach ($children as $name) {
        $property_data = $data[$name];
        $translation[$name] = $this->convertToTranslation($property_data);
      }
      return $translation;
    }
    elseif (isset($data['#translation']['#text'])) {
      return $data['#translation']['#text'];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getItemTypes() {
    $entity_types = \Drupal::entityManager()->getDefinitions();
    $types = array();
    foreach ($entity_types as $entity_type_name => $entity_type) {
      if ($entity_type->isSubclassOf('Drupal\Core\Config\Entity\ConfigEntityInterface')) {
        $types[$entity_type_name] = (string) $entity_type->getLabel();
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
    return \Drupal::entityManager()->getDefinition($job_item->getItemType())->getLabel();
  }

  /**
   * {@inheritdoc}
   */
  public function getSourceLangCode(JobItemInterface $job_item) {
    $entity = entity_load($job_item->getItemType(), $job_item->getItemId());
    return $entity->language()->getId();
  }

  /**
   * {@inheritdoc}
   */
  public function getExistingLangCodes(JobItemInterface $job_item) {
    // @todo
    return array();
  }

  /**
   * @param $schema
   */
  protected function extractTranslatables($schema, $config_data, $base_key = '') {
    $data = array();
    foreach ($schema as $key => $element) {
      $element_key = implode('.', array_filter(array($base_key, $key)));
      $definition = $element->getDataDefinition();
        // + array('label' => t('N/A'));
      if ($element instanceof Mapping || $element instanceof Sequence) {
        // Build sub-structure and include it with a wrapper in the form
        // if there are any translatable elements there.
        $sub_data = $this->extractTranslatables($element, $config_data[$key], $element_key);
        if ($sub_data) {
          $data[$key] = $sub_data;
        }
      }
      else {
        if (!isset($definition['translatable']) || !isset($definition['type']) || empty($config_data[$key])) {
          continue;
        }
        $data[$key] = array(
          '#label' => $definition['label'],
          '#text' => $config_data[$key],
          '#translate' => TRUE,
        );
      }
    }
    return $data;
  }

}
