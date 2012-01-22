<?php

function tmgmt_i18n_string_source_tab_page($object_type) {
  module_load_include("i18n.inc", "i18n_string");
  $i18n_object_info = i18n_object_info($object_type);

  $states = tmgmt_job_states();
  $language_query = tmgmt_i18n_string_select_query($object_type);
  $query = $language_query['query'];
  $query->condition("i18n_s.textgroup", $i18n_object_info['string translation']['textgroup']);
  $query->condition("i18n_s.type", $i18n_object_info['string translation']['type']);

  $table = array(
    '#theme' => 'table',
    '#header' => array(
      'Name',
    ),
    '#rows' => array(),
  );

  foreach($language_query['languages'] as $lang_code) {
    $table['#header'][] = $lang_code;
  }

  $result = $query->execute()->fetchAll();
  foreach ($result as $item) {
    $row = array(
      'name' => $item->context,
    );
    foreach ($language_query['languages'] as $lang_code) {
      // $state = isset($states[$item->{"state_$lang_code"}]) ? $states[$item->{"state_$lang_code"}] : '';
      $row[$lang_code] = isset($item->{"lang_$lang_code"}) ? '✔' : '✖';
      // $row[$lang_code] .= ' ' . $state;
    }
    $table['#rows'][] = $row;
  }

  return $table;
}

function tmgmt_i18n_string_source_tab_page_default() {
  return 'i18n String translation Overview.';
}
