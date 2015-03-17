<?php

/**
 * @file
 * Contains \Drupal\tmgmt\Plugin\views\field\Progress.
 */

namespace Drupal\tmgmt\Plugin\views\field;

use Drupal\Component\Annotation\PluginID;
use Drupal\tmgmt\Entity\Job;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Field handler which shows the progress of a job or job item.
 *
 * @ViewsField("tmgmt_progress")
 */
class Progress extends FieldPluginBase {

  /**
   * Prefetch statistics for all jobs.
   */
  function preRender(&$values) {
    parent::preRender($values);

    // In case of jobs, pre-fetch the statistics in a single query and add them
    // to the static cache.
    if ($this->getEntityType() == 'tmgmt_job') {
      $tjids = array();
      foreach ($values as $value) {
        // Do not load statistics for aborted jobs.
        if ($value->_entity->tmgmt_job_state == Job::STATE_ABORTED) {
          continue;
        }
        $tjids[] = $value->tjid;
      }
      tmgmt_job_statistics_load($tjids);
    }
  }

  /**
   * {@inheritdoc}
   */
  function render(ResultRow $values) {
    $entity = $values->_entity;
    // If job has been aborted the status info is not applicable.
    if ($entity->isAborted()) {
      return t('N/A');
    }
    $counts = array(
      '@accepted' => $entity->getCountAccepted(),
      '@reviewed' => $entity->getCountReviewed(),
      '@translated' => $entity->getCountTranslated(),
      '@pending' => $entity->getCountPending(),
    );
    $id = $entity->id();

    if (\Drupal::moduleHandler()->moduleExists('google_chart_tools')) {
      draw_chart($this->build_progressbar_settings($id, $counts));
      return '<div id="progress' . $id . '"></div>';
    }
    $title = t('Accepted: @accepted, reviewed: @reviewed, translated: @translated, pending: @pending.', $counts);
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
