<?php
/**
 * @file
 * tmgmt_job_management.views_default.inc
 */

/**
 * Implements hook_views_default_views().
 */
function tmgmt_job_management_views_default_views() {
  $export = array();

  $view = new view;
  $view->name = 'tmgmt_job_management_job_overview';
  $view->description = '';
  $view->tag = 'default';
  $view->base_table = 'tmgmt_job';
  $view->human_name = 'Translation job managment job overview';
  $view->core = 7;
  $view->api_version = '3.0';
  $view->disabled = FALSE; /* Edit this to true to make a default view disabled initially */

  /* Display: Master */
  $handler = $view->new_display('default', 'Master', 'default');
  $handler->display->display_options['title'] = 'Translation Jobs';
  $handler->display->display_options['access']['type'] = 'perm';
  $handler->display->display_options['access']['perm'] = 'access tmgmt ui';
  $handler->display->display_options['cache']['type'] = 'none';
  $handler->display->display_options['query']['type'] = 'views_query';
  $handler->display->display_options['query']['options']['query_comment'] = FALSE;
  $handler->display->display_options['exposed_form']['type'] = 'basic';
  $handler->display->display_options['pager']['type'] = 'full';
  $handler->display->display_options['pager']['options']['items_per_page'] = '10';
  $handler->display->display_options['style_plugin'] = 'table';
  $handler->display->display_options['style_options']['columns'] = array(
    'views_bulk_operations' => 'views_bulk_operations',
    'changed' => 'changed',
    'created' => 'created',
    'source_language' => 'source_language',
    'state' => 'state',
    'target_language' => 'target_language',
    'translator' => 'translator',
  );
  $handler->display->display_options['style_options']['default'] = 'changed';
  $handler->display->display_options['style_options']['info'] = array(
    'views_bulk_operations' => array(
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'changed' => array(
      'sortable' => 1,
      'default_sort_order' => 'desc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'created' => array(
      'sortable' => 1,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'source_language' => array(
      'sortable' => 1,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'state' => array(
      'sortable' => 1,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'target_language' => array(
      'sortable' => 1,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
    'translator' => array(
      'sortable' => 1,
      'default_sort_order' => 'asc',
      'align' => '',
      'separator' => '',
      'empty_column' => 0,
    ),
  );
  $handler->display->display_options['style_options']['override'] = 1;
  $handler->display->display_options['style_options']['sticky'] = 0;
  $handler->display->display_options['style_options']['empty_table'] = 0;
  /* Field: Translation Management Job: Bulk operations */
  $handler->display->display_options['fields']['views_bulk_operations']['id'] = 'views_bulk_operations';
  $handler->display->display_options['fields']['views_bulk_operations']['table'] = 'tmgmt_job';
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
    'rules_component::rules_tmgmt_job_accept_translation' => array(
      'selected' => 1,
      'use_queue' => 0,
      'skip_confirmation' => 1,
      'override_label' => 0,
      'label' => '',
    ),
    'rules_component::rules_tmgmt_job_cancel_translation' => array(
      'selected' => 1,
      'use_queue' => 0,
      'skip_confirmation' => 1,
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
    'action::views_bulk_operations_argument_selector_action' => array(
      'selected' => 0,
      'skip_confirmation' => 0,
      'override_label' => 0,
      'label' => '',
      'settings' => array(
        'url' => '',
      ),
    ),
    'action::system_goto_action' => array(
      'selected' => 0,
      'use_queue' => 0,
      'skip_confirmation' => 0,
      'override_label' => 0,
      'label' => '',
    ),
    'rules_component::rules_tmgmt_job_request_translation' => array(
      'selected' => 1,
      'use_queue' => 0,
      'skip_confirmation' => 1,
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
  );
  $handler->display->display_options['fields']['views_bulk_operations']['vbo']['enable_select_all_pages'] = 1;
  $handler->display->display_options['fields']['views_bulk_operations']['vbo']['display_type'] = '0';
  $handler->display->display_options['fields']['views_bulk_operations']['vbo']['display_result'] = 1;
  $handler->display->display_options['fields']['views_bulk_operations']['vbo']['merge_single_action'] = 0;
  $handler->display->display_options['fields']['views_bulk_operations']['vbo']['force_single'] = 0;
  /* Field: Translation Management Job: Changed */
  $handler->display->display_options['fields']['changed']['id'] = 'changed';
  $handler->display->display_options['fields']['changed']['table'] = 'tmgmt_job';
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
  /* Field: Translation Management Job: Created */
  $handler->display->display_options['fields']['created']['id'] = 'created';
  $handler->display->display_options['fields']['created']['table'] = 'tmgmt_job';
  $handler->display->display_options['fields']['created']['field'] = 'created';
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
  $handler->display->display_options['fields']['created']['element_label_colon'] = 1;
  $handler->display->display_options['fields']['created']['element_default_classes'] = 1;
  $handler->display->display_options['fields']['created']['hide_empty'] = 0;
  $handler->display->display_options['fields']['created']['empty_zero'] = 0;
  $handler->display->display_options['fields']['created']['hide_alter_empty'] = 1;
  $handler->display->display_options['fields']['created']['date_format'] = 'short';
  /* Field: Translation Management Job: Source_language */
  $handler->display->display_options['fields']['source_language']['id'] = 'source_language';
  $handler->display->display_options['fields']['source_language']['table'] = 'tmgmt_job';
  $handler->display->display_options['fields']['source_language']['field'] = 'source_language';
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
  /* Field: Translation Management Job: State */
  $handler->display->display_options['fields']['state']['id'] = 'state';
  $handler->display->display_options['fields']['state']['table'] = 'tmgmt_job';
  $handler->display->display_options['fields']['state']['field'] = 'state';
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
  /* Field: Translation Management Job: Target_language */
  $handler->display->display_options['fields']['target_language']['id'] = 'target_language';
  $handler->display->display_options['fields']['target_language']['table'] = 'tmgmt_job';
  $handler->display->display_options['fields']['target_language']['field'] = 'target_language';
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
  /* Field: Translation Management Job: Translator */
  $handler->display->display_options['fields']['translator']['id'] = 'translator';
  $handler->display->display_options['fields']['translator']['table'] = 'tmgmt_job';
  $handler->display->display_options['fields']['translator']['field'] = 'translator';
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
  $handler->display->display_options['fields']['translator']['element_label_colon'] = 1;
  $handler->display->display_options['fields']['translator']['element_default_classes'] = 1;
  $handler->display->display_options['fields']['translator']['hide_empty'] = 0;
  $handler->display->display_options['fields']['translator']['empty_zero'] = 0;
  $handler->display->display_options['fields']['translator']['hide_alter_empty'] = 1;
  /* Filter criterion: Translation Management Job: State */
  $handler->display->display_options['filters']['state']['id'] = 'state';
  $handler->display->display_options['filters']['state']['table'] = 'tmgmt_job';
  $handler->display->display_options['filters']['state']['field'] = 'state';
  $handler->display->display_options['filters']['state']['exposed'] = TRUE;
  $handler->display->display_options['filters']['state']['expose']['operator_id'] = 'state_op';
  $handler->display->display_options['filters']['state']['expose']['label'] = 'State';
  $handler->display->display_options['filters']['state']['expose']['operator'] = 'state_op';
  $handler->display->display_options['filters']['state']['expose']['identifier'] = 'state';
  $handler->display->display_options['filters']['state']['expose']['reduce'] = 0;
  /* Filter criterion: Translation Management Job: Source_language */
  $handler->display->display_options['filters']['source_language']['id'] = 'source_language';
  $handler->display->display_options['filters']['source_language']['table'] = 'tmgmt_job';
  $handler->display->display_options['filters']['source_language']['field'] = 'source_language';
  $handler->display->display_options['filters']['source_language']['exposed'] = TRUE;
  $handler->display->display_options['filters']['source_language']['expose']['operator_id'] = 'source_language_op';
  $handler->display->display_options['filters']['source_language']['expose']['label'] = 'Source language';
  $handler->display->display_options['filters']['source_language']['expose']['operator'] = 'source_language_op';
  $handler->display->display_options['filters']['source_language']['expose']['identifier'] = 'source_language';
  $handler->display->display_options['filters']['source_language']['expose']['reduce'] = 0;
  /* Filter criterion: Translation Management Job: Target_language */
  $handler->display->display_options['filters']['target_language']['id'] = 'target_language';
  $handler->display->display_options['filters']['target_language']['table'] = 'tmgmt_job';
  $handler->display->display_options['filters']['target_language']['field'] = 'target_language';
  $handler->display->display_options['filters']['target_language']['exposed'] = TRUE;
  $handler->display->display_options['filters']['target_language']['expose']['operator_id'] = 'target_language_op';
  $handler->display->display_options['filters']['target_language']['expose']['label'] = 'Target language';
  $handler->display->display_options['filters']['target_language']['expose']['operator'] = 'target_language_op';
  $handler->display->display_options['filters']['target_language']['expose']['identifier'] = 'target_language';
  $handler->display->display_options['filters']['target_language']['expose']['reduce'] = 0;
  /* Filter criterion: Translation Management Job: Translator */
  $handler->display->display_options['filters']['translator']['id'] = 'translator';
  $handler->display->display_options['filters']['translator']['table'] = 'tmgmt_job';
  $handler->display->display_options['filters']['translator']['field'] = 'translator';
  $handler->display->display_options['filters']['translator']['exposed'] = TRUE;
  $handler->display->display_options['filters']['translator']['expose']['operator_id'] = 'translator_op';
  $handler->display->display_options['filters']['translator']['expose']['label'] = 'Translator';
  $handler->display->display_options['filters']['translator']['expose']['operator'] = 'translator_op';
  $handler->display->display_options['filters']['translator']['expose']['identifier'] = 'translator';
  $handler->display->display_options['filters']['translator']['expose']['reduce'] = 0;

  /* Display: Page */
  $handler = $view->new_display('page', 'Page', 'page');
  $handler->display->display_options['path'] = 'admin/tmgmt/jobs';
  $handler->display->display_options['menu']['type'] = 'tab';
  $handler->display->display_options['menu']['title'] = 'job overview';
  $handler->display->display_options['menu']['weight'] = '-1';
  $handler->display->display_options['menu']['context'] = 0;
  $translatables['tmgmt_job_management_job_overview'] = array(
    t('Master'),
    t('Translation Jobs'),
    t('more'),
    t('Apply'),
    t('Reset'),
    t('Sort by'),
    t('Asc'),
    t('Desc'),
    t('Items per page'),
    t('- All -'),
    t('Offset'),
    t('Bulk operations'),
    t('Changed'),
    t('Created'),
    t('Source_language'),
    t('State'),
    t('Target_language'),
    t('Translator'),
    t('Source language'),
    t('Target language'),
    t('Page'),
  );

  $export['tmgmt_job_management_job_overview'] = $view;

  return $export;
}
