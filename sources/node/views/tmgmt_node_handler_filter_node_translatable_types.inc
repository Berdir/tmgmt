<?php

/**
 * Filter handler which filters by content type which are translatable.
 *
 * @ingroup views_filter_handlers
 */
class tmgmt_node_handler_filter_node_translatable_types extends views_handler_filter {
  function query() {
    $this->ensure_my_table();

    $types = $this->get_translatable_node_types();
    if (!empty($types)) {
      $this->query->add_where($this->options['group'], "$this->table_alias.$this->real_field", array_values($types), 'IN');
    }
  }


  /**
   * Returns all node types which are translatable.
   *
   * @return array
   */
  function get_translatable_node_types() {
    $types = array();
    foreach (node_type_get_names() as $type => $name) {
      // @TODO: Figure out whether this is the right constant or > 0 is better here.
      if (translation_supported_type($type) == TRANSLATION_ENABLED) {
        $types[] = $type;
      }
    }
    return $types;
  }
}
