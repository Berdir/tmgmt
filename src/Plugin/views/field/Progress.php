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

    $output = array(
      '#theme' => 'tmgmt_progress_bar',
      '#attached' => array('library' => 'tmgmt/admin'),
      '#title' => $title,
      '#count_pending' => $counts['@pending'],
      '#count_translated' => $counts['@translated'],
      '#count_reviewed' => $counts['@reviewed'],
      '#count_accepted' => $counts['@accepted'],
      '#width_pending' => $counts['@pending'] / $one_hundred_percent * 100,
      '#width_translated' => $counts['@translated'] / $one_hundred_percent * 100,
      '#width_reviewed' => $counts['@reviewed'] / $one_hundred_percent * 100,
      '#width_accepted' => $counts['@accepted'] / $one_hundred_percent * 100,
    );
    return $output;
  }

}
