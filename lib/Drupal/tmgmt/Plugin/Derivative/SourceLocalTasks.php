<?php

/**
 * @file
 * Contains \Drupal\tmgmt\Plugin\Derivative\SourceLocalTasks.
 */

namespace Drupal\tmgmt\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DerivativeBase;
use Drupal\Core\Plugin\Discovery\ContainerDerivativeInterface;
use Drupal\tmgmt\SourceManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides dynamic local tasks for sources.
 */
class SourceLocalTasks extends DerivativeBase implements ContainerDerivativeInterface {

  /**
   * The source manager.
   *
   * @var \Drupal\tmgmt\SourceManager
   */
  protected $sourceManager;

  /**
   * Constructs a new SourceLocalTasks object.
   *
   * @param \Drupal\tmgmt\SourceManager $source_manager
   *   The source manager.
   */
  public function __construct(SourceManager $source_manager) {
    $this->sourceManager = $source_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static(
      $container->get('plugin.manager.tmgmt.source')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions(array $base_plugin_definition) {
    // Create tabs for all possible source item types.
    $weight = 0;
    foreach ($this->sourceManager->getDefinitions() as $type => $definition) {
      $plugin = $this->sourceManager->createInstance($type);
      foreach ($plugin->getItemTypes() as $item_type => $item_label) {
        $this->derivatives[$type . ':' . $item_type] = $base_plugin_definition;
        $this->derivatives[$type . ':' . $item_type]['title'] = $item_label;
        $this->derivatives[$type . ':' . $item_type]['weight'] = $weight++;
        $this->derivatives[$type . ':' . $item_type]['route_parameters'] = array(
          'plugin' => $type,
          'item_type' => $item_type,
        );
      }
    }
    return parent::getDerivativeDefinitions($base_plugin_definition);
  }

}
