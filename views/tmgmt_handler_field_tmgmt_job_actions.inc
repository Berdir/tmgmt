<?php

/**
 * Field handler which shows the actions for a job.
 *
 * @ingroup views_field_handlers
 */
class tmgmt_handler_field_tmgmt_job_actions extends views_handler_field_entity {
  function render($values) {
    $job = $this->get_value($values);
    /* @var $job TMGMTJob */
    $element = array();
    $element['#theme'] = 'links';
    $element['#attributes'] = array('class' => array('inline'));
    $uri = $job->uri();
    $element['#links']['view'] = array(
      'href' => $uri['path'],
      'title' => t('view'),
    );
    if ($job->isUnprocessed() || $job->isRejected() || $job->isCancelled()) {
      $element['#links']['checkout'] = array(
        'href' => "admin/config/regional/tmgmt/jobs/$job->tjid/manage",
        'title' => t('checkout'),
      );
    }
    if ($job->isNeedsReview()) {
      $element['#links']['review'] = array(
        'href' => "admin/config/regional/tmgmt/jobs/$job->tjid/review",
        'title' => t('review'),
      );
      $element['#links']['accept'] = array(
        'href' => "admin/config/regional/tmgmt/jobs/$job->tjid/accept",
        'title' => t('accept'),
      );
    }
    if ($job->isActive() || $job->isNeedsReview()) {
      $element['#links']['cancel'] = array(
        'href' => "admin/config/regional/tmgmt/jobs/$job->tjid/cancel",
        'title' => t('cancel'),
      );
    }
    else {
      $element['#links']['delete'] = array(
        'href' => "admin/config/regional/tmgmt/jobs/$job->tjid/delete",
        'title' => t('delete'),
      );
    }
    return drupal_render($element);
  }

}
