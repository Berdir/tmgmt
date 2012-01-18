<?php
/**
 * @file
 * tmgmt_rules_job_autosubmit.rules_defaults.inc
 */

/**
 * Implements hook_default_rules_configuration().
 */
function tmgmt_rules_job_autosubmit_default_rules_configuration() {
  $items = array();
  $items['rules_auto_accept_a_translation_job'] = entity_import('rules_config', '{ "rules_auto_accept_a_translation_job" : {
      "LABEL" : "Auto-Accept a translation job",
      "PLUGIN" : "reaction rule",
      "REQUIRES" : [ "rules", "tmgmt" ],
      "ON" : [ "tmgmt_job_update" ],
      "IF" : [ { "data_is" : { "data" : [ "tmgmt-job:state" ], "value" : "4" } } ],
      "DO" : [ { "tmgmt_rules_job_accept_translation" : { "job" : [ "tmgmt-job" ] } } ]
    }
  }');
  $items['rules_auto_submit_a_translation_job'] = entity_import('rules_config', '{ "rules_auto_submit_a_translation_job" : {
      "LABEL" : "Auto-Submit a translation job",
      "PLUGIN" : "reaction rule",
      "REQUIRES" : [ "rules", "tmgmt" ],
      "ON" : [ "tmgmt_job_update" ],
      "IF" : [ { "data_is" : { "data" : [ "tmgmt-job:state" ], "value" : "1" } } ],
      "DO" : [
        { "tmgmt_rules_job_request_translation" : { "job" : [ "tmgmt-job" ] } }
      ]
    }
  }');
  return $items;
}
