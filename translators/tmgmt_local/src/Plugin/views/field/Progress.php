<?php

/**
 * @file
 * Contains \Drupal\tmgmt_local\Plugin\views\field\Progress.
 */

namespace Drupal\tmgmt_local\Plugin\views\field;

use Drupal\Component\Annotation\PluginID;
use Drupal\tmgmt_local\Entity\LocalTask;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Field handler which shows the progress of a job or job item.
 *
 * @ViewsField("tmgmt_local_progress")
 */
class Progress extends FieldPluginBase {

  /**
   * Prefetch statistics for all jobs.
   */
  function preRender(&$values) {
    parent::preRender($values);

    // In case of tasks, pre-fetch the statistics in a single query and add them
    // to the static cache.
    if ($this->getEntityType() == 'tmgmt_task') {
      $tltids = array();
      foreach ($values as $value) {
        $tltids[] = $value->tjid;
      }
      tmgmt_local_task_statistics_load($tltids);
    }
  }

  /**
   * {@inheritdoc}
   */
  function render(ResultRow $values) {

    $entity =  $values->_entity;
    $counts = array(
      '@untranslated' => $entity->getCountUntranslated(),
      '@translated' => $entity->getCountTranslated(),
      '@completed' => $entity->getCountCompleted(),
    );
    $id = $entity->id();

    if (\Drupal::moduleHandler()->moduleExists('google_chart_tools')) {
      draw_chart($this->build_progressbar_settings($id, $counts));
      return '<div id="progress' . $id . '"></div>';
    }
    $title = t('Untranslated: @untranslated, translated: @translated, completed: @completed.', $counts);
    return sprintf('<span title="%s">%s</span>', $title, implode('/', $counts));
  }

  /**
   * Creates a settings array for the google chart tools.
   *
   * The settings are preset with values to display a progress bar for either
   * a job or job item.
   *
   * @param $id
   *   The id of the chart.
   * @param $counts
   *   Array with the counts for accepted, translated and pending.
   * @param $prefix
   *   Prefix to id.
   * @return
   *   Settings array.
   */
  function build_progressbar_settings($id, $counts, $prefix = 'progress') {
    $settings['chart'][$prefix . $id] = array(
      'header' => array(t('Accepted'), t('Reviewed'), t('Translated'), t('Pending')),
      'rows' => array(
        array($counts['@accepted'], $counts['@reviewed'], $counts['@translated'], $counts['@pending']),
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
      )
    );
    return $settings;
  }

}
