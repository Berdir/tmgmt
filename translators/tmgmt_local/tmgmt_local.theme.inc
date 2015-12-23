<?php

/**
 * @file
 * Theme file stub for tmgmt.
 */

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\SafeMarkup;
use Drupal\Core\Template\Attribute;
use Drupal\Core\Url;
use Drupal\tmgmt\Entity\JobItem;
use Drupal\Core\Render\Element;

/**
 * Render one single data item as a table row.
 */
function tmgmt_local_translation_form_element($variables) {
  // Theme table which contains source, translation and action state button.
  $element = $variables['element'];

  $parts = explode('|', $element['#parents'][0]);
  $header_title = ucfirst(str_replace('_', ' ', $parts[0]));

  // Container for ajax messages.
  $result = '<div id="tmgmt-status-messages-' . strtolower($element['#parent_label'][0]) . '"></div>';

  $table = [
    '#type' => 'table',
    '#attributes' => array(
      'id' => $element['#ajaxid'],
      'class' => array($element['#zebra']),
    ),
    '#header' => [
      [
        'data' => $header_title,
        'colspan' => 3,
      ]
    ],
    '#rows' => array(
      array(
        'data' => array(
          \Drupal::service('renderer')->render($element['source']),
          \Drupal::service('renderer')->render($element['translation']),
          \Drupal::service('renderer')->render($element['actions']),
        ),
      ),
    ),
  ];
  $result .= \Drupal::service('renderer')->render($table);

  return $result;
}
