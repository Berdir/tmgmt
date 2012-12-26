<?php

/**
 * Field handler which shows the link for translating translation task items.
 *
 * @ingroup views_field_handlers
 */
class tmgmt_local_task_handler_field_translate_task_item extends views_handler_field_entity {

  function render($values) {
    /**
     * @var TMGMTJobItem $job_item
     */
    $task = $this->get_value($values);

    if (tmgmt_local_translation_access($task)) {
      $element = array(
        '#theme' => 'link',
        // @todo- is there a better way to get item id? This way we are
        // dependent on db values.
        '#path' => 'translate/' . $task->tltid . '/item/' . $values->tjiid,
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
