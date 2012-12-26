<?php

/**
 * Field handler which shows the link for assign translation task to current
 * user.
 *
 * @ingroup views_field_handlers
 */
class tmgmt_local_task_handler_field_assign_to_me extends views_handler_field_entity {

  function render($values) {
    /**
     * @var TMGMTLocalTask $task
     */
    $task = $this->get_value($values);

    if (tmgmt_local_translation_access($task)) {
      $element = array(
        '#theme' => 'link',
        '#path' => 'translate/' . $task->tltid . '/assign-to-me',
        '#options' => array(
          'query' => array('destination' => current_path()),
          'attributes' => array(),
          'title' => t('assign to me'),
          'html' => FALSE,
        ),
        '#text' => t('assign'),
      );
      return drupal_render($element);
    }
    return '';
  }

}
