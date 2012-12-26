<?php

/**
 * Field handler which shows the manager view link for translation task.
 *
 * @ingroup views_field_handlers
 */
class tmgmt_local_task_handler_field_view extends views_handler_field_entity {

  function render($values) {
    /**
     * @var TMGMTLocalTask $task
     */
    $task = $this->get_value($values);

    if (user_access('administer translation tasks')) {
      $element = array(
        '#theme' => 'link',
        '#path' => 'manage-translate/task/' . $task->tltid,
        '#options' => array(
          'query' => array('destination' => current_path()),
          'attributes' => array(),
          'title' => t('view'),
          'html' => FALSE,
        ),
        '#text' => t('view'),
      );
      return drupal_render($element);
    }
    return '';
  }

}
