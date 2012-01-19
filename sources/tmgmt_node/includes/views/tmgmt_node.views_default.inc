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
  /* Field: Translation Management Job: Translation management job ID */
  $handler->display->display_options['fields']['tjid']['id'] = 'tjid';
  $handler->display->display_options['fields']['tjid']['table'] = 'tmgmt_job';
  $handler->display->display_options['fields']['tjid']['field'] = 'tjid';
  $handler->display->display_options['fields']['tjid']['relationship'] = 'tjid';
  $handler->display->display_options['fields']['tjid']['label'] = 'Job id';
  $handler->display->display_options['fields']['tjid']['alter']['alter_text'] = 0;
  $handler->display->display_options['fields']['tjid']['alter']['make_link'] = 0;
  $handler->display->display_options['fields']['tjid']['alter']['absolute'] = 0;
  $handler->display->display_options['fields']['tjid']['alter']['external'] = 0;
  $handler->display->display_options['fields']['tjid']['alter']['replace_spaces'] = 0;
  $handler->display->display_options['fields']['tjid']['alter']['trim_whitespace'] = 0;
  $handler->display->display_options['fields']['tjid']['alter']['nl2br'] = 0;
  $handler->display->display_options['fields']['tjid']['alter']['word_boundary'] = 1;
  $handler->display->display_options['fields']['tjid']['alter']['ellipsis'] = 1;
  $handler->display->display_options['fields']['tjid']['alter']['strip_tags'] = 0;
  $handler->display->display_options['fields']['tjid']['alter']['trim'] = 0;
  $handler->display->display_options['fields']['tjid']['alter']['html'] = 0;
  $handler->display->display_options['fields']['tjid']['element_label_colon'] = 1;
  $handler->display->display_options['fields']['tjid']['element_default_classes'] = 1;
  $handler->display->display_options['fields']['tjid']['hide_empty'] = 0;
  $handler->display->display_options['fields']['tjid']['empty_zero'] = 0;
  $handler->display->display_options['fields']['tjid']['hide_alter_empty'] = 1;
  $handler->display->display_options['fields']['tjid']['format_plural'] = 0;
  /* Field: Translation Management Job: State */
  $handler->display->display_options['fields']['state']['id'] = 'state';
  $handler->display->display_options['fields']['state']['table'] = 'tmgmt_job';
  $handler->display->display_options['fields']['state']['field'] = 'state';
  $handler->display->display_options['fields']['state']['relationship'] = 'tjid';
  $handler->display->display_options['fields']['state']['label'] = 'Job state';
  $handler->display->display_options['fields']['state']['alter']['alter_text'] = 0;
  $handler->display->display_options['fields']['state']['alter']['make_link'] = 0;
  $handler->display->display_options['fields']['state']['alter']['absolute'] = 0;
  $handler->display->display_options['fields']['state']['alter']['external'] = 0;
  $handler->display->display_options['fields']['state']['alter']['replace_spaces'] = 0;
  $handler->display->display_options['fields']['state']['alter']['trim_whitespace'] = 0;
  $handler->display->display_options['fields']['state']['alter']['nl2br'] = 0;
  $handler->display->display_options['fields']['state']['alter']['word_boundary'] = 1;
  $handler->display->display_options['fields']['state']['alter']['ellipsis'] = 1;
  $handler->display->display_options['fields']['state']['alter']['strip_tags'] = 0;
  $handler->display->display_options['fields']['state']['alter']['trim'] = 0;
  $handler->display->display_options['fields']['state']['alter']['html'] = 0;
  $handler->display->display_options['fields']['state']['element_label_colon'] = 1;
  $handler->display->display_options['fields']['state']['element_default_classes'] = 1;
  $handler->display->display_options['fields']['state']['hide_empty'] = 0;
  $handler->display->display_options['fields']['state']['empty_zero'] = 0;
  $handler->display->display_options['fields']['state']['hide_alter_empty'] = 1;
  $handler->display->display_options['fields']['state']['format_plural'] = 0;
  /* Field: Translation Management Job: Translator */
  $handler->display->display_options['fields']['translator']['id'] = 'translator';
  $handler->display->display_options['fields']['translator']['table'] = 'tmgmt_job';
  $handler->display->display_options['fields']['translator']['field'] = 'translator';
  $handler->display->display_options['fields']['translator']['relationship'] = 'tjid';
  $handler->display->display_options['fields']['translator']['label'] = 'Translation service';
  $handler->display->display_options['fields']['translator']['alter']['alter_text'] = 0;
  $handler->display->display_options['fields']['translator']['alter']['make_link'] = 0;
  $handler->display->display_options['fields']['translator']['alter']['absolute'] = 0;
  $handler->display->display_options['fields']['translator']['alter']['external'] = 0;
  $handler->display->display_options['fields']['translator']['alter']['replace_spaces'] = 0;
  $handler->display->display_options['fields']['translator']['alter']['trim_whitespace'] = 0;
  $handler->display->display_options['fields']['translator']['alter']['nl2br'] = 0;
  $handler->display->display_options['fields']['translator']['alter']['word_boundary'] = 1;
  $handler->display->display_options['fields']['translator']['alter']['ellipsis'] = 1;
  $handler->display->display_options['fields']['translator']['alter']['strip_tags'] = 0;
  $handler->display->display_options['fields']['translator']['alter']['trim'] = 0;
  $handler->display->display_options['fields']['translator']['alter']['html'] = 0;
  $handler->display->display_options['fields']['translator']['element_label_colon'] = 0;
  $handler->display->display_options['fields']['translator']['element_default_classes'] = 1;
  $handler->display->display_options['fields']['translator']['hide_empty'] = 0;
  $handler->display->display_options['fields']['translator']['empty_zero'] = 0;
  $handler->display->display_options['fields']['translator']['hide_alter_empty'] = 1;
  /* Field: Translation Management Job: Source_language */
  $handler->display->display_options['fields']['source_language']['id'] = 'source_language';
  $handler->display->display_options['fields']['source_language']['table'] = 'tmgmt_job';
  $handler->display->display_options['fields']['source_language']['field'] = 'source_language';
  $handler->display->display_options['fields']['source_language']['relationship'] = 'tjid';
  $handler->display->display_options['fields']['source_language']['label'] = 'Sourcelanguage';
  $handler->display->display_options['fields']['source_language']['alter']['alter_text'] = 0;
  $handler->display->display_options['fields']['source_language']['alter']['make_link'] = 0;
  $handler->display->display_options['fields']['source_language']['alter']['absolute'] = 0;
  $handler->display->display_options['fields']['source_language']['alter']['external'] = 0;
  $handler->display->display_options['fields']['source_language']['alter']['replace_spaces'] = 0;
  $handler->display->display_options['fields']['source_language']['alter']['trim_whitespace'] = 0;
  $handler->display->display_options['fields']['source_language']['alter']['nl2br'] = 0;
  $handler->display->display_options['fields']['source_language']['alter']['word_boundary'] = 1;
  $handler->display->display_options['fields']['source_language']['alter']['ellipsis'] = 1;
  $handler->display->display_options['fields']['source_language']['alter']['strip_tags'] = 0;
  $handler->display->display_options['fields']['source_language']['alter']['trim'] = 0;
  $handler->display->display_options['fields']['source_language']['alter']['html'] = 0;
  $handler->display->display_options['fields']['source_language']['element_label_colon'] = 0;
  $handler->display->display_options['fields']['source_language']['element_default_classes'] = 1;
  $handler->display->display_options['fields']['source_language']['hide_empty'] = 0;
  $handler->display->display_options['fields']['source_language']['empty_zero'] = 0;
  $handler->display->display_options['fields']['source_language']['hide_alter_empty'] = 1;
  /* Field: Translation Management Job: Target_language */
  $handler->display->display_options['fields']['target_language']['id'] = 'target_language';
  $handler->display->display_options['fields']['target_language']['table'] = 'tmgmt_job';
  $handler->display->display_options['fields']['target_language']['field'] = 'target_language';
  $handler->display->display_options['fields']['target_language']['relationship'] = 'tjid';
  $handler->display->display_options['fields']['target_language']['label'] = 'Destination language';
  $handler->display->display_options['fields']['target_language']['alter']['alter_text'] = 0;
  $handler->display->display_options['fields']['target_language']['alter']['make_link'] = 0;
  $handler->display->display_options['fields']['target_language']['alter']['absolute'] = 0;
  $handler->display->display_options['fields']['target_language']['alter']['external'] = 0;
  $handler->display->display_options['fields']['target_language']['alter']['replace_spaces'] = 0;
  $handler->display->display_options['fields']['target_language']['alter']['trim_whitespace'] = 0;
  $handler->display->display_options['fields']['target_language']['alter']['nl2br'] = 0;
  $handler->display->display_options['fields']['target_language']['alter']['word_boundary'] = 1;
  $handler->display->display_options['fields']['target_language']['alter']['ellipsis'] = 1;
  $handler->display->display_options['fields']['target_language']['alter']['strip_tags'] = 0;
  $handler->display->display_options['fields']['target_language']['alter']['trim'] = 0;
  $handler->display->display_options['fields']['target_language']['alter']['html'] = 0;
  $handler->display->display_options['fields']['target_language']['element_label_colon'] = 0;
  $handler->display->display_options['fields']['target_language']['element_default_classes'] = 1;
  $handler->display->display_options['fields']['target_language']['hide_empty'] = 0;
  $handler->display->display_options['fields']['target_language']['empty_zero'] = 0;
  $handler->display->display_options['fields']['target_language']['hide_alter_empty'] = 1;
  /* Field: Global: Custom text */
  $handler->display->display_options['fields']['nothing']['id'] = 'nothing';
  $handler->display->display_options['fields']['nothing']['table'] = 'views';
  $handler->display->display_options['fields']['nothing']['field'] = 'nothing';
  $handler->display->display_options['fields']['nothing']['label'] = 'Actions';
  $handler->display->display_options['fields']['nothing']['alter']['text'] = 'No actions avalible yet.';
  $handler->display->display_options['fields']['nothing']['alter']['make_link'] = 0;
  $handler->display->display_options['fields']['nothing']['alter']['absolute'] = 0;
  $handler->display->display_options['fields']['nothing']['alter']['external'] = 0;
  $handler->display->display_options['fields']['nothing']['alter']['replace_spaces'] = 0;
  $handler->display->display_options['fields']['nothing']['alter']['trim_whitespace'] = 0;
  $handler->display->display_options['fields']['nothing']['alter']['nl2br'] = 0;
  $handler->display->display_options['fields']['nothing']['alter']['word_boundary'] = 1;
  $handler->display->display_options['fields']['nothing']['alter']['ellipsis'] = 1;
  $handler->display->display_options['fields']['nothing']['alter']['strip_tags'] = 0;
  $handler->display->display_options['fields']['nothing']['alter']['trim'] = 0;
  $handler->display->display_options['fields']['nothing']['alter']['html'] = 0;
  $handler->display->display_options['fields']['nothing']['element_label_colon'] = 0;
  $handler->display->display_options['fields']['nothing']['element_default_classes'] = 1;
  $handler->display->display_options['fields']['nothing']['hide_empty'] = 0;
  $handler->display->display_options['fields']['nothing']['empty_zero'] = 0;
  $handler->display->display_options['fields']['nothing']['hide_alter_empty'] = 0;
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
    t('Job id'),
    t('.'),
    t(','),
    t('Job state'),
    t('Translation service'),
    t('Sourcelanguage'),
    t('Destination language'),
    t('Actions'),
    t('No actions avalible yet.'),
    t('All'),
  );

  $views[$view->name] = $view;

  return $views;
}