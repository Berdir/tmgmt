<?php

/**
 * @file
 * Contains \Drupal\tmgmt\Plugin\views\field\JobItemState.
 */

namespace Drupal\tmgmt\Plugin\views\field;

use Drupal\tmgmt\JobItemInterface;
use Drupal\views\Plugin\views\field\NumericField;
use Drupal\views\ResultRow;

/**
 * Field handler which shows the link for translating translation task items.
 *
 * @ViewsField("tmgmt_job_item_state")
 */
class JobItemState extends NumericField {

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    $value = parent::render($values);
    switch ($value) {
      case JobItemInterface::STATE_ACTIVE:
        $label = t('In progress');
        $icon = drupal_get_path('module', 'tmgmt') . '/icons/hourglass.svg';
        break;

      case JobItemInterface::STATE_REVIEW:
        $label = t('Needs review');
        $icon = drupal_get_path('module', 'tmgmt') . '/icons/ready.svg';
        break;

      default:
        $icon = NULL;
        $label = NULL;
    }
    $element = [
      '#type' => 'inline_template',
      '#template' => '{% if label %}<img src="{{ icon }}" title="{{ label }}"><span></span></img>{% endif %}',
      '#context' => array(
        'icon' => file_create_url($icon),
        'label' => $label,
      ),
    ];
    return \Drupal::service('renderer')->render($element);
  }

}
