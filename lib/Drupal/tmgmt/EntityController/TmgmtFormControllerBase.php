<?php

/**
 * @file
 * Contains \Drupal\tmgmt\EntityController\TmgmtFormControllerBase.
 */

namespace Drupal\tmgmt\EntityController;

use Drupal\Core\Entity\EntityControllerInterface;
use Drupal\Core\Entity\EntityFormController;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\tmgmt\SourceManager;
use Drupal\tmgmt\TranslatorManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form controller for the job item edit forms.
 *
 * @ingroup tmgmt_job
 */
class TmgmtFormControllerBase extends EntityFormController implements EntityControllerInterface {

  /**
   * Translator plugin manager.
   *
   * @var \Drupal\tmgmt\TranslatorManager
   */
  protected $translatorManager;

  /**
   * Source plugin manager.
   *
   * @var \Drupal\tmgmt\SourceManager
   */
  protected $sourceManager;

  /**
   * Constructs an EntityFormController object.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface
   *   The module handler service.
   * @param \Drupal\tmgmt\TranslatorManager $translator_manager
   *   The translator plugin manager.
   */
  public function __construct(ModuleHandlerInterface $module_handler, TranslatorManager $translator_manager, SourceManager $source_manager) {
    parent::__construct($module_handler);
    $this->translatorManager = $translator_manager;
    $this->sourceManager = $source_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, $entity_type, array $entity_info) {
    return new static(
      $container->get('module_handler'),
      $container->get('plugin.manager.tmgmt.translator'),
      $container->get('plugin.manager.tmgmt.source')
    );
  }

}
