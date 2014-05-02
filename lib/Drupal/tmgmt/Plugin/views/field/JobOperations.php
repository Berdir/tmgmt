<?php

/**
 * @file
 * Contains \Drupal\tmgmt\Plugin\views\field\JobOperations.
 */

namespace Drupal\tmgmt\Plugin\views\field;

use Drupal\Component\Annotation\PluginID;
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
    $uri = $job->uri();
    if ($job->isSubmittable() && $job->access('submit')) {
      $element['#links']['submit'] = array(
        'href' => $uri['path'],
        'query' => array('destination' => current_path()),
        'title' => t('submit'),
      );
    }
    else {
      $element['#links']['manage'] = array(
        'href' => $uri['path'],
        'title' => t('manage'),
      );
    }
    if ($job->isAbortable() && $job->access('submit')) {
      $element['#links']['cancel'] = array(
        'href' => 'admin/tmgmt/jobs/' . $job->id() . '/abort',
        'query' => array('destination' => current_path()),
        'title' => t('abort'),
      );
    }
    if ($job->isDeletable() && user_access('administer tmgmt')) {
      $element['#links']['delete'] = array(
        'href' => 'admin/tmgmt/jobs/' . $job->id() . '/delete',
        'query' => array('destination' => current_path()),
        'title' => t('delete'),
      );
    }
    return drupal_render($element);
  }

}
