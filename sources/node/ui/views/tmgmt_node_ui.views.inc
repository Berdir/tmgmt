<?php

/**
 * Implements hook_views_data().
 */
function tmgmt_node_ui_views_data() {
  $data = array();

  $data['node']['table']['join']['node'] = array(
    'left_field' => 'tnid',
    'field' => 'tnid',
    'extra fields' => array(
    )
  );

  $data['tmgmt_job_item']['table']['join'] = array(
    'node' => array(
      'field' => 'item_id',
      'left_field' => 'vid',
      'extra' => array(
        'field' => 'item_type',
        'value' => 'node'
      )
    ),
  );

  // Relationships between subscriptions and nodes.
  $data['tmgmt_job_item']['job_item_to_node'] = array(
    'title' => t('Content'),
    'help' => t('Content that is associated with this job item.'),
    'real field' => 'item_id',
    'relationship' => array(
      'title' => t('Content'),
      'label' => t('Content'),
      'base' => 'node',
      'base field' => 'vid',
      'relationship field' => 'item_id',
      'extra' => array(
        array(
          'table' => 'tmgmt_job_item',
          'field' => 'item_type',
          'operator' => '=',
          'value' => 'node',
        ),
        array(
          'table' => 'tmgmt_job_item',
          'field' => 'source_plugin',
          'operator' => '=',
          'value' => 'node',
        ),
      ),
    ),
  );

  return $data;
}
