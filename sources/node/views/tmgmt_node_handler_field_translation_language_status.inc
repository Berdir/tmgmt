<?php

/**
 * Field handler to display the status of all languages.
 *
 * @ingroup views_field_handlers
 */
class tmgmt_node_handler_field_translation_language_status extends views_handler_field {
  /**
   * @var views_plugin_query_default
   */
  var $query;

  function query() {
    $this->ensure_my_table();
    dsm(language_list('language'));
    foreach (language_list('language') as $lang => $info) {
      $join = new views_join();
      $this->query->add_table('node', $this->relationship, $join);
    }
  }

}
