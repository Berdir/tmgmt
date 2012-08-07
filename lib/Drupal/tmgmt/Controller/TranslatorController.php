<?php

/**
 * @file
 * Contains \Drupal\tmgmt\Controller\TranslatorController.
 */

namespace Drupal\tmgmt\Controller;

use Drupal\tmgmt\Plugin\Core\Entity\Translator;
use Drupal\Component\Utility\String;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Route controller class for the tmgmt translator entity.
 */
class TranslatorController {

  /**
   * Presents the translator edit form.
   *
   * @param \Drupal\tmgmt\Plugin\Core\Entity\Translator $tmgmt_translator
   *   The Translator object to edit.
   *
   * @return array
   *   A form array as expected by drupal_render().
   */
  public function edit(Translator $tmgmt_translator) {
    drupal_set_title(String::format('Edit %label', array('%label' => $tmgmt_translator->label())), PASS_THROUGH);
    return entity_get_form($tmgmt_translator);
  }

  /**
   * Enables a Translator object.
   *
   * @param \Drupal\tmgmt\Translator $tmgmt_translator
   *   The Translator object to enable.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect response to the tmgmt listing page.
   */
  function enable(Translator $tmgmt_translator) {
    $tmgmt_translator->enable()->save();
    return new RedirectResponse(url('admin/config/regional/tmgmt_translator', array('absolute' => TRUE)));
  }

  /**
   * Disables a Translator object.
   *
   * @param \Drupal\tmgmt\Translator $tmgmt_translator
   *   The Translator object to disable.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   A redirect response to the tmgmt listing page.
   */
  function disable(Translator $tmgmt_translator) {
    $tmgmt_translator->disable()->save();
    return new RedirectResponse(url('admin/config/regional/tmgmt_translator', array('absolute' => TRUE)));
  }

}
