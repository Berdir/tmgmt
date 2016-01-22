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

      case JobItemInterface::STATE_ACCEPTED:
        $label = t('Accepted');
        $icon = 'core/misc/icons/73b355/check.svg';
        break;

      case JobItemInterface::STATE_ABORTED:
        $label = t('Aborted');
        $icon = drupal_get_path('module', 'tmgmt') . '/icons/ex-red.svg';
        break;

      default:
        $label = t('In progress');
        $icon = drupal_get_path('module', 'tmgmt') . '/icons/ready.svg';
    }
    $element = [
      '#type' => 'inline_template',
      '#template' => '<img src="{{ icon }}" title="{{ label }}"><span></span></img>',
      '#context' => array(
        'icon' => file_create_url($icon),
        'label' => $label,
      ),
    ];
    return \Drupal::service('renderer')->render($element);
  }

}
