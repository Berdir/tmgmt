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
  /* Field: Translation Management Job: Actions */
  $handler->display->display_options['fields']['actions']['id'] = 'actions';
  $handler->display->display_options['fields']['actions']['table'] = 'tmgmt_job';
  $handler->display->display_options['fields']['actions']['field'] = 'actions';
  $handler->display->display_options['fields']['actions']['relationship'] = 'tjid';
  $handler->display->display_options['fields']['actions']['alter']['alter_text'] = 0;
  $handler->display->display_options['fields']['actions']['alter']['make_link'] = 0;
  $handler->display->display_options['fields']['actions']['alter']['absolute'] = 0;
  $handler->display->display_options['fields']['actions']['alter']['external'] = 0;
  $handler->display->display_options['fields']['actions']['alter']['replace_spaces'] = 0;
  $handler->display->display_options['fields']['actions']['alter']['trim_whitespace'] = 0;
  $handler->display->display_options['fields']['actions']['alter']['nl2br'] = 0;
  $handler->display->display_options['fields']['actions']['alter']['word_boundary'] = 1;
  $handler->display->display_options['fields']['actions']['alter']['ellipsis'] = 1;
  $handler->display->display_options['fields']['actions']['alter']['strip_tags'] = 0;
  $handler->display->display_options['fields']['actions']['alter']['trim'] = 0;
  $handler->display->display_options['fields']['actions']['alter']['html'] = 0;
  $handler->display->display_options['fields']['actions']['element_label_colon'] = 1;
  $handler->display->display_options['fields']['actions']['element_default_classes'] = 1;
  $handler->display->display_options['fields']['actions']['hide_empty'] = 0;
  $handler->display->display_options['fields']['actions']['empty_zero'] = 0;
  $handler->display->display_options['fields']['actions']['hide_alter_empty'] = 1;
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
  $handler->display->display_options['relationships']['node_to_job_item']['id'] = 'node_to_job_item';
  $handler->display->display_options['relationships']['node_to_job_item']['table'] = 'node';
  $handler->display->display_options['relationships']['node_to_job_item']['field'] = 'node_to_job_item';
  $handler->display->display_options['relationships']['node_to_job_item']['required'] = 0;
  /* Relationship: Translation Management Job Item: Tjid */
  $handler->display->display_options['relationships']['tjid']['id'] = 'tjid';
  $handler->display->display_options['relationships']['tjid']['table'] = 'tmgmt_job_item';
  $handler->display->display_options['relationships']['tjid']['field'] = 'tjid';
  $handler->display->display_options['relationships']['tjid']['relationship'] = 'node_to_job_item';
  $handler->display->display_options['relationships']['tjid']['required'] = 0;
  /* Field: Content: Bulk operations */
  $handler->display->display_options['fields']['views_bulk_operations']['id'] = 'views_bulk_operations';
  $handler->display->display_options['fields']['views_bulk_operations']['table'] = 'node';
  $handler->display->display_options['fields']['views_bulk_operations']['field'] = 'views_bulk_operations';
  $handler->display->display_options['fields']['views_bulk_operations']['alter']['alter_text'] = 0;
  $handler->display->display_options['fields']['views_bulk_operations']['alter']['make_link'] = 0;
  $handler->display->display_options['fields']['views_bulk_operations']['alter']['absolute'] = 0;
  $handler->display->display_options['fields']['views_bulk_operations']['alter']['external'] = 0;
  $handler->display->display_options['fields']['views_bulk_operations']['alter']['replace_spaces'] = 0;
  $handler->display->display_options['fields']['views_bulk_operations']['alter']['trim_whitespace'] = 0;
  $handler->display->display_options['fields']['views_bulk_operations']['alter']['nl2br'] = 0;
  $handler->display->display_options['fields']['views_bulk_operations']['alter']['word_boundary'] = 1;
  $handler->display->display_options['fields']['views_bulk_operations']['alter']['ellipsis'] = 1;
  $handler->display->display_options['fields']['views_bulk_operations']['alter']['strip_tags'] = 0;
  $handler->display->display_options['fields']['views_bulk_operations']['alter']['trim'] = 0;
  $handler->display->display_options['fields']['views_bulk_operations']['alter']['html'] = 0;
  $handler->display->display_options['fields']['views_bulk_operations']['element_label_colon'] = 1;
  $handler->display->display_options['fields']['views_bulk_operations']['element_default_classes'] = 1;
  $handler->display->display_options['fields']['views_bulk_operations']['hide_empty'] = 0;
  $handler->display->display_options['fields']['views_bulk_operations']['empty_zero'] = 0;
  $handler->display->display_options['fields']['views_bulk_operations']['hide_alter_empty'] = 1;
  $handler->display->display_options['fields']['views_bulk_operations']['vbo']['entity_load_capacity'] = '10';
  $handler->display->display_options['fields']['views_bulk_operations']['vbo']['operations'] = array(
    'rules_component::rules_tmgmt_source_node_add_to_job' => array(
      'selected' => 1,
      'skip_confirmation' => 1,
      'override_label' => 0,
      'label' => '',
    ),
    'action::node_assign_owner_action' => array(
      'selected' => 0,
      'use_queue' => 0,
      'skip_confirmation' => 0,
      'override_label' => 0,
      'label' => '',
    ),
    'action::views_bulk_operations_delete_item' => array(
      'selected' => 0,
      'use_queue' => 0,
      'skip_confirmation' => 0,
      'override_label' => 0,
      'label' => '',
    ),
    'action::system_message_action' => array(
      'selected' => 0,
      'use_queue' => 0,
      'skip_confirmation' => 0,
      'override_label' => 0,
      'label' => '',
    ),
    'action::views_bulk_operations_script_action' => array(
      'selected' => 0,
      'use_queue' => 0,
      'skip_confirmation' => 0,
      'override_label' => 0,
      'label' => '',
    ),
    'action::node_make_sticky_action' => array(
      'selected' => 0,
      'use_queue' => 0,
      'skip_confirmation' => 0,
      'override_label' => 0,
      'label' => '',
    ),
    'action::node_make_unsticky_action' => array(
      'selected' => 0,
      'use_queue' => 0,
      'skip_confirmation' => 0,
      'override_label' => 0,
      'label' => '',
    ),
    'action::views_bulk_operations_argument_selector_action' => array(
      'selected' => 0,
      'skip_confirmation' => 0,
      'override_label' => 0,
      'label' => '',
      'settings' => array(
        'url' => '',
      ),
    ),
    'action::node_promote_action' => array(
      'selected' => 0,
      'use_queue' => 0,
      'skip_confirmation' => 0,
      'override_label' => 0,
      'label' => '',
    ),
    'action::node_publish_action' => array(
      'selected' => 0,
      'use_queue' => 0,
      'skip_confirmation' => 0,
      'override_label' => 0,
      'label' => '',
    ),
    'action::system_goto_action' => array(
      'selected' => 0,
      'use_queue' => 0,
      'skip_confirmation' => 0,
      'override_label' => 0,
      'label' => '',
    ),
    'action::node_unpromote_action' => array(
      'selected' => 0,
      'use_queue' => 0,
      'skip_confirmation' => 0,
      'override_label' => 0,
      'label' => '',
    ),
    'action::node_save_action' => array(
      'selected' => 0,
      'use_queue' => 0,
      'skip_confirmation' => 0,
      'override_label' => 0,
      'label' => '',
    ),
    'action::system_send_email_action' => array(
      'selected' => 0,
      'use_queue' => 0,
      'skip_confirmation' => 0,
      'override_label' => 0,
      'label' => '',
    ),
    'action::node_unpublish_action' => array(
      'selected' => 0,
      'use_queue' => 0,
      'skip_confirmation' => 0,
      'override_label' => 0,
      'label' => '',
    ),
    'action::node_unpublish_by_keyword_action' => array(
      'selected' => 0,
      'use_queue' => 0,
      'skip_confirmation' => 0,
      'override_label' => 0,
      'label' => '',
    ),
  );
  $handler->display->display_options['fields']['views_bulk_operations']['vbo']['enable_select_all_pages'] = 1;
  $handler->display->display_options['fields']['views_bulk_operations']['vbo']['display_type'] = '0';
  $handler->display->display_options['fields']['views_bulk_operations']['vbo']['display_result'] = 1;
  $handler->display->display_options['fields']['views_bulk_operations']['vbo']['merge_single_action'] = 0;
  $handler->display->display_options['fields']['views_bulk_operations']['vbo']['force_single'] = 0;
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
  /* Field: Content translation: Language */
  $handler->display->display_options['fields']['language']['id'] = 'language';
  $handler->display->display_options['fields']['language']['table'] = 'node';
  $handler->display->display_options['fields']['language']['field'] = 'language';
  $handler->display->display_options['fields']['language']['label'] = 'Source';
  $handler->display->display_options['fields']['language']['alter']['alter_text'] = 0;
  $handler->display->display_options['fields']['language']['alter']['make_link'] = 0;
  $handler->display->display_options['fields']['language']['alter']['absolute'] = 0;
  $handler->display->display_options['fields']['language']['alter']['external'] = 0;
  $handler->display->display_options['fields']['language']['alter']['replace_spaces'] = 0;
  $handler->display->display_options['fields']['language']['alter']['trim_whitespace'] = 0;
  $handler->display->display_options['fields']['language']['alter']['nl2br'] = 0;
  $handler->display->display_options['fields']['language']['alter']['word_boundary'] = 1;
  $handler->display->display_options['fields']['language']['alter']['ellipsis'] = 1;
  $handler->display->display_options['fields']['language']['alter']['strip_tags'] = 0;
  $handler->display->display_options['fields']['language']['alter']['trim'] = 0;
  $handler->display->display_options['fields']['language']['alter']['html'] = 0;
  $handler->display->display_options['fields']['language']['element_label_colon'] = 1;
  $handler->display->display_options['fields']['language']['element_default_classes'] = 1;
  $handler->display->display_options['fields']['language']['hide_empty'] = 0;
  $handler->display->display_options['fields']['language']['empty_zero'] = 0;
  $handler->display->display_options['fields']['language']['hide_alter_empty'] = 1;
  $handler->display->display_options['fields']['language']['link_to_node'] = 0;
  $handler->display->display_options['fields']['language']['native_language'] = 0;
  /* Field: Content: All translation languages */
  $handler->display->display_options['fields']['translation_language_status_1']['id'] = 'translation_language_status_1';
  $handler->display->display_options['fields']['translation_language_status_1']['table'] = 'node';
  $handler->display->display_options['fields']['translation_language_status_1']['field'] = 'translation_language_status';
  $handler->display->display_options['fields']['translation_language_status_1']['exclude'] = TRUE;
  $handler->display->display_options['fields']['translation_language_status_1']['alter']['alter_text'] = 0;
  $handler->display->display_options['fields']['translation_language_status_1']['alter']['make_link'] = 0;
  $handler->display->display_options['fields']['translation_language_status_1']['alter']['absolute'] = 0;
  $handler->display->display_options['fields']['translation_language_status_1']['alter']['external'] = 0;
  $handler->display->display_options['fields']['translation_language_status_1']['alter']['replace_spaces'] = 0;
  $handler->display->display_options['fields']['translation_language_status_1']['alter']['trim_whitespace'] = 0;
  $handler->display->display_options['fields']['translation_language_status_1']['alter']['nl2br'] = 0;
  $handler->display->display_options['fields']['translation_language_status_1']['alter']['word_boundary'] = 1;
  $handler->display->display_options['fields']['translation_language_status_1']['alter']['ellipsis'] = 1;
  $handler->display->display_options['fields']['translation_language_status_1']['alter']['strip_tags'] = 0;
  $handler->display->display_options['fields']['translation_language_status_1']['alter']['trim'] = 0;
  $handler->display->display_options['fields']['translation_language_status_1']['alter']['html'] = 0;
  $handler->display->display_options['fields']['translation_language_status_1']['element_label_colon'] = 1;
  $handler->display->display_options['fields']['translation_language_status_1']['element_default_classes'] = 1;
  $handler->display->display_options['fields']['translation_language_status_1']['hide_empty'] = 0;
  $handler->display->display_options['fields']['translation_language_status_1']['empty_zero'] = 0;
  $handler->display->display_options['fields']['translation_language_status_1']['hide_alter_empty'] = 1;
  $handler->display->display_options['fields']['translation_language_status_1']['type'] = 'unicode-yes-no';
  $handler->display->display_options['fields']['translation_language_status_1']['not'] = 0;
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
  $handler->display->display_options['fields']['tjid']['exclude'] = TRUE;
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
  $handler->display->display_options['filters']['tmgmt_translatable_types_1']['id'] = 'tmgmt_translatable_types_1';
  $handler->display->display_options['filters']['tmgmt_translatable_types_1']['table'] = 'node';
  $handler->display->display_options['filters']['tmgmt_translatable_types_1']['field'] = 'tmgmt_translatable_types';
  $handler->display->display_options['filters']['tmgmt_translatable_types_1']['group'] = 1;
  /* Filter criterion: Content translation: Language */
  $handler->display->display_options['filters']['language']['id'] = 'language';
  $handler->display->display_options['filters']['language']['table'] = 'node';
  $handler->display->display_options['filters']['language']['field'] = 'language';
  $handler->display->display_options['filters']['language']['group'] = 1;
  $handler->display->display_options['filters']['language']['exposed'] = TRUE;
  $handler->display->display_options['filters']['language']['expose']['operator_id'] = 'language_op';
  $handler->display->display_options['filters']['language']['expose']['label'] = 'Source';
  $handler->display->display_options['filters']['language']['expose']['operator'] = 'language_op';
  $handler->display->display_options['filters']['language']['expose']['identifier'] = 'language';
  $handler->display->display_options['filters']['language']['expose']['multiple'] = 1;
  $handler->display->display_options['filters']['language']['expose']['reduce'] = 0;
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
  /* Filter criterion: Content: Type */
  $handler->display->display_options['filters']['type']['id'] = 'type';
  $handler->display->display_options['filters']['type']['table'] = 'node';
  $handler->display->display_options['filters']['type']['field'] = 'type';
  $handler->display->display_options['filters']['type']['group'] = 1;
  $handler->display->display_options['filters']['type']['exposed'] = TRUE;
  $handler->display->display_options['filters']['type']['expose']['operator_id'] = 'type_op';
  $handler->display->display_options['filters']['type']['expose']['label'] = 'Type';
  $handler->display->display_options['filters']['type']['expose']['operator'] = 'type_op';
  $handler->display->display_options['filters']['type']['expose']['identifier'] = 'type';
  $handler->display->display_options['filters']['type']['expose']['multiple'] = 1;
  $handler->display->display_options['filters']['type']['expose']['reduce'] = 0;
  /* Filter criterion: Content translation: Source translation */
  $handler->display->display_options['filters']['source_translation']['id'] = 'source_translation';
  $handler->display->display_options['filters']['source_translation']['table'] = 'node';
  $handler->display->display_options['filters']['source_translation']['field'] = 'source_translation';
  $handler->display->display_options['filters']['source_translation']['operator'] = '1';
  $handler->display->display_options['filters']['source_translation']['group'] = 1;

  /* Display: Page */
  $handler = $view->new_display('page', 'Page', 'page_1');
  $handler->display->display_options['path'] = 'admin/tmgmt/node';
  $handler->display->display_options['menu']['type'] = 'tab';
  $handler->display->display_options['menu']['title'] = 'Content overview';
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
    t('Bulk operations'),
    t('node title (in source language)'),
    t('type'),
    t('Source'),
    t('All translation languages'),
    t('Updated date'),
    t('Translation management job ID'),
    t('.'),
    t(','),
    t('Target'),
    t('Type'),
    t('Page'),
  );

  $views[$view->name] = $view;

  return $views;
}
