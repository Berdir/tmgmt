<?php

/**
 * Field handler which shows a label of a job item.
 *
 * @ingroup views_field_handlers
 */
class tmgmt_handler_field_tmgmt_entity_label extends views_handler_field_entity {

  function render($values) {
    if ($entity = $this->get_value($values)) {
      return $entity->label();
    }
  }

}
