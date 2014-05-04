<?php

/**
 * @file
 * Contains \Drupal\tmgmt\Plugin\views\field\JobItemOperations.
 */

namespace Drupal\tmgmt\Plugin\views\field;

use Drupal\Component\Annotation\PluginID;
use Drupal\views\Plugin\views\area\Result;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Field handler which shows the operations for a job.
 *
 * @ViewsField("tmgmt_job_item_operations")
 */
class JobItemOperations extends FieldPluginBase {

  function render(ResultRow $values) {
    $item = $values->_entity;
    $element = array();
    $element['#theme'] = 'links';
    $element['#attributes'] = array('class' => array('inline'));
    $url = $item->urlInfo();
    if ($item->getCountTranslated() > 0 && $item->access('accept')) {
      $element['#links']['review'] = array(
        'query' => array('destination' => current_path()),
        'title' => t('review'),
      ) + $url->toArray();
    }
    // Do not display view on unprocessed jobs.
    elseif (!$item->getJob()->isUnprocessed()) {
      $element['#links']['view'] = array(
        'query' => array('destination' => current_path()),
        'title' => t('view'),
      ) + $url->toArray();
    }
    if (user_access('administer tmgmt') && !$item->isAccepted()) {
      $element['#links']['delete'] = array(
        'route_name' => 'tmgmt.job_item_delete',
        'route_parameters' => array('tmgmt_job_item' => $item->id()),
        'query' => array('destination' => current_path()),
        'title' => t('delete'),
      );
    }
    return drupal_render($element);
  }

}
