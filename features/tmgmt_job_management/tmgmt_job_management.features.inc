<?php
/**
 * @file
 * tmgmt_job_management.features.inc
 */

/**
 * Implements hook_views_api().
 */
function tmgmt_job_management_views_api() {
  list($module, $api) = func_get_args();
  if ($module == "views" && $api == "views_default") {
    return array("version" => "3.0");
  }
}
