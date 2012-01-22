<?php

/**
 * @file
 * Please supply a file description.
 */

/**
 * Entity API form for the translator entity.
 */
function tmgmt_translator_form($form, &$form_state, TMGMTTranslator $translator, $op = 'edit') {
  // We don't want the terrible long title that Entity API generates for us.
  if (in_array($op, array('import', 'add'))) {
    drupal_set_title($op == 'import' ? t('Add Translator') : t('Import Translator'), PASS_THROUGH);
  }
  $available_plugins = tmgmt_translator_plugin_labels();
  // Initialize some of the default values.
  foreach (array('description', 'label', 'name', 'translator_plugin') as $key) {
    $translator->{$key} = isset($form_state['values'][$key]) ? $form_state['values'][$key] : $translator->{$key};
  }
  // If the translator plugin is not set, pick the first available plugin as the
  // default.
  $translator->translator_plugin = empty($translator->translator_plugin) ? key($available_plugins) : $translator->translator_plugin;
  $form['label'] = array(
    '#type' => 'textfield',
    '#title' => t('Label'),
    '#description' => t('The label of the translator.'),
    '#default_value' => $translator->label,
    '#required' => TRUE,
    '#size' => 32,
    '#maxlength' => 64,
  );
  $form['name'] = array(
    '#type' => 'machine_name',
    '#title' => t('Machine name'),
    '#description' => t('The machine readable name of this translator. It must be unique, and it must contain only alphanumeric characters and underscores. Once created, you will not be able to change this value!'),
    '#default_value' => $translator->name,
    '#machine_name' => array(
      'exists' => 'tmgmt_ui_translator_exists',
      'source' => array('label'),
    ),
    '#disabled' => empty($translator->is_new) && $op != 'clone',
    '#size' => 32,
    '#maxlength' => 64,
  );
  $form['description'] = array(
    '#type' => 'textarea',
    '#title' => t('Description'),
    '#description' => t('The description of the translator.'),
    '#default_value' => $translator->description,
    '#size' => 32,
    '#maxlength' => 64,
  );
  if (!empty($available_plugins)) {
    $form['translator_plugin'] = array(
      '#type' => 'select',
      '#title' => t('Plugin'),
      '#description' => t('The translator plugin that will be used.'),
      '#options' => $available_plugins,
      '#default_value' => $translator->translator_plugin,
      '#required' => TRUE,
      '#access' => !empty($translator->is_new),
      '#ajax' => array(
        'callback' => 'tmgmt_ui_ajax_callback_translator_plugin_settings',
        'wrapper' => 'tmgmt-translator-plugin-settings-wrapper',
      ),
    );
    $form['translator_plugin_settings_wrapper'] = array(
      '#type' => 'fieldset',
      '#title' => t('Translator plugin settings'),
      '#prefix' => '<div id="tmgmt-translator-plugin-settings-wrapper">',
      '#suffix' => '</div>',
    );
    if ($translator->translator_plugin) {
      $controller = tmgmt_translator_plugin_controller($translator->translator_plugin);
      $info = $controller->pluginInfo();
      if ($settings = $controller->settingsForm(array(), $form_state, $translator)) {
        $form['translator_plugin_settings_wrapper']['#description'] = t('These settings apply to the @plugin plugin in this configuration set.', array('@plugin' => $info['label']));
        $form['translator_plugin_settings_wrapper']['settings'] = $settings;
        $form['translator_plugin_settings_wrapper']['settings']['#tree'] = TRUE;
      }
      else {
        $form['translator_plugin_settings_wrapper']['#description'] = t("The @plugin plugin doesn't provide any settings.", array('@plugin' => $info['label']));
      }
    }
  }
  else {
    // Fallback for when no translator plugins are available.
    $form['translator_plugin'] = array(
      '#type' => 'item',
      '#title' => t('Plugin'),
      '#description' => t('There are no available translator plugins.'),
      '#required' => TRUE,
    );
  }
  $form['actions']['#type'] = 'actions';
  $form['actions']['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Submit'),
    '#disabled' => empty($available_plugins),
  );
  $form['actions']['cancel'] = array(
    '#type' => 'link',
    '#title' => t('Cancel'),
    '#href' => 'admin/config/regional/tmgmt/translators',
  );
  return $form;
}

/**
 * Default submit handler for Homebox forms. Populates and stores the submitted
 * values for the Homebox entity via entity_ui_form_submit_build_entity().
 *
 * @param $form
 *   An associative array containing the structure of the form.
 * @param $form_state
 *   The standard associative array containing the current state of the form.
 */
function tmgmt_translator_form_submit($form, &$form_state) {
  entity_ui_form_submit_build_entity($form, $form_state)->save();
  drupal_set_message(t('The configuration options have been saved.'));
  $form_state['redirect'] = 'admin/config/regional/tmgmt/translators';
}

/**
 * Ajax callback for loading the translator plugin settings form for the
 * currently selected translator plugin.
 */
function tmgmt_ui_ajax_callback_translator_plugin_settings($form, &$form_state) {
  return $form['translator_plugin_settings_wrapper'];
}

/**
 * Checks whether a translator entity with the supplied name already exists.
 *
 * We can't user entity_load or any of its wrapper functions for that as our
 * translator entity controller filters out broken translator entities (e.g. if
 * the translator plugin of the translator entity doesn't exist (anymore).
 *
 * @param $name
 *   The machine-readable name of the translator entity that we are trying to
 *   save.
 *
 * @return bool
 *   TRUE if a translator entity with the same machine-readable already exists,
 *   FALSE otherwise.
 */
function tmgmt_ui_translator_exists($name) {
  $query = new EntityFieldQuery();
  $query = $query->entityCondition('entity_type', 'tmgmt_translator')
    ->propertyCondition('name', $name)
    ->count()
    ->range(0, 1);
  return (bool) $query->execute();
}
