<?php

/**
 * @file
 * Contains \Drupal\tmgmt\Plugin\views\field\JobState.
 */

namespace Drupal\tmgmt\Plugin\views\field;

use Drupal\tmgmt\JobInterface;
use Drupal\tmgmt\JobItemInterface;
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
        $icon = drupal_get_path('module', 'tmgmt') . '/icons/rejected.svg';
        break;

      case JobInterface::STATE_ACTIVE:
        $needs_review = FALSE;
        /** @var JobItemInterface $item */
        foreach ($values->_entity->getItems() as $item) {
          if ($item->isNeedsReview()) {
            $needs_review = TRUE;
            break;
          }
        }
        if ($needs_review) {
          $label = t('Needs review');
          $icon = drupal_get_path('module', 'tmgmt') . '/icons/ready.svg';
          break;
        }
        $label = t('In progress');
        $icon = drupal_get_path('module', 'tmgmt') . '/icons/hourglass.svg';
        break;

      case JobInterface::STATE_CONTINUOUS:
        $label = t('Continuous');
        $icon = drupal_get_path('module', 'tmgmt') . '/icons/continuous.svg';
        break;

      case JobInterface::STATE_CONTINUOUS_INACTIVE:
        $label = t('Continuous Inactive');
        $icon = drupal_get_path('module', 'tmgmt') . '/icons/continuous_inactive.svg';
        break;

      default:
        $icon = NULL;
        $label = NULL;
    }
    $element = [
      '#type' => 'inline_template',
      '#template' => '{% if label %}<img src="{{ icon }}" title="{{ label }}"><span></span></img>{% endif %}',
      '#context' => [
        'icon' => file_create_url($icon),
        'label' => $label,
      ],
    ];
    return \Drupal::service('renderer')->render($element);
  }

}
