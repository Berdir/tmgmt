<?php

/**
 * Field handler which shows the operations for a task.
 *
 * @todo Remove this once http://drupal.org/node/1435662 is through.
 *
 * @ingroup views_field_handlers
 */
class tmgmt_local_task_handler_field_operations extends views_handler_field_entity {

  function render($values) {
    /**
     * @var TMGMTLocalTask $task
     */
    $task = $this->get_value($values);

    $element = array();
    $element['#theme'] = 'links';
    $element['#attributes'] = array('class' => array('inline'));
    $uri = $task->uri();
    if (entity_access('edit', 'tmgmt_local_task', $task)) {
      $element['#links']['edit'] = array(
        'href' => $uri['path'],
        'query' => array('destination' => current_path()),
        'title' => t('edit'),
      );
      $element['#links']['delete'] = array(
        'href' => $uri['path'] . '/delete',
        'query' => array('destination' => current_path()),
        'title' => t('delete'),
      );
    }
    return drupal_render($element);
  }

}
