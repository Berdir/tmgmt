<?php

/**
 * @file
 * Contains \Drupal\tmgmt_entity_ui\Controller\TmgmtContentTranslationControllerOverride.
 */

namespace Drupal\tmgmt_entity_ui\Controller;

use Drupal\content_translation\Controller\ContentTranslationController;
use Symfony\Component\HttpFoundation\Request;

/**
 * Overridden class for entity translation controllers.
 */
class TmgmtContentTranslationControllerOverride extends ContentTranslationController  {

  /**
   * {@inheritdoc}
   */
  public function overview(Request $request) {
    $build = parent::overview($request);
    if (\Drupal::entityManager()->getAccessController('tmgmt_job')->createAccess()) {
      module_load_include('inc', 'tmgmt_entity_ui', 'tmgmt_entity_ui.pages');
      $build = drupal_get_form('tmgmt_entity_ui_translate_form', $build);
    }
    return $build;
  }

}
