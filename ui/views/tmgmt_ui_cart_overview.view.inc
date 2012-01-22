<?php

/**
 * @file
 * Default view. Provide cart overview page.
 */
$view = new view;
$view->name = 'tmgmt_ui_cart_overview';
$view->description = '';
$view->tag = 'default';
$view->base_table = 'tmgmt_job_item';
$view->human_name = 'Translation managment cart overview';
$view->core = 7;
$view->api_version = '3.0';
$view->disabled = FALSE; /* Edit this to true to make a default view disabled initially */

/* Display: Master */
$handler = $view->new_display('default', 'Master', 'default');
$handler->display->display_options['title'] = 'Cart overview';
$handler->display->display_options['access']['type'] = 'none';
$handler->display->display_options['cache']['type'] = 'none';
$handler->display->display_options['query']['type'] = 'views_query';
$handler->display->display_options['query']['options']['query_comment'] = FALSE;
$handler->display->display_options['exposed_form']['type'] = 'basic';
$handler->display->display_options['pager']['type'] = 'full';
$handler->display->display_options['style_plugin'] = 'table';
$handler->display->display_options['style_options']['grouping'] = array(
  0 => array(
    'field' => 'tjid',
    'rendered' => 1,
  ),
);
$handler->display->display_options['style_options']['columns'] = array(
  'source_language' => 'source_language',
  'target_language' => 'target_language',
  'label' => 'label',
  'tjid' => 'tjid',
  'item_type' => 'item_type',
  'source_plugin' => 'source_plugin',
);
$handler->display->display_options['style_options']['default'] = '-1';
$handler->display->display_options['style_options']['info'] = array(
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
  'label' => array(
    'sortable' => 0,
    'default_sort_order' => 'asc',
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
  'item_type' => array(
    'sortable' => 0,
    'default_sort_order' => 'asc',
    'align' => '',
    'separator' => '',
    'empty_column' => 0,
  ),
  'source_plugin' => array(
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
$handler->display->display_options['relationships']['tjid']['label'] = 'job';
$handler->display->display_options['relationships']['tjid']['required'] = 0;
/* Relationship: Translation Management Job: Author uid */
$handler->display->display_options['relationships']['author']['id'] = 'author';
$handler->display->display_options['relationships']['author']['table'] = 'tmgmt_job';
$handler->display->display_options['relationships']['author']['field'] = 'author';
$handler->display->display_options['relationships']['author']['relationship'] = 'tjid';
$handler->display->display_options['relationships']['author']['required'] = 0;
/* Field: Translation Management Job: Source_language */
$handler->display->display_options['fields']['source_language']['id'] = 'source_language';
$handler->display->display_options['fields']['source_language']['table'] = 'tmgmt_job';
$handler->display->display_options['fields']['source_language']['field'] = 'source_language';
$handler->display->display_options['fields']['source_language']['relationship'] = 'tjid';
$handler->display->display_options['fields']['source_language']['exclude'] = TRUE;
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
$handler->display->display_options['fields']['source_language']['element_label_colon'] = 1;
$handler->display->display_options['fields']['source_language']['element_default_classes'] = 1;
$handler->display->display_options['fields']['source_language']['hide_empty'] = 0;
$handler->display->display_options['fields']['source_language']['empty_zero'] = 0;
$handler->display->display_options['fields']['source_language']['hide_alter_empty'] = 1;
$handler->display->display_options['fields']['source_language']['machine_name'] = 0;
/* Field: Translation Management Job: Target_language */
$handler->display->display_options['fields']['target_language']['id'] = 'target_language';
$handler->display->display_options['fields']['target_language']['table'] = 'tmgmt_job';
$handler->display->display_options['fields']['target_language']['field'] = 'target_language';
$handler->display->display_options['fields']['target_language']['relationship'] = 'tjid';
$handler->display->display_options['fields']['target_language']['label'] = '';
$handler->display->display_options['fields']['target_language']['exclude'] = TRUE;
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
$handler->display->display_options['fields']['target_language']['element_label_colon'] = FALSE;
$handler->display->display_options['fields']['target_language']['element_default_classes'] = 1;
$handler->display->display_options['fields']['target_language']['hide_empty'] = 0;
$handler->display->display_options['fields']['target_language']['empty_zero'] = 0;
$handler->display->display_options['fields']['target_language']['hide_alter_empty'] = 1;
$handler->display->display_options['fields']['target_language']['machine_name'] = 0;
/* Field: checkout link */
$handler->display->display_options['fields']['tjid_1']['id'] = 'tjid_1';
$handler->display->display_options['fields']['tjid_1']['table'] = 'tmgmt_job';
$handler->display->display_options['fields']['tjid_1']['field'] = 'tjid';
$handler->display->display_options['fields']['tjid_1']['relationship'] = 'tjid';
$handler->display->display_options['fields']['tjid_1']['ui_name'] = 'checkout link';
$handler->display->display_options['fields']['tjid_1']['label'] = '';
$handler->display->display_options['fields']['tjid_1']['exclude'] = TRUE;
$handler->display->display_options['fields']['tjid_1']['alter']['alter_text'] = 1;
$handler->display->display_options['fields']['tjid_1']['alter']['text'] = 'checkout';
$handler->display->display_options['fields']['tjid_1']['alter']['make_link'] = 1;
$handler->display->display_options['fields']['tjid_1']['alter']['path'] = 'admin/config/regional/tmgmt/checkout/[tjid_1]';
$handler->display->display_options['fields']['tjid_1']['alter']['absolute'] = 0;
$handler->display->display_options['fields']['tjid_1']['alter']['external'] = 0;
$handler->display->display_options['fields']['tjid_1']['alter']['replace_spaces'] = 0;
$handler->display->display_options['fields']['tjid_1']['alter']['trim_whitespace'] = 0;
$handler->display->display_options['fields']['tjid_1']['alter']['nl2br'] = 0;
$handler->display->display_options['fields']['tjid_1']['alter']['word_boundary'] = 1;
$handler->display->display_options['fields']['tjid_1']['alter']['ellipsis'] = 1;
$handler->display->display_options['fields']['tjid_1']['alter']['strip_tags'] = 0;
$handler->display->display_options['fields']['tjid_1']['alter']['trim'] = 0;
$handler->display->display_options['fields']['tjid_1']['alter']['html'] = 0;
$handler->display->display_options['fields']['tjid_1']['element_label_colon'] = FALSE;
$handler->display->display_options['fields']['tjid_1']['element_default_classes'] = 1;
$handler->display->display_options['fields']['tjid_1']['hide_empty'] = 0;
$handler->display->display_options['fields']['tjid_1']['empty_zero'] = 0;
$handler->display->display_options['fields']['tjid_1']['hide_alter_empty'] = 1;
$handler->display->display_options['fields']['tjid_1']['format_plural'] = 0;
/* Field: header */
$handler->display->display_options['fields']['tjid']['id'] = 'tjid';
$handler->display->display_options['fields']['tjid']['table'] = 'tmgmt_job';
$handler->display->display_options['fields']['tjid']['field'] = 'tjid';
$handler->display->display_options['fields']['tjid']['relationship'] = 'tjid';
$handler->display->display_options['fields']['tjid']['ui_name'] = 'header';
$handler->display->display_options['fields']['tjid']['label'] = '';
$handler->display->display_options['fields']['tjid']['exclude'] = TRUE;
$handler->display->display_options['fields']['tjid']['alter']['alter_text'] = 1;
$handler->display->display_options['fields']['tjid']['alter']['text'] = 'Cart: [tjid] [tjid_1] <br> source: [source_language] target: [target_language] ';
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
$handler->display->display_options['fields']['tjid']['element_label_colon'] = FALSE;
$handler->display->display_options['fields']['tjid']['element_default_classes'] = 1;
$handler->display->display_options['fields']['tjid']['hide_empty'] = 0;
$handler->display->display_options['fields']['tjid']['empty_zero'] = 0;
$handler->display->display_options['fields']['tjid']['hide_alter_empty'] = 1;
$handler->display->display_options['fields']['tjid']['format_plural'] = 0;
/* Field: Translation Management Job Item: Label */
$handler->display->display_options['fields']['label']['id'] = 'label';
$handler->display->display_options['fields']['label']['table'] = 'tmgmt_job_item';
$handler->display->display_options['fields']['label']['field'] = 'label';
$handler->display->display_options['fields']['label']['alter']['alter_text'] = 0;
$handler->display->display_options['fields']['label']['alter']['make_link'] = 0;
$handler->display->display_options['fields']['label']['alter']['absolute'] = 0;
$handler->display->display_options['fields']['label']['alter']['external'] = 0;
$handler->display->display_options['fields']['label']['alter']['replace_spaces'] = 0;
$handler->display->display_options['fields']['label']['alter']['trim_whitespace'] = 0;
$handler->display->display_options['fields']['label']['alter']['nl2br'] = 0;
$handler->display->display_options['fields']['label']['alter']['word_boundary'] = 1;
$handler->display->display_options['fields']['label']['alter']['ellipsis'] = 1;
$handler->display->display_options['fields']['label']['alter']['strip_tags'] = 0;
$handler->display->display_options['fields']['label']['alter']['trim'] = 0;
$handler->display->display_options['fields']['label']['alter']['html'] = 0;
$handler->display->display_options['fields']['label']['element_label_colon'] = 1;
$handler->display->display_options['fields']['label']['element_default_classes'] = 1;
$handler->display->display_options['fields']['label']['hide_empty'] = 0;
$handler->display->display_options['fields']['label']['empty_zero'] = 0;
$handler->display->display_options['fields']['label']['hide_alter_empty'] = 1;
/* Field: Translation Management Job Item: Item_type */
$handler->display->display_options['fields']['item_type']['id'] = 'item_type';
$handler->display->display_options['fields']['item_type']['table'] = 'tmgmt_job_item';
$handler->display->display_options['fields']['item_type']['field'] = 'item_type';
/* Filter criterion: User: Current */
$handler->display->display_options['filters']['uid_current']['id'] = 'uid_current';
$handler->display->display_options['filters']['uid_current']['table'] = 'users';
$handler->display->display_options['filters']['uid_current']['field'] = 'uid_current';
$handler->display->display_options['filters']['uid_current']['relationship'] = 'author';
$handler->display->display_options['filters']['uid_current']['value'] = '1';
/* Filter criterion: Translation Management Job: State */
$handler->display->display_options['filters']['state']['id'] = 'state';
$handler->display->display_options['filters']['state']['table'] = 'tmgmt_job';
$handler->display->display_options['filters']['state']['field'] = 'state';
$handler->display->display_options['filters']['state']['relationship'] = 'tjid';
$handler->display->display_options['filters']['state']['value'] = array(
  0 => '0',
);

/* Display: Block */
$handler = $view->new_display('block', 'Block', 'block_1');
$translatables['tmgmt_ui_cart_overview'] = array(
  t('Master'),
  t('Cart overview'),
  t('more'),
  t('Apply'),
  t('Reset'),
  t('Sort by'),
  t('Asc'),
  t('Desc'),
  t('Items per page'),
  t('- All -'),
  t('Offset'),
  t('job'),
  t('User'),
  t('Source_language'),
  t('checkout'),
  t('admin/config/regional/tmgmt/checkout/[tjid_1]'),
  t('.'),
  t(','),
  t('Cart: [tjid] [tjid_1] <br> source: [source_language] target: [target_language] '),
  t('Label'),
  t('Item_type'),
  t('Block'),
);
