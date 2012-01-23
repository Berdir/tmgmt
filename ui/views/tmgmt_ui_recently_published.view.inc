<?php

$view = new view;
$view->name = 'tmgmt_ui_recently_published';
$view->description = '';
$view->tag = 'default';
$view->base_table = 'tmgmt_job';
$view->human_name = 'Translation Management Recently Published Translations';
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
$handler->display->display_options['pager']['type'] = 'some';
$handler->display->display_options['pager']['options']['items_per_page'] = '10';
$handler->display->display_options['pager']['options']['offset'] = '0';
$handler->display->display_options['style_plugin'] = 'table';
$handler->display->display_options['style_options']['columns'] = array(
  'tjid' => 'tjid',
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
);
$handler->display->display_options['style_options']['override'] = 1;
$handler->display->display_options['style_options']['sticky'] = 0;
$handler->display->display_options['style_options']['empty_table'] = 0;
/* Field: Translation Management Job: Translation management job ID */
$handler->display->display_options['fields']['tjid']['id'] = 'tjid';
$handler->display->display_options['fields']['tjid']['table'] = 'tmgmt_job';
$handler->display->display_options['fields']['tjid']['field'] = 'tjid';
$handler->display->display_options['fields']['tjid']['label'] = 'job id';
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
/* Field: Translation Management Job: Label */
$handler->display->display_options['fields']['label']['id'] = 'label';
$handler->display->display_options['fields']['label']['table'] = 'tmgmt_job';
$handler->display->display_options['fields']['label']['field'] = 'label';
$handler->display->display_options['fields']['label']['label'] = 'job title';
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
/* Field: Translation Management Job: Source_language */
$handler->display->display_options['fields']['source_language']['id'] = 'source_language';
$handler->display->display_options['fields']['source_language']['table'] = 'tmgmt_job';
$handler->display->display_options['fields']['source_language']['field'] = 'source_language';
$handler->display->display_options['fields']['source_language']['label'] = 'source';
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
$handler->display->display_options['fields']['target_language']['label'] = 'target';
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
$handler->display->display_options['fields']['target_language']['element_label_colon'] = 1;
$handler->display->display_options['fields']['target_language']['element_default_classes'] = 1;
$handler->display->display_options['fields']['target_language']['hide_empty'] = 0;
$handler->display->display_options['fields']['target_language']['empty_zero'] = 0;
$handler->display->display_options['fields']['target_language']['hide_alter_empty'] = 1;
$handler->display->display_options['fields']['target_language']['machine_name'] = 0;
/* Sort criterion: Translation Management Job: Changed */
$handler->display->display_options['sorts']['changed']['id'] = 'changed';
$handler->display->display_options['sorts']['changed']['table'] = 'tmgmt_job';
$handler->display->display_options['sorts']['changed']['field'] = 'changed';
$handler->display->display_options['sorts']['changed']['order'] = 'DESC';
/* Filter criterion: Translation Management Job: State */
$handler->display->display_options['filters']['state']['id'] = 'state';
$handler->display->display_options['filters']['state']['table'] = 'tmgmt_job';
$handler->display->display_options['filters']['state']['field'] = 'state';
$handler->display->display_options['filters']['state']['value'] = array(
  5 => '5',
);
$translatables['tmgmt_ui_recently_published'] = array(
  t('Master'),
  t('more'),
  t('Apply'),
  t('Reset'),
  t('Sort by'),
  t('Asc'),
  t('Desc'),
  t('job id'),
  t('.'),
  t(','),
  t('job title'),
  t('source'),
  t('target'),
);
