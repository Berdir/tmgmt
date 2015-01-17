<?php

/**
 * @file
 * Contains \Drupal\tmgmt\Entity\Formr\TmgmtFormBase.
 */

namespace Drupal\tmgmt\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\tmgmt\SourceManager;
use Drupal\tmgmt\TranslatorManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form controller for the job item edit forms.
 *
 * @ingroup tmgmt_job
 */
class TmgmtFormBase extends ContentEntityForm {

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
   * Renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Constructs an EntityForm object.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager service.
   * @param \Drupal\tmgmt\TranslatorManager $translator_manager
   *   The translator plugin manager.
   * @param \Drupal\tmgmt\SourceManager $source_manager
   *   The translation source manager.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   */
  public function __construct(EntityManagerInterface $entity_manager, TranslatorManager $translator_manager, SourceManager $source_manager, RendererInterface $renderer) {
    $this->entityManager = $entity_manager;
    $this->translatorManager = $translator_manager;
    $this->sourceManager = $source_manager;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.manager'),
      $container->get('plugin.manager.tmgmt.translator'),
      $container->get('plugin.manager.tmgmt.source'),
      $container->get('renderer')
    );
  }

}
