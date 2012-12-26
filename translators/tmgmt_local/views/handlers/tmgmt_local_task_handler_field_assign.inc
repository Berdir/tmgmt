<?php

/**
 * Field handler which shows the link for assign translation task to selected
 * user.
 *
 * @ingroup views_field_handlers
 */
class tmgmt_local_task_handler_field_assign extends views_handler_field_entity {

  function render($values) {
    /**
     * @var TMGMTLocalTask $task
     */
    $task = $this->get_value($values);

    if (tmgmt_local_translation_access($task)) {
      $element = array(
        '#theme' => 'link',
        '#path' => 'manage-translate/assign-tasks/' . $task->tltid,
        '#options' => array(
          'query' => array('destination' => current_path()),
          'attributes' => array(),
          'title' => t('assign'),
          'html' => FALSE,
        ),
        '#text' => t('assign'),
      );
      return drupal_render($element);
    }
    return '';
  }

}
