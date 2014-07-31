<?php

/**
 * @file
 * Contains \Drupal\tmgmt\Plugin\views\field\JobOperations.
 */

namespace Drupal\tmgmt\Plugin\views\field;

use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Field handler which shows the operations for a job.
 *
 * @ViewsField("tmgmt_job_operations")
 */
class JobOperations extends FieldPluginBase {

  function render(ResultRow $values) {
    $job = $values->_entity;
    $element = array();
    $element['#theme'] = 'links';
    $element['#attributes'] = array('class' => array('inline'));
    $uri = $job->urlInfo();
    if ($job->isSubmittable() && $job->access('submit')) {
      $element['#links']['submit'] = array(
        'query' => array('destination' => current_path()),
        'title' => t('submit'),
      ) + $uri->toArray();
    }
    else {
      $element['#links']['manage'] = array(
        'title' => t('manage'),
      ) + $uri->toArray();;
    }
    if ($job->isAbortable() && $job->access('submit')) {
      $element['#links']['cancel'] = array(
        'route_name' => 'tmgmt.job_entity_abort',
        'route_parameters' => array('tmgmt_job' => $job->id()),
        'query' => array('destination' => current_path()),
        'title' => t('abort'),
      );
    }
    if ($job->isDeletable() && \Drupal::currentUser()->hasPermission('administer tmgmt')) {
      $element['#links']['delete'] = array(
        'route_name' => 'tmgmt.job_entity_delete',
        'route_parameters' => array('tmgmt_job' => $job->id()),
        'query' => array('destination' => current_path()),
        'title' => t('delete'),
      );
    }
    return drupal_render($element);
  }

}
