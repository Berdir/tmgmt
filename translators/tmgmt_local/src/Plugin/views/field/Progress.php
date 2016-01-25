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
    $id = $entity->id();

    if (\Drupal::moduleHandler()->moduleExists('google_chart_tools')) {
      draw_chart($this->buildProgressbarSettings($id, $counts));
      return '<div id="progress' . $id . '"></div>';
    }
    $title = t('Untranslated: @untranslated, translated: @translated, completed: @completed.', $counts);
    $complete_title = t('<span title="@title">@values</span>', ['@title' => $title, '@values' => implode('/', $counts)]);
    return $complete_title;
  }

  /**
   * Creates a settings array for the google chart tools.
   *
   * The settings are preset with values to display a progress bar for either
   * a job or job item.
   *
   * @param string $id
   *   The id of the chart.
   * @param array $counts
   *   Array with the counts for accepted, translated and pending.
   * @param string $prefix
   *   Prefix to id.
   *
   * @return array
   *   Settings array.
   */
  protected function buildProgressbarSettings($id, array $counts, $prefix = 'progress') {
    $settings['chart'][$prefix . $id] = array(
      'header' => [t('Accepted'), t('Reviewed'), t('Translated'), t('Pending')],
      'rows' => array(
        [$counts['@accepted'], $counts['@reviewed'], $counts['@translated'], $counts['@pending']],
      ),
      'columns' => array(''),
      'chartType' => 'PieChart',
      'containerId' => $prefix . $id,
      'options' => array(
        'backgroundColor' => 'transparent',
        'colors' => array('#00b600', '#60ff60', '#ffff00', '#6060ff'),
        'forceIFrame' => FALSE,
        'chartArea' => array(
          'left' => 0,
          'top' => 0,
          'width' => '50%',
          'height' => '100%',
        ),
        'fontSize' => 9,
        'title' => t('Progress'),
        'titlePosition' => 'none',
        'width' => 60,
        'height' => 50,
        'isStacked' => TRUE,
        'legend' => array('position' => 'none'),
        'pieSliceText' => 'none',
      ),
    );
    return $settings;
  }

}
