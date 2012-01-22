<?php

/**
 * Field handler which shows a label of a job item.
 *
 * @ingroup views_field_handlers
 */
class tmgmt_handler_field_tmgmt_job_item_label extends views_handler_field_entity {
  function render($values) {
    /**
     * @var TMGMTJobItem
     */
    $job_item = $this->get_value($values);
    if ($job_item) {
      return $job_item->label();
    }
  }
}
