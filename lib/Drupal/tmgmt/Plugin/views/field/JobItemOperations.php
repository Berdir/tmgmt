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
 * @PluginID("tmgmt_job_item_operations")
 */
class JobItemOperations extends FieldPluginBase {

  function render(ResultRow $values) {
    $item = $values->_entity;
    $element = array();
    $element['#theme'] = 'links';
    $element['#attributes'] = array('class' => array('inline'));
    $uri = $item->uri();
    if ($item->getCountTranslated() > 0 && $item->access('accept')) {
      $element['#links']['review'] = array(
        'href' => $uri['path'],
        'query' => array('destination' => current_path()),
        'title' => t('review'),
      );
    }
    // Do not display view on unprocessed jobs.
    elseif (!$item->getJob()->isUnprocessed()) {
      $element['#links']['view'] = array(
        'href' => $uri['path'],
        'query' => array('destination' => current_path()),
        'title' => t('view'),
      );
    }
    if (user_access('administer tmgmt') && !$item->isAccepted()) {
      $element['#links']['delete'] = array(
        'href' => 'admin/tmgmt/items/' . $item->id() . '/delete',
        'query' => array('destination' => current_path()),
        'title' => t('delete'),
      );
    }
    return drupal_render($element);
  }

}
