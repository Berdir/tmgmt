<?php

/*
 * @file
 * Contains \Drupal\tmgmt\Plugin\views\field\TmgmtEntityLabel.
 */

/**
 * Field handler which shows a label of a entity.
 *
 * @todo Remove this once http://drupal.org/node/1435418 is through.
 *
 * @ingroup views_field_handlers
 */
class TmgmtEntityLabel extends views_handler_field_entity {

  function render($values) {
    if ($entity = $this->get_value($values)) {
      return $entity->label();
    }
  }

}
