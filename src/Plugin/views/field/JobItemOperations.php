<?php

/**
 * @file
 * Contains \Drupal\tmgmt\Plugin\views\field\JobItemOperations.
 */

namespace Drupal\tmgmt\Plugin\views\field;

use Drupal\Core\Url;
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
    if ($item->getCountTranslated() > 0 && $item->access('accept')) {
      $element['#links']['review'] = array(
        'url' => $item->urlInfo()->setOption('query', array('destination' => Url::fromRoute('<current>')->getInternalPath())),
        'title' => t('review'),
      );
    }
    // Do not display view on unprocessed jobs.
    elseif (!$item->getJob()->isUnprocessed()) {
      $element['#links']['view'] = array(
        'url' => $item->urlInfo()->setOption('query', array('destination' => Url::fromRoute('<current>')->getInternalPath())),
        'title' => t('view'),
      );
    }
    if (\Drupal::currentUser()->hasPermission('administer tmgmt') && !$item->isAccepted()) {
      $element['#links']['delete'] = array(
        'url' => $item->urlInfo('delete-form')->setOption('query', array('destination' => Url::fromRoute('<current>')->getInternalPath())),
        'title' => t('delete'),
      );
    }
    return \Drupal::service('renderer')->render($element);
  }

}
