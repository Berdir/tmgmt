<?php

/**
 * Field handler which shows the link for translating translation task items.
 *
 * @ingroup views_field_handlers
 */
class tmgmt_local_task_handler_field_translate extends views_handler_field_entity {

  function render($values) {
    /**
     * @var TMGMTLocalTask $task
     */
    $task = $this->get_value($values);

    if (tmgmt_local_translation_access($task)) {
      $element = array(
        '#theme' => 'link',
        '#path' => 'translate/' . $task->tltid,
        '#options' => array(
          'query' => array('destination' => current_path()),
          'attributes' => array(),
          'title' => t('translate'),
          'html' => FALSE,
        ),
        '#text' => t('translate'),
      );
      return drupal_render($element);
    }
    return '';
  }

}
