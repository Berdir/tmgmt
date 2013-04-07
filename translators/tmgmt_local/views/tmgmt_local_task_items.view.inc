<?php

$view = new view();
$view->name = 'tmgmt_local_task_items';
$view->description = '';
$view->tag = 'default';
$view->base_table = 'tmgmt_local_task_item';
$view->human_name = 'Translation Task Items';
$view->core = 7;
$view->api_version = '3.0';
$view->disabled = FALSE; /* Edit this to true to make a default view disabled initially */

/* Display: Master */
$handler = $view->new_display('default', 'Master', 'default');
$handler->display->display_options['title'] = 'Translation Task Items';
$handler->display->display_options['use_more_always'] = FALSE;
$handler->display->display_options['access']['type'] = 'none';
$handler->display->display_options['cache']['type'] = 'none';
$handler->display->display_options['query']['type'] = 'views_query';
$handler->display->display_options['exposed_form']['type'] = 'basic';
$handler->display->display_options['pager']['type'] = 'full';
$handler->display->display_options['pager']['options']['items_per_page'] = '20';
$handler->display->display_options['style_plugin'] = 'table';
/* Relationship: Translation Task Item: Tltid */
$handler->display->display_options['relationships']['tltid']['id'] = 'tltid';
$handler->display->display_options['relationships']['tltid']['table'] = 'tmgmt_local_task_item';
$handler->display->display_options['relationships']['tltid']['field'] = 'tltid';
/* Relationship: Translation Task Item: Tjiid */
$handler->display->display_options['relationships']['tjiid']['id'] = 'tjiid';
$handler->display->display_options['relationships']['tjiid']['table'] = 'tmgmt_local_task_item';
$handler->display->display_options['relationships']['tjiid']['field'] = 'tjiid';
/* Field: Translation Task Item: Translation task item ID */
$handler->display->display_options['fields']['tltiid']['id'] = 'tltiid';
$handler->display->display_options['fields']['tltiid']['table'] = 'tmgmt_local_task_item';
$handler->display->display_options['fields']['tltiid']['field'] = 'tltiid';
/* Field: Translation Task Item: Status */
$handler->display->display_options['fields']['status']['id'] = 'status';
$handler->display->display_options['fields']['status']['table'] = 'tmgmt_local_task_item';
$handler->display->display_options['fields']['status']['field'] = 'status';
/* Field: Translation Task Item: Operations */
$handler->display->display_options['fields']['operations']['id'] = 'operations';
$handler->display->display_options['fields']['operations']['table'] = 'tmgmt_local_task_item';
$handler->display->display_options['fields']['operations']['field'] = 'operations';

/* Display: Block */
$handler = $view->new_display('block', 'Block', 'block');
$handler->display->display_options['defaults']['hide_admin_links'] = FALSE;
$translatables['translation_task_items'] = array(
  t('Master'),
  t('Translation Task Items'),
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
  t('Translation Task'),
  t('Translation Management Job Item'),
  t('Translation task item ID'),
  t('.'),
  t(','),
  t('Status'),
  t('Operations'),
  t('Block'),
);
