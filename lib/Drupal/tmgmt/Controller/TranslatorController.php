<?php

/**
 * @file
 * Contains \Drupal\tmgmt\Controller\TranslatorController.
 */

namespace Drupal\tmgmt\Controller;

use Drupal\tmgmt\Entity\Translator;
use Drupal\Component\Utility\String;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * Route controller class for the tmgmt translator entity.
 */
class TranslatorController {

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
