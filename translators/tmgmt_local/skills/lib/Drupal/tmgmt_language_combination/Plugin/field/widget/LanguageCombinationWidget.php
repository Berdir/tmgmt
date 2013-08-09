<?php

/**
 * @file
 * Contains Drupal\tmgmt_language_combination\Plugin\field\widget\LanguageCombinationWidget.
 */

namespace Drupal\tmgmt_language_combination\Plugin\field\widget;

use Drupal\field\Annotation\FieldWidget;
use Drupal\Core\Annotation\Translation;
use Drupal\Core\Entity\Field\FieldInterface;
use Drupal\field\Plugin\Type\Widget\WidgetBase;

/**
 * Plugin implementation of the 'tmgmt_language_combination_default' widget.
 *
 * @FieldWidget(
 *   id = "tmgmt_language_combination_default",
 *   label = @Translation("Select list"),
 *   description = @Translation("Default widget for allowing users to define translation combination."),
 *   field_types = {
 *     "tmgmt_language_combination"
 *   }
 * )
 */
class LanguageCombinationWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldInterface $items, $delta, array $element, $langcode, array &$form, array &$form_state) {
    if (isset($form_state['list_all_languages'])) {
      $languages_options = tmgmt_language_combination_languages_predefined_list();
    }
    else {
      $languages_options = array();
      foreach (language_list() as $code => $language) {
        $languages_options[$code] = $language->name;
      }
    }

    $options = array('_none' => t('- None -')) + $languages_options;

    $element['language_from'] = array(
      '#type' => 'select',
      '#title' => t('From'),
      '#options' => $options,
      '#default_value' => isset($items[$delta]) ? $items[$delta]->language_from : '',
      '#attributes' => array('class' => array('from-language')),
    );

    $element['language_to'] = array(
      '#type' => 'select',
      '#title' => t('To'),
      '#options' => $options,
      '#default_value' => isset($items[$delta]) ? $items[$delta]->language_to : '',
      '#attributes' => array('class' => array('to-language')),
    );

    return $element;
  }

}
