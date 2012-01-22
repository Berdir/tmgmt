<?php

/**
 * Field handler which shows the actions for a job.
 *
 * @ingroup views_field_handlers
 */
class tmgmt_handler_field_tmgmt_job_actions extends views_handler_field_entity {
  function render($values) {
    $job = $this->get_value($values);
    $element = array();
    $element['#theme'] = 'links';
    $uri = $job->uri();

    // @todo: Use entity_access links once they are implemented.
    $element['#links']['view'] = array(
      'href' => $uri['path'],
      'title' => t('view'),
    );
    $element['#links']['checkout'] = array(
      'href' => "admin/tmgmt/checkout/$job->tjid",
      'title' => t('checkout'),
    );

    return drupal_render($element);
  }

}
