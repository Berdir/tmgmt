<?php

/**
 * Field handler to show the label of a job.
 *
 * @ingroup views_field_handlers
 */
class tmgmt_handler_field_tmgmt_job_label extends views_handler_field_entity {
  function render($values) {
    /**
     * @var TMGMTJob
     */
    $job = $this->get_value($values);
    if ($job) {
      return $job->label();
    }
  }

}
