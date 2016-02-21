<?php

/**
 * @file
 * Contains \Drupal\tmgmt\Plugin\views\field\Progress.
 */

namespace Drupal\tmgmt\Plugin\views\field;

use Drupal\views\ResultRow;

/**
 * Field handler which shows the progress of a job or job item.
 *
 * @ViewsField("tmgmt_progress")
 */
class Progress extends StatisticsBase {

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    $entity = $values->_entity;
    // If job has been aborted the status info is not applicable.
    if ($entity->isAborted()) {
      return t('N/A');
    }
    $counts = array(
      '@pending' => $entity->getCountPending(),
      '@translated' => $entity->getCountTranslated(),
      '@reviewed' => $entity->getCountReviewed(),
      '@accepted' => $entity->getCountAccepted(),
    );

    $title = t('Pending: @pending, translated: @translated, reviewed: @reviewed, accepted: @accepted.', $counts);

    $one_hundred_percent = array_sum($counts);
    if ($one_hundred_percent == 0) {
      return [];
    }

    $output[] = array(
      '#type' => 'inline_template',
      '#template' => '
        <div title="{{ title }}">
        {% if width_pending %}
          <div class="tmgmt-progress-pending" style="width: {{ width_pending }}%">{{ count_pending }}</div>
        {% endif %}
        {% if width_translated %}
          <div class="tmgmt-progress-translated" style="width: {{ width_translated }}%">{{ count_translated }}</div>
        {% endif %}
        {% if width_reviewed %}
          <div class="tmgmt-progress-reviewed" style="width: {{ width_reviewed }}%">{{ count_reviewed }}</div>
        {% endif %}
        {% if width_accepted %}
          <div class="tmgmt-progress-accepted" style="width: {{ width_accepted }}%">{{ count_accepted }}</div>
        {% endif %}
        </div>
          ',
      '#context' => array(
        'title' => $title,
        'count_pending' => $counts['@pending'],
        'count_translated' => $counts['@translated'],
        'count_reviewed' => $counts['@reviewed'],
        'count_accepted' => $counts['@accepted'],
        'width_pending' => $counts['@pending'] / $one_hundred_percent * 100,
        'width_translated' => $counts['@translated'] / $one_hundred_percent * 100,
        'width_reviewed' => $counts['@reviewed'] / $one_hundred_percent * 100,
        'width_accepted' => $counts['@accepted'] / $one_hundred_percent * 100,
      ),
    );

    return $output;
  }

}
