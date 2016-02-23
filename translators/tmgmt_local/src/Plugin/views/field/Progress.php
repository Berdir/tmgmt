<?php

/**
 * @file
 * Contains \Drupal\tmgmt_local\Plugin\views\field\Progress.
 */

namespace Drupal\tmgmt_local\Plugin\views\field;

use Drupal\tmgmt\Plugin\views\field\StatisticsBase;
use Drupal\views\ResultRow;

/**
 * Field handler which shows the progress of a job or job item.
 *
 * @ViewsField("tmgmt_local_progress")
 */
class Progress extends StatisticsBase {

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    /** @var \Drupal\tmgmt_local\LocalTaskInterface $entity */
    $entity = $values->_entity;
    $counts = array(
      '@untranslated' => $entity->getCountUntranslated(),
      '@translated' => $entity->getCountTranslated(),
      '@completed' => $entity->getCountCompleted(),
    );

    $title = t('Untranslated: @untranslated, translated: @translated, completed: @completed.', $counts);

    $one_hundred_percent = array_sum($counts);
    if ($one_hundred_percent == 0) {
      return [];
    }

    $output[] = array(
      '#type' => 'inline_template',
      '#template' => '
        <div title="{{ title }}">
        {% if width_untranslated %}
          <div class="tmgmt-local-progress-untranslated" style="width: {{ width_untranslated }}%">{{ count_untranslated }}</div>
        {% endif %}
        {% if width_translated %}
          <div class="tmgmt-local-progress-translated" style="width: {{ width_translated }}%">{{ count_translated }}</div>
        {% endif %}
        {% if width_completed %}
          <div class="tmgmt-local-progress-completed" style="width: {{ width_completed }}%">{{ count_completed }}</div>
        {% endif %}
        </div>
          ',
      '#context' => array(
        'title' => $title,
        'count_untranslated' => $counts['@untranslated'],
        'count_translated' => $counts['@translated'],
        'count_completed' => $counts['@completed'],
        'width_untranslated' => $counts['@untranslated'] / $one_hundred_percent * 100,
        'width_translated' => $counts['@translated'] / $one_hundred_percent * 100,
        'width_completed' => $counts['@completed'] / $one_hundred_percent * 100,
      ),
    );
    $output['#attached'] = [
      'library' => ['tmgmt/admin'],
    ];
    return $output;
  }

}
