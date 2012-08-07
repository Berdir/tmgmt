<?php

/**
 * @file
 * Contains \Drupal\tmgmt\Plugin\views\field\Translator.
 */

namespace Drupal\tmgmt\Plugin\views\field;

use Drupal\Component\Annotation\PluginID;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Field handler which shows the operations for a job.
 *
 * @PluginID("tmgmt_translator")
 */
class Translator extends FieldPluginBase {

  function render(ResultRow $values) {
    if ($job = $values->_entity) {
      $translator = $job->getTranslator();
      return $translator ? $translator->label() : t('Missing translator');
    }
  }
}
