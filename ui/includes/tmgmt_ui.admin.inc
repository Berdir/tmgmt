<?php

/**
 * @file
 * Please supply a file description.
 */

function tmgmt_translator_form($form, &$form_state, TMGMTTranslator $translator, $op = 'edit') {
  $form_state['build_info']['files'][] = drupal_get_path('module', 'tmgmt_ui') . '/includes/tmgmt_ui.admin.inc';
  // We don't want the terrible long title that Entity API generates for us.
  if (in_array($op, array('import', 'add'))) {
    drupal_set_title($op == 'import' ? t('Add Translator') : t('Import Translator'), PASS_THROUGH);
  }
  if ($op == 'clone') {
    $translator->label .= ' (cloned)';
    $translator->name = '';
  }
  $available_plugins = tmgmt_translator_plugin_labels();
  if ($translator->is_new) {
    $selected_plugin = isset($form_state['values']['translator_plugin']) ? $form_state['values']['translator_plugin'] : key($available_plugins);
  }
  else {
    $selected_plugin = $translator->translator_plugin;
  }
  $form['label'] = array(
    '#type' => 'textfield',
    '#title' => t('Label'),
    '#description' => t('The label of the translator.'),
    '#default_value' => isset($form_state['values']['label']) ? $form_state['values']['label'] : $translator->label,
    '#required' => TRUE,
    '#size' => 32,
    '#maxlength' => 64,
  );
  $form['name'] = array(
    '#type' => 'machine_name',
    '#title' => t('Machine name'),
    '#description' => t('The machine readable name of this translator. It must be unique, and it must contain only alphanumeric characters and underscores. Once created, you will not be able to change this value!'),
    '#default_value' => isset($translator->name) ? $translator->name : '',
    '#machine_name' => array(
      'exists' => 'tmgmt_translator_load',
      'source' => array('label'),
    ),
    '#disabled' => isset($translator->name) && $op != 'clone',
    '#size' => 32,
    '#maxlength' => 64,
  );
  $form['description'] = array(
    '#type' => 'textarea',
    '#title' => t('Description'),
    '#description' => t('The description of the translator.'),
    '#default_value' => isset($values['description']) ? $values['description'] : $translator->description,
    '#size' => 32,
    '#maxlength' => 64,
  );
  $form['translator_plugin'] = array(
    '#type' => 'select',
    '#title' => t('Plugin'),
    '#description' => t('The translator plugin that will be used.'),
    '#options' => $available_plugins,
    '#default_value' => $selected_plugin,
    '#required' => TRUE,
    '#access' => !isset($translator->translator_plugin),
    '#ajax' => array(
      'callback' => 'tmgmt_ui_ajax_callback_translator_plugin_settings',
      'wrapper' => 'tmgmt-translator-plugin-settings-wrapper',
    ),
  );
  $form['translator_plugin_settings_wrapper'] = array(
    '#type' => 'fieldset',
    '#title' => t('Translator plugin settings'),
    '#tree' => TRUE,
    '#prefix' => '<div id="tmgmt-translator-plugin-settings-wrapper">',
    '#suffix' => '</div>',
  );
  if ($selected_plugin) {
    $plugin_controller = tmgmt_translator_plugin_controller($selected_plugin);
    $plugin_info = tmgmt_translator_plugin_info($selected_plugin);
    if (method_exists($plugin_controller, 'settingsForm') && $settings = $plugin_controller->settingsForm(array(), $form_state)) {
      $form['translator_plugin_settings_wrapper']['#description'] = t('These settings apply to the @plugin plugin in this configuration set.', array('@plugin' => $plugin_info['label']));
      $form['translator_plugin_settings_wrapper']['translator_plugin_settings'] = $settings;
    }
    else {
      $form['translator_plugin_settings_wrapper']['#description'] = t("The @plugin plugin doesn't provide any settings.", array('@plugin' => $plugin_info['label']));
    }
  }
  return $form;
}

function tmgmt_ui_ajax_callback_translator_plugin_settings($form, &$form_state) {
  return $form['translator_plugin_settings_wrapper'];
}
