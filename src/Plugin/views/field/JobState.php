<?php

/**
 * @file
 * Contains \Drupal\tmgmt\Plugin\views\field\JobState.
 */

namespace Drupal\tmgmt\Plugin\views\field;

use Drupal\tmgmt\JobInterface;
use Drupal\views\Plugin\views\field\NumericField;
use Drupal\views\ResultRow;

/**
 * Field handler which shows the state icons for jobs.
 *
 * @ViewsField("tmgmt_job_state")
 */
class JobState extends NumericField {

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    $value = parent::render($values);
    switch ($value) {
      case JobInterface::STATE_UNPROCESSED:
        $label = t('Unprocessed');
        $icon = drupal_get_path('module', 'tmgmt') . '/icons/hourglass.svg';
        break;

      case JobInterface::STATE_ACTIVE:
        $label = t('Active');
        $icon = drupal_get_path('module', 'tmgmt') . '/icons/ready.svg';
        break;

      case JobInterface::STATE_REJECTED:
        $label = t('Rejected');
        $icon = 'core/misc/icons/787878/ex.svg';
        break;

      case JobInterface::STATE_ABORTED:
        $label = t('Aborted');
        $icon = drupal_get_path('module', 'tmgmt') . '/icons/ex-red.svg';
        break;

      case JobInterface::STATE_FINISHED:
        $label = t('Finished');
        $icon = 'core/misc/icons/73b355/check.svg';
        break;

      default:
        $label = t('Unprocessed');
        $icon = drupal_get_path('module', 'tmgmt') . '/icons/hourglass.svg';
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
