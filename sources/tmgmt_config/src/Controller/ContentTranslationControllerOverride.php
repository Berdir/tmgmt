<?php

/**
 * @file
 * Contains \Drupal\tmgmt_content\Controller\ContentTranslationControllerOverride.
 */

namespace Drupal\tmgmt_content\Controller;

use Drupal\content_translation\Controller\ContentTranslationController;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\HttpFoundation\Request;

/**
 * Overridden class for entity translation controllers.
 */
class ContentTranslationControllerOverride extends ContentTranslationController  {

  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function overview(Request $request) {
    $build = parent::overview($request);
    if (\Drupal::entityManager()->getAccessControlHandler('tmgmt_job')->createAccess()) {
      $build = \Drupal::formBuilder()->getForm('Drupal\tmgmt_content\Form\ContentTranslateForm', $build);
    }
    return $build;
  }

}
