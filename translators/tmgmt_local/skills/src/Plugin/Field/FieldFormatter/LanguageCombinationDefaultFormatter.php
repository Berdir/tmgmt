<?php

/**
 * @file
 * Contains \Drupal\tmgmt_language_combination\Plugin\field\formatter\LanguageCombinationDefaultFormatter.
 */

namespace Drupal\tmgmt_language_combination\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;


/**
 * Plugin implementation of the 'tmgmt_language_combination_default' formatter.
 *
 * @FieldFormatter(
 *   id = "tmgmt_language_combination_default",
 *   label = @Translation("Default"),
 *   field_types = {
 *     "tmgmt_language_combination",
 *   }
 * )
 */
class LanguageCombinationDefaultFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements['#theme'] = 'item_list';
    $elements['#items'] = array();

    foreach ($items as $delta => $item) {
      $from = tmgmt_language_combination_language_label($item->language_from);
      $to = tmgmt_language_combination_language_label($item->language_to);
      $elements['#items'][$delta]['data'] = t('From @from to @to', array('@from' => $from, '@to' => $to));
      $elements['#items'][$delta]['class'][] = drupal_html_class($from . '-' . $to) . '">';
    }

    return $elements;
  }

}
