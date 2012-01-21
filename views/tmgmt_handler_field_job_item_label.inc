<?php

/**
 * Field handler which shows a label of a job item.
 *
 * @ingroup views_field_handlers
 */
class tmgmt_handler_field_job_item_label extends views_handler_field_entity {
  function render($values) {
    $job_item_id = $this->get_value($values, 'tjiid');

    /**
     * @var TMGMTJobItem
     */
    $job_item = $this->get_value($values);
    return $job_item->label();
  }
}
