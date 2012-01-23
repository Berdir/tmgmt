<?php

/*
 * @file
 * Contains default rules.
 */

/**
 * Implements hook_default_rules_configuration().
 */
function tmgmt_ui_default_rules_configuration() {

  $data = '{ "rules_auto_accept_a_translation_job" : {
      "LABEL" : "Auto-Accept a translation job",
      "PLUGIN" : "reaction rule",
      "REQUIRES" : [ "rules", "tmgmt" ],
      "ON" : [ "tmgmt_job_update" ],
      "IF" : [ { "data_is" : { "data" : [ "tmgmt-job:state" ], "value" : "4" } } ],
      "DO" : [
        { "tmgmt_rules_job_accept_translation" : {
            "job" : [ "tmgmt-job" ],
            "message" : "Automatically accepted \u0022[tmgmt-job:label]\u0022 with rules."
          }
        }
      ]
    }
  }';
  $rule = rules_import($data);
  $configs[$rule->name] = $rule;

  $data = '{ "rules_tmgmt_job_accept_translation" : {
      "LABEL" : "Accept translation",
      "PLUGIN" : "rule",
      "REQUIRES" : [ "rules", "tmgmt" ],
      "USES VARIABLES" : { "job" : { "label" : "Job", "type" : "tmgmt_job" } },
      "IF" : [ { "data_is" : { "data" : [ "job:state" ], "value" : "4" } } ],
      "DO" : [ { "tmgmt_rules_job_accept_translation" : { "job" : [ "job" ] } } ]
    }
  }';
  $rule = rules_import($data);
  $configs[$rule->name] = $rule;

  $data = '{ "rules_tmgmt_job_cancel_translation" : {
      "LABEL" : "Cancel Translation",
      "PLUGIN" : "rule",
      "REQUIRES" : [ "tmgmt" ],
      "USES VARIABLES" : { "job" : { "label" : "Job", "type" : "tmgmt_job" } },
      "DO" : [ { "tmgmt_rules_job_cancel_translation" : { "job" : [ "job" ] } } ]
    }
  }';
  $rule = rules_import($data);
  $configs[$rule->name] = $rule;

  $data = '{ "rules_tmgmt_source_node_add_to_job" : {
      "LABEL" : "Add to Translation Job",
      "PLUGIN" : "rule",
      "REQUIRES" : [ "tmgmt" ],
      "USES VARIABLES" : {
        "nodes" : { "label" : "Nodes", "type" : "list\u003Cnode\u003E" },
        "target_languages" : { "label" : "Target language", "type" : "list\u003Ctoken\u003E" }
      },
      "DO" : [
        { "LOOP" : {
            "USING" : { "list" : [ "target-languages" ] },
            "ITEM" : { "target_language" : "Target Language" },
            "DO" : [
              { "LOOP" : {
                  "USING" : { "list" : [ "nodes" ] },
                  "ITEM" : { "node" : "Node" },
                  "DO" : [
                    { "tmgmt_rules_get_job_for_language" : {
                        "USING" : {
                          "source_language" : [ "node:language" ],
                          "target_language" : [ "target_language" ],
                          "account" : [ "site:current-user" ]
                        },
                        "PROVIDE" : { "job" : { "job" : "Job" } }
                      }
                    },
                    { "tmgmt_rules_job_add_item" : {
                        "job" : [ "job" ],
                        "source_plugin" : "node",
                        "item_type" : "node",
                        "item_id" : [ "node:nid" ]
                      }
                    }
                  ]
                }
              }
            ]
          }
        }
      ]
    }
  }';

  $rule = rules_import($data);
  $configs[$rule->name] = $rule;

  return $configs;
}
