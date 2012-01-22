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

    if ($job->isNew() || $job->isPrepared() | $job->isRejected()) {
      $element['#links']['checkout'] = array(
        'href' => "admin/tmgmt/checkout/$job->tjid",
        'title' => t('checkout'),
      );
    }

    if ($job->isNeedsReview()) {
      $element['#links']['review'] = array(
        'href' => "admin/tmgmt/review/$job->tjid",
        'title' => t('review'),
      );
      $element['#links']['accept'] = array(
        'href' => "admin/tmgmt/accept/$job->tjid",
        'title' => t('accept'),
      );
    }

    if (!$job->isAccepted() && !$job->isCancelled()) {
      $element['#links']['cancel'] = array(
        'href' => "admin/tmgmt/cancel/$job->tjid",
        'title' => t('cancel'),
      );
    }

    return drupal_render($element);
  }

}
