<?php

/**
 * Field handler which shows the link for translating translation task items.
 *
 * @ingroup views_field_handlers
 */
class tmgmt_local_task_handler_field_translate_task_item extends views_handler_field_entity {

  function render($values) {
    /**
     * @var TMGMTLocalTask $task
     */
    $task = $this->get_value($values);

    // @todo- is there a better way to get item id? This way we are
    // dependent on db values.
    $tjiid = $values->tjiid;
    $job_item = tmgmt_job_item_load($tjiid);
    if (tmgmt_local_translation_access($task) && $job_item->isActive()) {
      $element = array(
        '#theme' => 'link',
        '#path' => 'translate/' . $task->tltid . '/item/' . $tjiid,
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
