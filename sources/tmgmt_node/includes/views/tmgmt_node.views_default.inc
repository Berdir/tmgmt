<?php

/**
 * Implements hook_views_default_views().
 */
function tmgmt_node_views_default_views() {
  $views = array();

  $view = new view;
  $view->name = 'job_items_current_node';
  $view->description = '';
  $view->tag = 'default';
  $view->base_table = 'tmgmt_job_item';
  $view->human_name = 'Job items current node';
  $view->core = 7;
  $view->api_version = '3.0';
  $view->disabled = FALSE; /* Edit this to true to make a default view disabled initially */

  /* Display: Master */
  $handler = $view->new_display('default', 'Master', 'default');
  $handler->display->display_options['access']['type'] = 'none';
  $handler->display->display_options['cache']['type'] = 'none';
  $handler->display->display_options['query']['type'] = 'views_query';
  $handler->display->display_options['query']['options']['query_comment'] = FALSE;
  $handler->display->display_options['exposed_form']['type'] = 'basic';
  $handler->display->display_options['pager']['type'] = 'none';
  $handler->display->display_options['pager']['options']['offset'] = '0';
  $handler->display->display_options['style_plugin'] = 'table';
  $handler->display->display_options['style_options']['columns'] = array(
    'tjiid' => 'tjiid',
    'item_id' => 'item_id',
    'item_type' => 'item_type',
    'plugin' => 'plugin',
    'rendered_entity' => 'rendered_entity',
    'tjid' => 'tjid',
    'tjiid_1' => 'tjiid_1',
  );
  $handler->display->display_options['style_options']['default'] = '-1';
  $handler->display->display_options['style_options']['info'] = array(
    'tjiid' => array(
      'sortable' => 0,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'item_id' => array(
      'sortable' => 0,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'item_type' => array(
      'sortable' => 0,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'plugin' => array(
      'sortable' => 0,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'rendered_entity' => array(
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'tjid' => array(
      'sortable' => 0,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'tjiid_1' => array(
      'sortable' => 0,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
  );
  $handler->display->display_options['style_options']['override'] = 1;
  $handler->display->display_options['style_options']['sticky'] = 0;
  $handler->display->display_options['style_options']['empty_table'] = 0;
  /* Relationship: Translation Management Job Item: Tjid */
  $handler->display->display_options['relationships']['tjid']['id'] = 'tjid';
  $handler->display->display_options['relationships']['tjid']['table'] = 'tmgmt_job_item';
  $handler->display->display_options['relationships']['tjid']['field'] = 'tjid';
  $handler->display->display_options['relationships']['tjid']['required'] = 0;
  /* Field: Translation Management Job Item: Rendered Translation Management Job Item */
  $handler->display->display_options['fields']['rendered_entity']['id'] = 'rendered_entity';
  $handler->display->display_options['fields']['rendered_entity']['table'] = 'views_entity_tmgmt_job_item';
  $handler->display->display_options['fields']['rendered_entity']['field'] = 'rendered_entity';
  $handler->display->display_options['fields']['rendered_entity']['alter']['alter_text'] = 0;
  $handler->display->display_options['fields']['rendered_entity']['alter']['make_link'] = 0;
  $handler->display->display_options['fields']['rendered_entity']['alter']['absolute'] = 0;
  $handler->display->display_options['fields']['rendered_entity']['alter']['external'] = 0;
  $handler->display->display_options['fields']['rendered_entity']['alter']['replace_spaces'] = 0;
  $handler->display->display_options['fields']['rendered_entity']['alter']['trim_whitespace'] = 0;
  $handler->display->display_options['fields']['rendered_entity']['alter']['nl2br'] = 0;
  $handler->display->display_options['fields']['rendered_entity']['alter']['word_boundary'] = 1;
  $handler->display->display_options['fields']['rendered_entity']['alter']['ellipsis'] = 1;
  $handler->display->display_options['fields']['rendered_entity']['alter']['strip_tags'] = 0;
  $handler->display->display_options['fields']['rendered_entity']['alter']['trim'] = 0;
  $handler->display->display_options['fields']['rendered_entity']['alter']['html'] = 0;
  $handler->display->display_options['fields']['rendered_entity']['element_label_colon'] = 1;
  $handler->display->display_options['fields']['rendered_entity']['element_default_classes'] = 1;
  $handler->display->display_options['fields']['rendered_entity']['hide_empty'] = 0;
  $handler->display->display_options['fields']['rendered_entity']['empty_zero'] = 0;
  $handler->display->display_options['fields']['rendered_entity']['hide_alter_empty'] = 1;
  $handler->display->display_options['fields']['rendered_entity']['link_to_entity'] = 1;
  /* Field: Translation Management Job Item: Translation management job item ID */
  $handler->display->display_options['fields']['tjiid_1']['id'] = 'tjiid_1';
  $handler->display->display_options['fields']['tjiid_1']['table'] = 'tmgmt_job_item';
  $handler->display->display_options['fields']['tjiid_1']['field'] = 'tjiid';
  $handler->display->display_options['fields']['tjiid_1']['alter']['alter_text'] = 0;
  $handler->display->display_options['fields']['tjiid_1']['alter']['make_link'] = 0;
  $handler->display->display_options['fields']['tjiid_1']['alter']['absolute'] = 0;
  $handler->display->display_options['fields']['tjiid_1']['alter']['external'] = 0;
  $handler->display->display_options['fields']['tjiid_1']['alter']['replace_spaces'] = 0;
  $handler->display->display_options['fields']['tjiid_1']['alter']['trim_whitespace'] = 0;
  $handler->display->display_options['fields']['tjiid_1']['alter']['nl2br'] = 0;
  $handler->display->display_options['fields']['tjiid_1']['alter']['word_boundary'] = 1;
  $handler->display->display_options['fields']['tjiid_1']['alter']['ellipsis'] = 1;
  $handler->display->display_options['fields']['tjiid_1']['alter']['strip_tags'] = 0;
  $handler->display->display_options['fields']['tjiid_1']['alter']['trim'] = 0;
  $handler->display->display_options['fields']['tjiid_1']['alter']['html'] = 0;
  $handler->display->display_options['fields']['tjiid_1']['element_label_colon'] = 1;
  $handler->display->display_options['fields']['tjiid_1']['element_default_classes'] = 1;
  $handler->display->display_options['fields']['tjiid_1']['hide_empty'] = 0;
  $handler->display->display_options['fields']['tjiid_1']['empty_zero'] = 0;
  $handler->display->display_options['fields']['tjiid_1']['hide_alter_empty'] = 1;
  $handler->display->display_options['fields']['tjiid_1']['format_plural'] = 0;
  /* Contextual filter: Translation Management Job Item: Item_id */
  $handler->display->display_options['arguments']['item_id']['id'] = 'item_id';
  $handler->display->display_options['arguments']['item_id']['table'] = 'tmgmt_job_item';
  $handler->display->display_options['arguments']['item_id']['field'] = 'item_id';
  $handler->display->display_options['arguments']['item_id']['default_action'] = 'not found';
  $handler->display->display_options['arguments']['item_id']['default_argument_type'] = 'fixed';
  $handler->display->display_options['arguments']['item_id']['default_argument_skip_url'] = 0;
  $handler->display->display_options['arguments']['item_id']['summary']['number_of_records'] = '0';
  $handler->display->display_options['arguments']['item_id']['summary']['format'] = 'default_summary';
  $handler->display->display_options['arguments']['item_id']['summary_options']['items_per_page'] = '25';
  $handler->display->display_options['arguments']['item_id']['specify_validation'] = 1;
  $handler->display->display_options['arguments']['item_id']['validate']['type'] = 'numeric';
  $handler->display->display_options['arguments']['item_id']['break_phrase'] = 0;
  $handler->display->display_options['arguments']['item_id']['not'] = 0;
  $translatables['job_items_current_node'] = array(
    t('Master'),
    t('more'),
    t('Apply'),
    t('Reset'),
    t('Sort by'),
    t('Asc'),
    t('Desc'),
    t('Translation Management Job'),
    t('Rendered Translation Management Job Item'),
    t('Translation management job item ID'),
    t('.'),
    t(','),
    t('State'),
    t('All'),
  );

  $views[$view->name] = $view;

  return $views;
}