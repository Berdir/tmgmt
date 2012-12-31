<?php

$view = new view();
$view->name = 'tmgmt_local_translate';
$view->description = 'Shows a list of translatable objects.';
$view->tag = 'default';
$view->base_table = 'tmgmt_job_item';
$view->human_name = 'Translate';
$view->core = 7;
$view->api_version = '3.0';
$view->disabled = FALSE; /* Edit this to true to make a default view disabled initially */

/* Display: Master */
$handler = $view->new_display('default', 'Master', 'default');
$handler->display->display_options['title'] = 'Translate Task';
$handler->display->display_options['use_more_always'] = FALSE;
$handler->display->display_options['access']['type'] = 'perm';
$handler->display->display_options['access']['perm'] = 'provide translation services';
$handler->display->display_options['cache']['type'] = 'none';
$handler->display->display_options['query']['type'] = 'views_query';
$handler->display->display_options['exposed_form']['type'] = 'basic';
$handler->display->display_options['pager']['type'] = 'full';
$handler->display->display_options['pager']['options']['items_per_page'] = '10';
$handler->display->display_options['style_plugin'] = 'table';
$handler->display->display_options['style_options']['columns'] = array(
  'item_id' => 'item_id',
  'label' => 'label',
  'plugin' => 'plugin',
  'progress' => 'progress',
  'changed' => 'changed',
  'created' => 'created',
);
$handler->display->display_options['style_options']['default'] = '-1';
$handler->display->display_options['style_options']['info'] = array(
  'label' => array(
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
  'progress' => array(
    'align' => '',
    'separator' => '',
    'empty_column' => 0,
  ),
);
/* No results behavior: Global: Text area */
$handler->display->display_options['empty']['area']['id'] = 'area';
$handler->display->display_options['empty']['area']['table'] = 'views';
$handler->display->display_options['empty']['area']['field'] = 'area';
$handler->display->display_options['empty']['area']['content'] = 'There are no translatable objects for you.';
$handler->display->display_options['empty']['area']['format'] = 'filtered_html';
/* Relationship: Translation Management Job Item: Tjid */
$handler->display->display_options['relationships']['tjid']['id'] = 'tjid';
$handler->display->display_options['relationships']['tjid']['table'] = 'tmgmt_job_item';
$handler->display->display_options['relationships']['tjid']['field'] = 'tjid';
$handler->display->display_options['relationships']['tjid']['required'] = TRUE;
/* Relationship: Translation Management Job: Translator */
$handler->display->display_options['relationships']['translator']['id'] = 'translator';
$handler->display->display_options['relationships']['translator']['table'] = 'tmgmt_job';
$handler->display->display_options['relationships']['translator']['field'] = 'translator';
$handler->display->display_options['relationships']['translator']['relationship'] = 'tjid';
$handler->display->display_options['relationships']['translator']['required'] = TRUE;
/* Field: Translation Management Job Item: Tjid */
$handler->display->display_options['fields']['tjid']['id'] = 'tjid';
$handler->display->display_options['fields']['tjid']['table'] = 'tmgmt_job_item';
$handler->display->display_options['fields']['tjid']['field'] = 'tjid';
$handler->display->display_options['fields']['tjid']['exclude'] = TRUE;
/* Field: Translation Management Job Item: Label */
$handler->display->display_options['fields']['label']['id'] = 'label';
$handler->display->display_options['fields']['label']['table'] = 'tmgmt_job_item';
$handler->display->display_options['fields']['label']['field'] = 'label';
$handler->display->display_options['fields']['label']['alter']['alter_text'] = TRUE;
$handler->display->display_options['fields']['label']['alter']['make_link'] = TRUE;
$handler->display->display_options['fields']['label']['alter']['path'] = 'translate/[tjid]';
/* Field: Translation Management Job Item: Plugin */
$handler->display->display_options['fields']['plugin']['id'] = 'plugin';
$handler->display->display_options['fields']['plugin']['table'] = 'tmgmt_job_item';
$handler->display->display_options['fields']['plugin']['field'] = 'plugin';
/* Field: Translation Management Job Item: Progress */
$handler->display->display_options['fields']['progress']['id'] = 'progress';
$handler->display->display_options['fields']['progress']['table'] = 'tmgmt_job_item';
$handler->display->display_options['fields']['progress']['field'] = 'progress';
/* Sort criterion: Translation Management Job: Created */
$handler->display->display_options['sorts']['created']['id'] = 'created';
$handler->display->display_options['sorts']['created']['table'] = 'tmgmt_job';
$handler->display->display_options['sorts']['created']['field'] = 'created';
$handler->display->display_options['sorts']['created']['relationship'] = 'tjid';
/* Filter criterion: Translation Management Translator: Plugin */
$handler->display->display_options['filters']['plugin']['id'] = 'plugin';
$handler->display->display_options['filters']['plugin']['table'] = 'tmgmt_translator';
$handler->display->display_options['filters']['plugin']['field'] = 'plugin';
$handler->display->display_options['filters']['plugin']['relationship'] = 'translator';
$handler->display->display_options['filters']['plugin']['value'] = array(
  'local' => 'local',
);

/* Display: Translate Task */
$handler = $view->new_display('page', 'Translate Task', 'translate_task');
$handler->display->display_options['defaults']['title'] = FALSE;
$handler->display->display_options['title'] = 'Translate Task Items';
$handler->display->display_options['display_description'] = 'Show list of translatable task items.';
$handler->display->display_options['defaults']['hide_admin_links'] = FALSE;
$handler->display->display_options['defaults']['relationships'] = FALSE;
/* Relationship: Translation Management Job Item: Tjid */
$handler->display->display_options['relationships']['tjid']['id'] = 'tjid';
$handler->display->display_options['relationships']['tjid']['table'] = 'tmgmt_job_item';
$handler->display->display_options['relationships']['tjid']['field'] = 'tjid';
$handler->display->display_options['relationships']['tjid']['required'] = TRUE;
/* Relationship: Translation Management Job: Translator */
$handler->display->display_options['relationships']['translator']['id'] = 'translator';
$handler->display->display_options['relationships']['translator']['table'] = 'tmgmt_job';
$handler->display->display_options['relationships']['translator']['field'] = 'translator';
$handler->display->display_options['relationships']['translator']['relationship'] = 'tjid';
$handler->display->display_options['relationships']['translator']['required'] = TRUE;
/* Relationship: Translation Management Job: Translation Management Local Task */
$handler->display->display_options['relationships']['tmgmt_local_task']['id'] = 'tmgmt_local_task';
$handler->display->display_options['relationships']['tmgmt_local_task']['table'] = 'tmgmt_job';
$handler->display->display_options['relationships']['tmgmt_local_task']['field'] = 'tmgmt_local_task';
$handler->display->display_options['relationships']['tmgmt_local_task']['relationship'] = 'tjid';
$handler->display->display_options['relationships']['tmgmt_local_task']['label'] = 'Translation Task';
$handler->display->display_options['relationships']['tmgmt_local_task']['required'] = TRUE;
$handler->display->display_options['defaults']['fields'] = FALSE;
/* Field: Translation Management Local Task: Translation management local task ID */
$handler->display->display_options['fields']['tltid']['id'] = 'tltid';
$handler->display->display_options['fields']['tltid']['table'] = 'tmgmt_local_task';
$handler->display->display_options['fields']['tltid']['field'] = 'tltid';
$handler->display->display_options['fields']['tltid']['relationship'] = 'tmgmt_local_task';
$handler->display->display_options['fields']['tltid']['label'] = '';
$handler->display->display_options['fields']['tltid']['exclude'] = TRUE;
$handler->display->display_options['fields']['tltid']['element_label_colon'] = FALSE;
/* Field: Translation Management Job Item: Tjid */
$handler->display->display_options['fields']['tjid']['id'] = 'tjid';
$handler->display->display_options['fields']['tjid']['table'] = 'tmgmt_job_item';
$handler->display->display_options['fields']['tjid']['field'] = 'tjid';
$handler->display->display_options['fields']['tjid']['exclude'] = TRUE;
/* Field: Translation Management Job Item: Label */
$handler->display->display_options['fields']['label']['id'] = 'label';
$handler->display->display_options['fields']['label']['table'] = 'tmgmt_job_item';
$handler->display->display_options['fields']['label']['field'] = 'label';
$handler->display->display_options['fields']['label']['alter']['path'] = 'translate/[tltid]/item/[tjid]';
/* Field: Translation Management Job Item: State */
$handler->display->display_options['fields']['state']['id'] = 'state';
$handler->display->display_options['fields']['state']['table'] = 'tmgmt_job_item';
$handler->display->display_options['fields']['state']['field'] = 'state';
/* Field: Translation Management Job Item: Progress */
$handler->display->display_options['fields']['progress']['id'] = 'progress';
$handler->display->display_options['fields']['progress']['table'] = 'tmgmt_job_item';
$handler->display->display_options['fields']['progress']['field'] = 'progress';
/* Field: Translation Management Job Item: Word count */
$handler->display->display_options['fields']['word_count']['id'] = 'word_count';
$handler->display->display_options['fields']['word_count']['table'] = 'tmgmt_job_item';
$handler->display->display_options['fields']['word_count']['field'] = 'word_count';
/* Field: Translation Management Local Task: Translate Task Item */
$handler->display->display_options['fields']['translate_task_item']['id'] = 'translate_task_item';
$handler->display->display_options['fields']['translate_task_item']['table'] = 'tmgmt_local_task';
$handler->display->display_options['fields']['translate_task_item']['field'] = 'translate_task_item';
$handler->display->display_options['fields']['translate_task_item']['relationship'] = 'tmgmt_local_task';
$handler->display->display_options['fields']['translate_task_item']['label'] = 'Action';
$handler->display->display_options['defaults']['arguments'] = FALSE;
/* Contextual filter: Translation Management Local Task: Translation management local task ID */
$handler->display->display_options['arguments']['tltid']['id'] = 'tltid';
$handler->display->display_options['arguments']['tltid']['table'] = 'tmgmt_local_task';
$handler->display->display_options['arguments']['tltid']['field'] = 'tltid';
$handler->display->display_options['arguments']['tltid']['relationship'] = 'tmgmt_local_task';
$handler->display->display_options['arguments']['tltid']['default_action'] = 'default';
$handler->display->display_options['arguments']['tltid']['default_argument_type'] = 'raw';
$handler->display->display_options['arguments']['tltid']['default_argument_options']['index'] = '1';
$handler->display->display_options['arguments']['tltid']['summary']['number_of_records'] = '0';
$handler->display->display_options['arguments']['tltid']['summary']['format'] = 'default_summary';
$handler->display->display_options['arguments']['tltid']['summary_options']['items_per_page'] = '25';
$handler->display->display_options['path'] = 'translate/%';
$handler->display->display_options['menu']['type'] = 'normal';
$handler->display->display_options['menu']['title'] = 'Translate';

/* Display: View Task */
$handler = $view->new_display('page', 'View Task', 'manager_view');
$handler->display->display_options['defaults']['title'] = FALSE;
$handler->display->display_options['title'] = 'Translate Task Items';
$handler->display->display_options['display_description'] = 'Show list of translatable task items - suitable for managers.';
$handler->display->display_options['defaults']['hide_admin_links'] = FALSE;
$handler->display->display_options['defaults']['access'] = FALSE;
$handler->display->display_options['access']['type'] = 'perm';
$handler->display->display_options['access']['perm'] = 'administer translation tasks';
$handler->display->display_options['defaults']['relationships'] = FALSE;
/* Relationship: Translation Management Job Item: Tjid */
$handler->display->display_options['relationships']['tjid']['id'] = 'tjid';
$handler->display->display_options['relationships']['tjid']['table'] = 'tmgmt_job_item';
$handler->display->display_options['relationships']['tjid']['field'] = 'tjid';
$handler->display->display_options['relationships']['tjid']['required'] = TRUE;
/* Relationship: Translation Management Job: Translator */
$handler->display->display_options['relationships']['translator']['id'] = 'translator';
$handler->display->display_options['relationships']['translator']['table'] = 'tmgmt_job';
$handler->display->display_options['relationships']['translator']['field'] = 'translator';
$handler->display->display_options['relationships']['translator']['relationship'] = 'tjid';
$handler->display->display_options['relationships']['translator']['required'] = TRUE;
/* Relationship: Translation Management Job: Translation Management Local Task */
$handler->display->display_options['relationships']['tmgmt_local_task']['id'] = 'tmgmt_local_task';
$handler->display->display_options['relationships']['tmgmt_local_task']['table'] = 'tmgmt_job';
$handler->display->display_options['relationships']['tmgmt_local_task']['field'] = 'tmgmt_local_task';
$handler->display->display_options['relationships']['tmgmt_local_task']['relationship'] = 'tjid';
$handler->display->display_options['relationships']['tmgmt_local_task']['label'] = 'Translation Task';
$handler->display->display_options['relationships']['tmgmt_local_task']['required'] = TRUE;
$handler->display->display_options['defaults']['fields'] = FALSE;
/* Field: Translation Management Local Task: Translation management local task ID */
$handler->display->display_options['fields']['tltid']['id'] = 'tltid';
$handler->display->display_options['fields']['tltid']['table'] = 'tmgmt_local_task';
$handler->display->display_options['fields']['tltid']['field'] = 'tltid';
$handler->display->display_options['fields']['tltid']['relationship'] = 'tmgmt_local_task';
$handler->display->display_options['fields']['tltid']['label'] = '';
$handler->display->display_options['fields']['tltid']['exclude'] = TRUE;
$handler->display->display_options['fields']['tltid']['element_label_colon'] = FALSE;
/* Field: Translation Management Job Item: Tjid */
$handler->display->display_options['fields']['tjid']['id'] = 'tjid';
$handler->display->display_options['fields']['tjid']['table'] = 'tmgmt_job_item';
$handler->display->display_options['fields']['tjid']['field'] = 'tjid';
$handler->display->display_options['fields']['tjid']['exclude'] = TRUE;
/* Field: Translation Management Job Item: Label */
$handler->display->display_options['fields']['label']['id'] = 'label';
$handler->display->display_options['fields']['label']['table'] = 'tmgmt_job_item';
$handler->display->display_options['fields']['label']['field'] = 'label';
$handler->display->display_options['fields']['label']['alter']['path'] = 'translate/[tltid]/item/[tjid]';
/* Field: Translation Management Job Item: State */
$handler->display->display_options['fields']['state']['id'] = 'state';
$handler->display->display_options['fields']['state']['table'] = 'tmgmt_job_item';
$handler->display->display_options['fields']['state']['field'] = 'state';
/* Field: Translation Management Job Item: Progress */
$handler->display->display_options['fields']['progress']['id'] = 'progress';
$handler->display->display_options['fields']['progress']['table'] = 'tmgmt_job_item';
$handler->display->display_options['fields']['progress']['field'] = 'progress';
/* Field: Translation Management Job Item: Word count */
$handler->display->display_options['fields']['word_count']['id'] = 'word_count';
$handler->display->display_options['fields']['word_count']['table'] = 'tmgmt_job_item';
$handler->display->display_options['fields']['word_count']['field'] = 'word_count';
$handler->display->display_options['defaults']['arguments'] = FALSE;
/* Contextual filter: Translation Management Local Task: Translation management local task ID */
$handler->display->display_options['arguments']['tltid']['id'] = 'tltid';
$handler->display->display_options['arguments']['tltid']['table'] = 'tmgmt_local_task';
$handler->display->display_options['arguments']['tltid']['field'] = 'tltid';
$handler->display->display_options['arguments']['tltid']['relationship'] = 'tmgmt_local_task';
$handler->display->display_options['arguments']['tltid']['default_action'] = 'default';
$handler->display->display_options['arguments']['tltid']['default_argument_type'] = 'raw';
$handler->display->display_options['arguments']['tltid']['default_argument_options']['index'] = '1';
$handler->display->display_options['arguments']['tltid']['summary']['number_of_records'] = '0';
$handler->display->display_options['arguments']['tltid']['summary']['format'] = 'default_summary';
$handler->display->display_options['arguments']['tltid']['summary_options']['items_per_page'] = '25';
$handler->display->display_options['path'] = 'manage-translate/task/%';
$handler->display->display_options['menu']['title'] = 'Translate';
$handler->display->display_options['menu']['weight'] = '0';
$handler->display->display_options['menu']['context'] = 0;
$translatables['tmgmt_local_translate'] = array(
  t('Master'),
  t('Translate Task'),
  t('more'),
  t('Apply'),
  t('Reset'),
  t('Sort by'),
  t('Asc'),
  t('Desc'),
  t('Items per page'),
  t('- All -'),
  t('Offset'),
  t('« first'),
  t('‹ previous'),
  t('next ›'),
  t('last »'),
  t('There are no translatable objects for you.'),
  t('Translation Management Job'),
  t('Translation Management Translator'),
  t('Tjid'),
  t('.'),
  t(','),
  t('Label'),
  t('Plugin'),
  t('Progress'),
  t('Translate Task Items'),
  t('Show list of translatable task items.'),
  t('Translation Task'),
  t('State'),
  t('Word count'),
  t('Action'),
  t('All'),
  t('View Task'),
  t('Show list of translatable task items - suitable for managers.'),
);
