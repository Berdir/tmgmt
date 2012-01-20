<?php
/**
 * @file
 * tmgmt_job_management.rules_defaults.inc
 */

/**
 * Implements hook_default_rules_configuration().
 */
function tmgmt_job_management_default_rules_configuration() {
  $items = array();
  $items['rules_tmgmt_job_accept_translation'] = entity_import('rules_config', '{ "rules_tmgmt_job_accept_translation" : {
      "LABEL" : "Accept translation",
      "PLUGIN" : "rule",
      "REQUIRES" : [ "rules", "tmgmt" ],
      "USES VARIABLES" : { "job" : { "label" : "Job", "type" : "tmgmt_job" } },
      "IF" : [ { "data_is" : { "data" : [ "job:state" ], "value" : "4" } } ],
      "DO" : [ { "tmgmt_rules_job_accept_translation" : { "job" : [ "job" ] } } ]
    }
  }');
  $items['rules_tmgmt_job_cancel_translation'] = entity_import('rules_config', '{ "rules_tmgmt_job_cancel_translation" : {
      "LABEL" : "Cancel Translation",
      "PLUGIN" : "rule",
      "REQUIRES" : [ "tmgmt" ],
      "USES VARIABLES" : { "job" : { "label" : "Job", "type" : "tmgmt_job" } },
      "DO" : [ { "tmgmt_rules_job_cancel_translation" : { "job" : [ "job" ] } } ]
    }
  }');
  $items['rules_tmgmt_job_request_translation'] = entity_import('rules_config', '{ "rules_tmgmt_job_request_translation" : {
      "LABEL" : "Request translation",
      "PLUGIN" : "rule",
      "REQUIRES" : [ "rules", "tmgmt" ],
      "USES VARIABLES" : { "job" : { "label" : "Job", "type" : "tmgmt_job" } },
      "IF" : [ { "data_is" : { "data" : [ "job:state" ], "value" : "1" } } ],
      "DO" : [ { "tmgmt_rules_job_request_translation" : { "job" : [ "job" ] } } ]
    }
  }');
  return $items;
}
