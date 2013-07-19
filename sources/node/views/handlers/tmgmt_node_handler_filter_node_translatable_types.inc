<?php


class tmgmt_node_ui_handler_filter_node_translatable_types extends views_handler_filter_in_operator{

  function query() {

  $valid_types = array_keys(tmgmt_source_translatable_item_types('node'));

  $this->ensure_my_table();

  $this->query->add_where($this->options['group'], "$this->table_alias.$this->real_field",
    array_values($valid_types), "in");
    }
}
