<?php

/**
 * Field handler which shows the progressbar.
 *
 * @ingroup views_field_handlers
 */
class tmgmt_local_task_handler_field_progress extends tmgmt_handler_field_tmgmt_progress {

  /**
   * Prefetch statistics for all jobs.
   */
  function pre_render(&$values) {
    parent::pre_render($values);

    // In case of tasks, pre-fetch the statistics in a single query and add them
    // to the static cache.
    if ($this->entity_type == 'tmgmt_task') {
      $tltids = array();
      foreach ($values as $value) {
        $tltids[] = $value->tjid;
      }
      tmgmt_local_task_statistics_load($tltids);
    }
  }

}
