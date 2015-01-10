<?php

/**
 * @file
 * Contains \Drupal\tmgmt_content\Controller\ContentTranslationControllerOverride.
 */

namespace Drupal\tmgmt_content\Controller;

use Drupal\content_translation\Controller\ContentTranslationController;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Overridden class for entity translation controllers.
 */
class ContentTranslationControllerOverride extends ContentTranslationController  {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function overview(RouteMatchInterface $route_match, $entity_type_id = NULL) {
    $build = parent::overview($route_match);
    if (\Drupal::entityManager()->getAccessControlHandler('tmgmt_job')->createAccess()) {
      $build = \Drupal::formBuilder()->getForm('Drupal\tmgmt_content\Form\ContentTranslateForm', $build);
    }
    return $build;
  }

}
