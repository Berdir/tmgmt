<?php

/**
 * @file
 * Contains \Drupal\tmgmt\Plugin\views\field\JobOperations.
 */

namespace Drupal\tmgmt\Plugin\views\field;

use Drupal\Core\Url;
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
    if ($job->isSubmittable() && $job->access('submit')) {
      $element['#links']['submit'] = array(
        'url' => $job->urlInfo()->setOption('query', array('destination' => Url::fromRoute('<current>')->getInternalPath())),
        'title' => t('submit'),
      );
    }
    else {
      $element['#links']['manage'] = array(
        'url' => $job->urlInfo()->setOption('query', array('destination' => Url::fromRoute('<current>')->getInternalPath())),
        'title' => t('manage'),
      );
    }
    if ($job->isAbortable() && $job->access('submit')) {
      $element['#links']['cancel'] = array(
        'url' => $job->urlInfo('abort-form')->setOption('query', array('destination' => Url::fromRoute('<current>')->getInternalPath())),
        'title' => t('abort'),
      );
    }
    if ($job->isDeletable() && \Drupal::currentUser()->hasPermission('administer tmgmt')) {
      $element['#links']['delete'] = array(
        'url' => $job->urlInfo('delete-form')->setOption('query', array('destination' => Url::fromRoute('<current>')->getInternalPath())),
        'title' => t('delete'),
      );
    }
    return drupal_render($element);
  }

}
