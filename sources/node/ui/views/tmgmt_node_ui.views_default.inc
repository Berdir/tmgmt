<?php

/**
 * Implements hook_views_default_views().
 */
function tmgmt_node_ui_views_default_views() {
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
  $handler->display->display_options['style_options']['grouping'] = array(
    0 => array(
      'field' => 'source_language',
      'rendered' => 1,
    ),
  );
  $handler->display->display_options['style_options']['columns'] = array(
    'tjid' => 'tjid',
    'state' => 'state',
    'translator' => 'translator',
    'source_language' => 'source_language',
    'target_language' => 'target_language',
    'created' => 'created',
    'changed' => 'changed',
    'nothing' => 'nothing',
  );
  $handler->display->display_options['style_options']['default'] = '-1';
  $handler->display->display_options['style_options']['info'] = array(
    'tjid' => array(
      'sortable' => 0,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'state' => array(
      'sortable' => 0,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'translator' => array(
      'sortable' => 0,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'source_language' => array(
      'sortable' => 0,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'target_language' => array(
      'sortable' => 0,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'created' => array(
      'sortable' => 0,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'changed' => array(
      'sortable' => 0,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'nothing' => array(
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
  $handler->display->display_options['relationships']['tjid']['required'] = 1;
  /* Relationship: Translation Management Job Item: Content */
  $handler->display->display_options['relationships']['job_item_to_node']['id'] = 'job_item_to_node';
  $handler->display->display_options['relationships']['job_item_to_node']['table'] = 'tmgmt_job_item';
  $handler->display->display_options['relationships']['job_item_to_node']['field'] = 'job_item_to_node';
  $handler->display->display_options['relationships']['job_item_to_node']['required'] = 1;
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
  $handler->display->display_options['fields']['source_language']['label'] = 'Source language';
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
  /* Field: Translation Management Job: Created */
  $handler->display->display_options['fields']['created']['id'] = 'created';
  $handler->display->display_options['fields']['created']['table'] = 'tmgmt_job';
  $handler->display->display_options['fields']['created']['field'] = 'created';
  $handler->display->display_options['fields']['created']['relationship'] = 'tjid';
  $handler->display->display_options['fields']['created']['label'] = 'Job created';
  $handler->display->display_options['fields']['created']['alter']['alter_text'] = 0;
  $handler->display->display_options['fields']['created']['alter']['make_link'] = 0;
  $handler->display->display_options['fields']['created']['alter']['absolute'] = 0;
  $handler->display->display_options['fields']['created']['alter']['external'] = 0;
  $handler->display->display_options['fields']['created']['alter']['replace_spaces'] = 0;
  $handler->display->display_options['fields']['created']['alter']['trim_whitespace'] = 0;
  $handler->display->display_options['fields']['created']['alter']['nl2br'] = 0;
  $handler->display->display_options['fields']['created']['alter']['word_boundary'] = 1;
  $handler->display->display_options['fields']['created']['alter']['ellipsis'] = 1;
  $handler->display->display_options['fields']['created']['alter']['strip_tags'] = 0;
  $handler->display->display_options['fields']['created']['alter']['trim'] = 0;
  $handler->display->display_options['fields']['created']['alter']['html'] = 0;
  $handler->display->display_options['fields']['created']['element_label_colon'] = 0;
  $handler->display->display_options['fields']['created']['element_default_classes'] = 1;
  $handler->display->display_options['fields']['created']['hide_empty'] = 0;
  $handler->display->display_options['fields']['created']['empty_zero'] = 0;
  $handler->display->display_options['fields']['created']['hide_alter_empty'] = 1;
  $handler->display->display_options['fields']['created']['date_format'] = 'short';
  /* Field: Translation Management Job: Changed */
  $handler->display->display_options['fields']['changed']['id'] = 'changed';
  $handler->display->display_options['fields']['changed']['table'] = 'tmgmt_job';
  $handler->display->display_options['fields']['changed']['field'] = 'changed';
  $handler->display->display_options['fields']['changed']['relationship'] = 'tjid';
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
  $handler->display->display_options['arguments']['item_id']['default_argument_type'] = 'fixed';
  $handler->display->display_options['arguments']['item_id']['default_argument_skip_url'] = 0;
  $handler->display->display_options['arguments']['item_id']['summary']['number_of_records'] = '0';
  $handler->display->display_options['arguments']['item_id']['summary']['format'] = 'default_summary';
  $handler->display->display_options['arguments']['item_id']['summary_options']['items_per_page'] = '25';
  $handler->display->display_options['arguments']['item_id']['glossary'] = 0;
  $handler->display->display_options['arguments']['item_id']['limit'] = '0';
  $handler->display->display_options['arguments']['item_id']['transform_dash'] = 0;
  $handler->display->display_options['arguments']['item_id']['break_phrase'] = 0;
  /* Filter criterion: Translation Management Job Item: Item_type */
  $handler->display->display_options['filters']['item_type']['id'] = 'item_type';
  $handler->display->display_options['filters']['item_type']['table'] = 'tmgmt_job_item';
  $handler->display->display_options['filters']['item_type']['field'] = 'item_type';
  $handler->display->display_options['filters']['item_type']['value'] = 'node';
  /* Filter criterion: Translation Management Job Item: Source_plugin */
  $handler->display->display_options['filters']['source_plugin']['id'] = 'source_plugin';
  $handler->display->display_options['filters']['source_plugin']['table'] = 'tmgmt_job_item';
  $handler->display->display_options['filters']['source_plugin']['field'] = 'source_plugin';
  $handler->display->display_options['filters']['source_plugin']['value'] = array(
    'node' => 'node',
  );
  $translatables['job_items_current_node'] = array(
    t('Master'),
    t('more'),
    t('Apply'),
    t('Reset'),
    t('Sort by'),
    t('Asc'),
    t('Desc'),
    t('Translation Management Job'),
    t('Content'),
    t('Job id'),
    t('.'),
    t(','),
    t('Job state'),
    t('Translation service'),
    t('Source language'),
    t('Destination language'),
    t('Job created'),
    t('Changed'),
    t('Actions'),
    t('No actions avalible yet.'),
    t('All'),
  );

  $views[$view->name] = $view;

  $view = new view;
  $view->name = 'tmgmt_node_ui_source_overview';
  $view->description = '';
  $view->tag = 'default';
  $view->base_table = 'node';
  $view->human_name = 'tmgmt_node_ui_source_overview';
  $view->core = 7;
  $view->api_version = '3.0';
  $view->disabled = FALSE; /* Edit this to true to make a default view disabled initially */

  /* Display: Master */
  $handler = $view->new_display('default', 'Master', 'default');
  $handler->display->display_options['title'] = 'Content overview';
  $handler->display->display_options['group_by'] = TRUE;
  $handler->display->display_options['access']['type'] = 'perm';
  $handler->display->display_options['access']['perm'] = 'access tmgmt ui';
  $handler->display->display_options['cache']['type'] = 'none';
  $handler->display->display_options['query']['type'] = 'views_query';
  $handler->display->display_options['query']['options']['query_comment'] = FALSE;
  $handler->display->display_options['exposed_form']['type'] = 'basic';
  $handler->display->display_options['pager']['type'] = 'full';
  $handler->display->display_options['style_plugin'] = 'table';
  $handler->display->display_options['style_options']['columns'] = array(
    'title' => 'title',
  );
  $handler->display->display_options['style_options']['default'] = '-1';
  $handler->display->display_options['style_options']['info'] = array(
    'title' => array(
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
  /* Relationship: Content: Author */
  $handler->display->display_options['relationships']['uid']['id'] = 'uid';
  $handler->display->display_options['relationships']['uid']['table'] = 'node';
  $handler->display->display_options['relationships']['uid']['field'] = 'uid';
  $handler->display->display_options['relationships']['uid']['required'] = 0;
  /* Relationship: Content: Translation job item */
  $handler->display->display_options['relationships']['tmgmt_job_item']['id'] = 'tmgmt_job_item';
  $handler->display->display_options['relationships']['tmgmt_job_item']['table'] = 'node';
  $handler->display->display_options['relationships']['tmgmt_job_item']['field'] = 'tmgmt_job_item';
  $handler->display->display_options['relationships']['tmgmt_job_item']['required'] = 0;
  /* Relationship: Translation Management Job Item: Tjid */
  $handler->display->display_options['relationships']['tjid']['id'] = 'tjid';
  $handler->display->display_options['relationships']['tjid']['table'] = 'tmgmt_job_item';
  $handler->display->display_options['relationships']['tjid']['field'] = 'tjid';
  $handler->display->display_options['relationships']['tjid']['relationship'] = 'tmgmt_job_item';
  $handler->display->display_options['relationships']['tjid']['required'] = 1;
  /* Field: Content: Title */
  $handler->display->display_options['fields']['title']['id'] = 'title';
  $handler->display->display_options['fields']['title']['table'] = 'node';
  $handler->display->display_options['fields']['title']['field'] = 'title';
  $handler->display->display_options['fields']['title']['label'] = 'node title (in source language)';
  $handler->display->display_options['fields']['title']['alter']['alter_text'] = 0;
  $handler->display->display_options['fields']['title']['alter']['make_link'] = 0;
  $handler->display->display_options['fields']['title']['alter']['absolute'] = 0;
  $handler->display->display_options['fields']['title']['alter']['external'] = 0;
  $handler->display->display_options['fields']['title']['alter']['replace_spaces'] = 0;
  $handler->display->display_options['fields']['title']['alter']['trim_whitespace'] = 0;
  $handler->display->display_options['fields']['title']['alter']['nl2br'] = 0;
  $handler->display->display_options['fields']['title']['alter']['word_boundary'] = 0;
  $handler->display->display_options['fields']['title']['alter']['ellipsis'] = 0;
  $handler->display->display_options['fields']['title']['alter']['strip_tags'] = 0;
  $handler->display->display_options['fields']['title']['alter']['trim'] = 0;
  $handler->display->display_options['fields']['title']['alter']['html'] = 0;
  $handler->display->display_options['fields']['title']['element_label_colon'] = 1;
  $handler->display->display_options['fields']['title']['element_default_classes'] = 1;
  $handler->display->display_options['fields']['title']['hide_empty'] = 0;
  $handler->display->display_options['fields']['title']['empty_zero'] = 0;
  $handler->display->display_options['fields']['title']['hide_alter_empty'] = 1;
  $handler->display->display_options['fields']['title']['link_to_node'] = 1;
  /* Field: Content: Type */
  $handler->display->display_options['fields']['type']['id'] = 'type';
  $handler->display->display_options['fields']['type']['table'] = 'node';
  $handler->display->display_options['fields']['type']['field'] = 'type';
  $handler->display->display_options['fields']['type']['label'] = 'type';
  $handler->display->display_options['fields']['type']['alter']['alter_text'] = 0;
  $handler->display->display_options['fields']['type']['alter']['make_link'] = 0;
  $handler->display->display_options['fields']['type']['alter']['absolute'] = 0;
  $handler->display->display_options['fields']['type']['alter']['external'] = 0;
  $handler->display->display_options['fields']['type']['alter']['replace_spaces'] = 0;
  $handler->display->display_options['fields']['type']['alter']['trim_whitespace'] = 0;
  $handler->display->display_options['fields']['type']['alter']['nl2br'] = 0;
  $handler->display->display_options['fields']['type']['alter']['word_boundary'] = 1;
  $handler->display->display_options['fields']['type']['alter']['ellipsis'] = 1;
  $handler->display->display_options['fields']['type']['alter']['strip_tags'] = 0;
  $handler->display->display_options['fields']['type']['alter']['trim'] = 0;
  $handler->display->display_options['fields']['type']['alter']['html'] = 0;
  $handler->display->display_options['fields']['type']['element_label_colon'] = 1;
  $handler->display->display_options['fields']['type']['element_default_classes'] = 1;
  $handler->display->display_options['fields']['type']['hide_empty'] = 0;
  $handler->display->display_options['fields']['type']['empty_zero'] = 0;
  $handler->display->display_options['fields']['type']['hide_alter_empty'] = 1;
  $handler->display->display_options['fields']['type']['link_to_node'] = 0;
  $handler->display->display_options['fields']['type']['machine_name'] = 0;
  /* Field: User: Name */
  $handler->display->display_options['fields']['name']['id'] = 'name';
  $handler->display->display_options['fields']['name']['table'] = 'users';
  $handler->display->display_options['fields']['name']['field'] = 'name';
  $handler->display->display_options['fields']['name']['relationship'] = 'uid';
  $handler->display->display_options['fields']['name']['label'] = 'author';
  $handler->display->display_options['fields']['name']['alter']['alter_text'] = 0;
  $handler->display->display_options['fields']['name']['alter']['make_link'] = 0;
  $handler->display->display_options['fields']['name']['alter']['absolute'] = 0;
  $handler->display->display_options['fields']['name']['alter']['external'] = 0;
  $handler->display->display_options['fields']['name']['alter']['replace_spaces'] = 0;
  $handler->display->display_options['fields']['name']['alter']['trim_whitespace'] = 0;
  $handler->display->display_options['fields']['name']['alter']['nl2br'] = 0;
  $handler->display->display_options['fields']['name']['alter']['word_boundary'] = 1;
  $handler->display->display_options['fields']['name']['alter']['ellipsis'] = 1;
  $handler->display->display_options['fields']['name']['alter']['strip_tags'] = 0;
  $handler->display->display_options['fields']['name']['alter']['trim'] = 0;
  $handler->display->display_options['fields']['name']['alter']['html'] = 0;
  $handler->display->display_options['fields']['name']['element_label_colon'] = 1;
  $handler->display->display_options['fields']['name']['element_default_classes'] = 1;
  $handler->display->display_options['fields']['name']['hide_empty'] = 0;
  $handler->display->display_options['fields']['name']['empty_zero'] = 0;
  $handler->display->display_options['fields']['name']['hide_alter_empty'] = 1;
  $handler->display->display_options['fields']['name']['link_to_user'] = 1;
  $handler->display->display_options['fields']['name']['overwrite_anonymous'] = 0;
  $handler->display->display_options['fields']['name']['format_username'] = 1;
  /* Field: Content: Updated date */
  $handler->display->display_options['fields']['changed']['id'] = 'changed';
  $handler->display->display_options['fields']['changed']['table'] = 'node';
  $handler->display->display_options['fields']['changed']['field'] = 'changed';
  $handler->display->display_options['fields']['changed']['alter']['alter_text'] = 0;
  $handler->display->display_options['fields']['changed']['alter']['make_link'] = 0;
  $handler->display->display_options['fields']['changed']['alter']['absolute'] = 0;
  $handler->display->display_options['fields']['changed']['alter']['external'] = 0;
  $handler->display->display_options['fields']['changed']['alter']['replace_spaces'] = 0;
  $handler->display->display_options['fields']['changed']['alter']['trim_whitespace'] = 0;
  $handler->display->display_options['fields']['changed']['alter']['nl2br'] = 0;
  $handler->display->display_options['fields']['changed']['alter']['word_boundary'] = 1;
  $handler->display->display_options['fields']['changed']['alter']['ellipsis'] = 1;
  $handler->display->display_options['fields']['changed']['alter']['strip_tags'] = 0;
  $handler->display->display_options['fields']['changed']['alter']['trim'] = 0;
  $handler->display->display_options['fields']['changed']['alter']['html'] = 0;
  $handler->display->display_options['fields']['changed']['element_label_colon'] = 1;
  $handler->display->display_options['fields']['changed']['element_default_classes'] = 1;
  $handler->display->display_options['fields']['changed']['hide_empty'] = 0;
  $handler->display->display_options['fields']['changed']['empty_zero'] = 0;
  $handler->display->display_options['fields']['changed']['hide_alter_empty'] = 1;
  $handler->display->display_options['fields']['changed']['date_format'] = 'short';
  /* Field: MAX(Translation Management Job: Translation management job ID) */
  $handler->display->display_options['fields']['tjid']['id'] = 'tjid';
  $handler->display->display_options['fields']['tjid']['table'] = 'tmgmt_job';
  $handler->display->display_options['fields']['tjid']['field'] = 'tjid';
  $handler->display->display_options['fields']['tjid']['relationship'] = 'tjid';
  $handler->display->display_options['fields']['tjid']['group_type'] = 'max';
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
  /* Sort criterion: Content: Post date */
  $handler->display->display_options['sorts']['created']['id'] = 'created';
  $handler->display->display_options['sorts']['created']['table'] = 'node';
  $handler->display->display_options['sorts']['created']['field'] = 'created';
  $handler->display->display_options['sorts']['created']['order'] = 'DESC';
  /* Filter criterion: Content: Published */
  $handler->display->display_options['filters']['status']['id'] = 'status';
  $handler->display->display_options['filters']['status']['table'] = 'node';
  $handler->display->display_options['filters']['status']['field'] = 'status';
  $handler->display->display_options['filters']['status']['value'] = 1;
  $handler->display->display_options['filters']['status']['group'] = 1;
  $handler->display->display_options['filters']['status']['expose']['operator'] = FALSE;
  /* Filter criterion: Content: Translatable types */
  $handler->display->display_options['filters']['tmgmt_translatable_types']['id'] = 'tmgmt_translatable_types';
  $handler->display->display_options['filters']['tmgmt_translatable_types']['table'] = 'node';
  $handler->display->display_options['filters']['tmgmt_translatable_types']['field'] = 'tmgmt_translatable_types';
  $handler->display->display_options['filters']['tmgmt_translatable_types']['group'] = 1;
  /* Filter criterion: Translation Management Job: Source_language */
  $handler->display->display_options['filters']['source_language']['id'] = 'source_language';
  $handler->display->display_options['filters']['source_language']['table'] = 'tmgmt_job';
  $handler->display->display_options['filters']['source_language']['field'] = 'source_language';
  $handler->display->display_options['filters']['source_language']['relationship'] = 'tjid';
  $handler->display->display_options['filters']['source_language']['group'] = 1;
  $handler->display->display_options['filters']['source_language']['exposed'] = TRUE;
  $handler->display->display_options['filters']['source_language']['expose']['operator_id'] = 'source_language_op';
  $handler->display->display_options['filters']['source_language']['expose']['label'] = 'Source';
  $handler->display->display_options['filters']['source_language']['expose']['operator'] = 'source_language_op';
  $handler->display->display_options['filters']['source_language']['expose']['identifier'] = 'source_language';
  $handler->display->display_options['filters']['source_language']['expose']['multiple'] = 1;
  $handler->display->display_options['filters']['source_language']['expose']['reduce'] = 0;
  /* Filter criterion: Translation Management Job: Target_language */
  $handler->display->display_options['filters']['target_language']['id'] = 'target_language';
  $handler->display->display_options['filters']['target_language']['table'] = 'tmgmt_job';
  $handler->display->display_options['filters']['target_language']['field'] = 'target_language';
  $handler->display->display_options['filters']['target_language']['relationship'] = 'tjid';
  $handler->display->display_options['filters']['target_language']['group'] = 1;
  $handler->display->display_options['filters']['target_language']['exposed'] = TRUE;
  $handler->display->display_options['filters']['target_language']['expose']['operator_id'] = 'target_language_op';
  $handler->display->display_options['filters']['target_language']['expose']['label'] = 'Target';
  $handler->display->display_options['filters']['target_language']['expose']['operator'] = 'target_language_op';
  $handler->display->display_options['filters']['target_language']['expose']['identifier'] = 'target_language';
  $handler->display->display_options['filters']['target_language']['expose']['multiple'] = 1;
  $handler->display->display_options['filters']['target_language']['expose']['reduce'] = 0;

  /* Display: Page */
  $handler = $view->new_display('page', 'Page', 'page_1');
  $handler->display->display_options['path'] = 'admin/config/regional/tmgmt/node';
  $handler->display->display_options['menu']['type'] = 'tab';
  $handler->display->display_options['menu']['title'] = 'Content translation overview';
  $handler->display->display_options['menu']['weight'] = '0';
  $handler->display->display_options['menu']['context'] = 0;
  $translatables['tmgmt_node_ui_source_overview'] = array(
    t('Master'),
    t('Content overview'),
    t('more'),
    t('Apply'),
    t('Reset'),
    t('Sort by'),
    t('Asc'),
    t('Desc'),
    t('Items per page'),
    t('- All -'),
    t('Offset'),
    t('author'),
    t('Translation job item'),
    t('Translation Management Job'),
    t('node title (in source language)'),
    t('type'),
    t('Updated date'),
    t('Translation management job ID'),
    t('.'),
    t(','),
    t('Source'),
    t('Target'),
    t('Page'),
  );

  $views[$view->name] = $view;

  return $views;
}
