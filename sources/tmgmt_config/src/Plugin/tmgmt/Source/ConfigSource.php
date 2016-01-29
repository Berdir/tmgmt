<?php

/**
 * @file
 * Contains Drupal\tmgmt_config\ConfigSource.
 */

namespace Drupal\tmgmt_config\Plugin\tmgmt\Source;

use Drupal\config_translation\ConfigMapperManagerInterface;
use Drupal\config_translation\Form\ConfigTranslationFormBase;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\Schema\Mapping;
use Drupal\Core\Config\Schema\Sequence;
use Drupal\Core\Config\TypedConfigManagerInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\language\ConfigurableLanguageManagerInterface;
use Drupal\tmgmt\JobItemInterface;
use Drupal\tmgmt\SourcePluginBase;
use Drupal\tmgmt\TMGMTException;
use Drupal\Core\Render\Element;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Content entity source plugin controller.
 *
 * @SourcePlugin(
 *   id = "config",
 *   label = @Translation("Config Entity"),
 *   description = @Translation("Source handler for config entities."),
 *   ui = "Drupal\tmgmt_config\ConfigSourcePluginUi"
 * )
 */
class ConfigSource extends SourcePluginBase implements ContainerFactoryPluginInterface {

  /**
   * Item type for simple configuration.
   *
   * @var string
   */
  const SIMPLE_CONFIG = '_simple_config';

  /**
   * The configuration mapper manager.
   *
   * @var \Drupal\config_translation\ConfigMapperManagerInterface
   */
  protected $configMapperManager;

  /**
   * The injected entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * Configuration factory manager
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactoryManager;

  /**
   * @var \Drupal\language\ConfigurableLanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Constructs a ConfigTranslationController.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\config_translation\ConfigMapperManagerInterface $config_mapper_manager
   *   The configuration mapper manager.
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager.
   * @param  \Drupal\Core\Config\TypedConfigManagerInterface $typedConfigManagerInterface
   *   The typed config.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory.
   * @param \Drupal\language\ConfigurableLanguageManagerInterface
   *   Configurable language manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ConfigMapperManagerInterface $config_mapper_manager, EntityManagerInterface $entity_manager, TypedConfigManagerInterface $typedConfigManagerInterface, ConfigFactoryInterface $config_factory, ConfigurableLanguageManagerInterface $language_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configMapperManager = $config_mapper_manager;
    $this->entityManager = $entity_manager;
    $this->typedConfig = $typedConfigManagerInterface;
    $this->configFactoryManager = $config_factory;
    $this->languageManager = $language_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('plugin.manager.config_translation.mapper'), $container->get('entity.manager'), $container->get('config.typed'), $container->get('config.factory'), $container->get('language_manager'));
  }

  /**
   * Gets the mapper.
   *
   * @param \Drupal\tmgmt\JobItemInterface $job_item
   *   Gets a job item as a parameter.
   *
   * @return \Drupal\config_translation\ConfigMapperInterface $config_mapper
   *   Returns the config mapper.
   *
   * @throws \Drupal\tmgmt\TMGMTException
   *   If there is no entity, throws an exception.
   */
  protected function getMapper(JobItemInterface $job_item) {
    // @todo: Inject dependencies.
    if ($job_item->getItemType() == static::SIMPLE_CONFIG) {
      $config_mapper =$this->configMapperManager->createInstance($job_item->getItemId());
    }
    else {
      $config_mapper = $this->configMapperManager->createInstance($job_item->getItemType());

      /** @var \Drupal\Core\Config\Entity\ConfigEntityTypeInterface $entity_type */
      $entity_type = $this->entityManager->getDefinition($config_mapper->getType());

      $pos = strpos($job_item->getItemId(), $entity_type->getConfigPrefix());
      if (($pos !== FALSE)) {
        $entity_id = str_replace($entity_type->getConfigPrefix() . '.', '', $job_item->getItemId());
      }
      else {
        throw new TMGMTException(t('Item ID does not contain the full config object name.'));
      }

      $entity = $this->entityManager->getStorage($config_mapper->getType())->load($entity_id);
      if (!$entity) {
        throw new TMGMTException(t('Unable to load entity %type with id %id', array('%type' => $job_item->getItemType(), '%id' => $entity_id)));
      }
      $config_mapper->setEntity($entity);
    }
    return $config_mapper;
  }

  /**
   * {@inheritdoc}
   */
  public function getLabel(JobItemInterface $job_item) {
    try {
      return $this->getMapper($job_item)->getTitle();
    }
    catch (TMGMTException $e) {
      drupal_set_message(t('Title can not be displayed, the entity does not exist: %error.', array(
        '%error' => $e->getMessage(),
      )), 'error');
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getUrl(JobItemInterface $job_item) {
    try {
      $config_mapper = $this->getMapper($job_item);
      return Url::fromRoute($config_mapper->getBaseRouteName(), $config_mapper->getBaseRouteParameters());
    }
    catch (TMGMTException $e) {
      drupal_set_message(t('Url can not be displayed, the entity does not exist: %error.', array(
        '%error' => $e->getMessage(),
      )), 'error');
    }
    return NULL;
  }

  /**
   * Implements TMGMTEntitySourcePluginController::getData().
   *
   * Returns the data from the fields as a structure that can be processed by
   * the Translation Management system.
   */
  public function getData(JobItemInterface $job_item) {
    $config_mapper = $this->getMapper($job_item);
    $data = array();
    foreach ($config_mapper->getConfigData() as $config_id => $config_data) {
      $schema = $this->typedConfig->get($config_id);
      $config_id = str_replace('.', '__', $config_id);
      $data[$config_id] = $this->extractTranslatables($schema, $config_data);
    }
    // If there is only one, we simplify the data and return it.
    if (count($data) == 1) {
      return reset($data);
    }
    else {
      return $data;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function saveTranslation(JobItemInterface $job_item, $target_langcode) {
    try {
      $config_mapper = $this->getMapper($job_item);
    }
    catch (TMGMTException $e) {
      $job_item->addMessage('The entity %id of type %type does not exist, the job can not be completed.', array(
        '%id' => $job_item->getItemId(),
        '%type' => $job_item->getItemType(),
      ), 'error');
      return FALSE;
    }

    $data = $job_item->getData();

    $config_names = $config_mapper->getConfigNames();

    // We need to refactor the array just as we did in getData.
    if (count($config_names) == 1) {
      $data[$config_names[0]] = $data;
    }
    else {

      // Replace the arrays keys back.
      foreach ($data as $key => $value) {
        $new_key = str_replace('__', '.', $key);
        $data[$new_key] = $value;
        unset($data[$key]);
      }
    }

    foreach ($config_mapper->getConfigNames() as $name) {
      $schema = $this->typedConfig->get($name);

      // Set configuration values based on form submission and source values.
      $base_config = $this->configFactoryManager->getEditable($name);
      $config_translation = $this->languageManager->getLanguageConfigOverride($target_langcode, $name);

      $element = ConfigTranslationFormBase::createFormElement($schema);

      $element->setConfig($base_config, $config_translation, $this->convertToTranslation($data[$name]));

      // If no overrides, delete language specific configuration file.
      $saved_config = $config_translation->get();
      if (empty($saved_config)) {
        $config_translation->delete();
      }
      else {
        $config_translation->save();
      }
    }
    return TRUE;
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
    // Only entity types are exposed as their own item type, all others are
    // grouped together in simple config.
    $entity_types = $this->entityManager->getDefinitions();
    $definitions = $this->configMapperManager->getDefinitions();
    $types = array();
    foreach ($definitions as $definition_name => $definition) {
      if (isset($definition['entity_type'])) {
        $types[$definition['entity_type']] = (string) $entity_types[$definition['entity_type']]->getLabel();
      }
    }
    $types[static::SIMPLE_CONFIG] = t('Simple configuration');
    return $types;
  }

  /**
   * {@inheritdoc}
   */
  public function getItemTypeLabel($type) {
    $item_types = $this->getItemTypes();
    return $item_types[$type];
  }

  /**
   * {@inheritdoc}
   */
  public function getType(JobItemInterface $job_item) {
    if ($job_item->getItemType() == static::SIMPLE_CONFIG) {
      $definition = $this->configMapperManager->getDefinition($job_item->getItemId());
    }
    else {
      $definition = $this->configMapperManager->getDefinition($job_item->getItemType());
    }
    return $definition['title'];
  }

  /**
   * {@inheritdoc}
   */
  public function getSourceLangCode(JobItemInterface $job_item) {
    $config_mapper = $this->getMapper($job_item);
    return $config_mapper->getLangcode();
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
      $element_key = isset($base_key) ? "$base_key.$key" : $key;
      $definition = $element->getDataDefinition();
        // + array('label' => t('N/A'));
      if ($element instanceof Mapping || $element instanceof Sequence) {
        // Build sub-structure and include it with a wrapper in the form
        // if there are any translatable elements there.
        $sub_data = $this->extractTranslatables($element, $config_data[$key], $element_key);
        if ($sub_data) {
          $data[$key] = $sub_data;
          $data[$key]['#label'] = $definition->getLabel();
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
