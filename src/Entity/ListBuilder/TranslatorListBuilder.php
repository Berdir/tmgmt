<?php

/**
 * @file
 * Contains \Drupal\tmgmt\Entity\ListBuilder\TranslatorListBuilder.
 */

namespace Drupal\tmgmt\Entity\ListBuilder;

use Drupal\Core\Config\Entity\DraggableListBuilder;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilderInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\tmgmt\TranslatorManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a listing of translators.
 */
class TranslatorListBuilder extends DraggableListBuilder implements EntityListBuilderInterface {
  /**
   * The entity storage class.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $storage;

  /**
   * Information about the entity type.
   *
   * @var \Drupal\tmgmt\TranslatorManager $translatorManager
   */
  protected $translatorManager;

  /**
   * Constructs a TranslatorListBuilder object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type definition.
   * @param \Drupal\Core\Entity\EntityStorageInterface $storage
   *   The config storage definition.
   * @param \Drupal\tmgmt\TranslatorManager $translator_manager
   *   The language manager.
   */
  public function __construct(EntityTypeInterface $entity_type, EntityStorageInterface $storage, TranslatorManager $translator_manager) {
    parent::__construct($entity_type, $storage);
    $this->storage = $storage;
    $this->translatorManager = $translator_manager;
  }

  /**
   * Creates the instance of the list builder.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The container entity.
   * @param \Drupal\Core\Entity\EntityTypeInterface $entity_type
   *   The entity type which should be created.
   *
   * @return TranslatorListBuilder
   *   The created instance of out list builder.
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $entity_type,
      $container->get('entity.manager')->getStorage($entity_type->id()),
      $container->get('plugin.manager.tmgmt.translator')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormID() {
    return 'tmgmt_translator_overview';
  }

  /**
   * {@inheritdoc}
   */
  public function buildHeader() {
    $header['label'] = t('Translator name');
    $installed_translators = $this->translatorManager->getLabels();
    if (empty($installed_translators)) {
      drupal_set_message(t("There are no translator plugins available. Please install a translator plugin."), 'error');
    }
    return $header + parent::buildHeader();
  }

  /**
   * {@inheritdoc}
   */
  public function buildRow(EntityInterface $entity) {
    $row['label'] = $this->getLabel($entity);
    return $row + parent::buildRow($entity);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
    drupal_set_message(t('The order of the translators has been saved.'));
  }

}
