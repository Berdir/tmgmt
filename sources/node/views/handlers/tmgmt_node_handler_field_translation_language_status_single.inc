<?php

/**
 * @todo What is this?
 */
class tmgmt_node_handler_field_translation_language_status_single extends views_handler_field {

  /**
   * @var tmgmt_node_handler_field_translation_language_status
   */
  var $main_handler;

  /**
   * @var string
   */
  var $langcode;

  function init(&$view, &$options) {
    parent::init($view, $options);
    $this->additional_fields['nid'] = 'nid';
  }

  function render($values) {
    $nid = $values->nid;
    $langcode = $this->langcode;

    // Check if this is the source language.
    if ($langcode == $values->node_language) {
      $type = 'original';
      $label = t('Source');
    }
    // Check if there is a translation.
    elseif (!isset($this->view->field[$this->main_field]->language_items[$nid][$langcode])) {
      $type = 'missing';
      $label = t('Missing');
    }
    // Check if the translation is outdated.
    elseif (!$this->view->field[$this->main_field]->language_items[$nid][$langcode]) {
      $type = 'outofdate';
      $label = t('Outdated');
    }
    else {
      $type = 'current';
      $label = t('Current');
    }

    // Override the image in case of an active job.
    if (isset($this->view->field[$this->main_field]->active_job_items[$nid][$langcode])) {
      $item = $this->view->field[$this->main_field]->active_job_items[$nid][$langcode];
      $wrapper = entity_metadata_wrapper('tmgmt_job_item', $item);

      $label = t('Active job item: @state', array('@state' => $wrapper->state->label()));

      switch ($item->state) {
        case TMGMT_JOB_ITEM_STATE_ACTIVE:
          $type = 'active';
          break;

        case TMGMT_JOB_ITEM_STATE_REVIEW:
          $type = 'translated';
          break;
      }
    }

    $url = drupal_get_path('module', 'tmgmt_ui') . '/images/' . $type . '.png';

    // Theme the image.
    $variables = array(
      'path' => $url,
      'alt' => $label,
      'title' => $label,
      'attributes' => array(
        'class' => array('tmgmt-translation-status-image', 'tmgmt-translation-status-' . $type),
      ),
    );
    $status = theme('image', $variables);

    // If we have an active job item, wrap it in a link.
    if (!empty($item)) {
      $uri = $item->uri();
      // If the job item needs to be reviewed, point to the review form.
      // Otherwise just use the plain entity uri.
      $path = $item->isNeedsReview() ? $uri['path'] . '/review' : $uri['path'];
      $status = array(
        '#type' => 'link',
        '#href' => $path,
        '#title' => $status,
        '#options' => array('query' => array('destination' => current_path()), 'html' => TRUE),
        '#attributes' => array('title' => $label),
      );
    }
    return $status;
  }

  function query() {
    $this->add_additional_fields();
  }

}
