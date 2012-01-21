<?php

/**
 * Implements hook_views_data_alter().
 */
function tmgmt_node_ui_views_data_alter(&$data) {
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

  $data['node']['tmgmt_translatable_types'] = array(
    'title' => t('Translatable types'),
    'help' => t('Display only nodes from node types which are translatable'),
    'filter' => array(
      'handler' => 'tmgmt_node_ui_handler_filter_node_translatable_types',
      'real field' => 'type',
    ),
  );
  return $data;
}
