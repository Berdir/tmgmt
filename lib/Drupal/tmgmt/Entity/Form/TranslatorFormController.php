<?php

/**
 * @file
 * Contains \Drupal\tmgmt\Entity\Form\TranslatorFormController.
 */

namespace Drupal\tmgmt\Entity\Form;

/**
 * Form controller for the translator edit forms.
 *
 * @ingroup tmgmt_translator
 */
class TranslatorFormController extends TmgmtFormControllerBase {

  /**
   * Overrides Drupal\Core\Entity\EntityFormController::form().
   */
  public function form(array $form, array &$form_state) {
    $form = parent::form($form, $form_state);
    $entity = $this->entity;
    // Check if the translator is currently in use.
    if ($busy = !$entity->isNew() ? tmgmt_translator_busy($entity->name) : FALSE) {
      drupal_set_message(t("This translator is currently in use. It cannot be deleted. The chosen Translation Plugin cannot be changed."), 'warning');
    }
    $available = $this->translatorManager->getLabels();
    // If the translator plugin is not set, pick the first available plugin as the
    // default.
    $entity->plugin = empty($entity->plugin) ? key($available) : $entity->plugin;
    $form['label'] = array(
      '#type' => 'textfield',
      '#title' => t('Label'),
      '#description' => t('The label of the translator.'),
      '#default_value' => $entity->label,
      '#required' => TRUE,
      '#size' => 32,
      '#maxlength' => 64,
    );
    $form['name'] = array(
      '#type' => 'machine_name',
      '#title' => t('Machine name'),
      '#description' => t('The machine readable name of this translator. It must be unique, and it must contain only alphanumeric characters and underscores. Once created, you will not be able to change this value!'),
      '#default_value' => $entity->name,
      '#machine_name' => array(
        'exists' => 'tmgmt_translator_load',
        'source' => array('label'),
      ),
      '#disabled' => !$entity->isNew(),
      '#size' => 32,
      '#maxlength' => 64,
    );
    $form['description'] = array(
      '#type' => 'textarea',
      '#title' => t('Description'),
      '#description' => t('The description of the translator.'),
      '#default_value' => $entity->description,
      '#size' => 32,
      '#maxlength' => 255,
    );
    $form['settings'] = array(
      '#type' => 'fieldset',
      '#title' => t('Translator settings'),
      '#tree' => TRUE,
    );
    $form['settings']['auto_accept'] = array(
      '#type' => 'checkbox',
      '#title' => t('Auto accept finished translations'),
      '#description' => t('This skips the reviewing process and automatically accepts all translations as soon as they are returned by the translation provider.'),
      '#default_value' => $entity->getSetting('auto_accept'),
      '#weight' => -1,
    );
    if (!element_children($form['settings'])) {
      unset($form['settings']);
    }
    $form['plugin_wrapper'] = array(
      '#type' => 'container',
      '#prefix' => '<div id="tmgmt-plugin-wrapper">',
      '#suffix' => '</div>',
    );
    // Pull the translator plugin info if any.
    if ($entity->plugin) {
      $definition = $this->translatorManager->getDefinition($entity->plugin);
      $form['plugin_wrapper']['plugin'] = array(
        '#type' => 'select',
        '#title' => t('Translator plugin'),
        '#description' => isset($definition['description']) ? filter_xss($definition['description']) : '',
        '#options' => $available,
        '#default_value' => $entity->plugin,
        '#required' => TRUE,
        '#disabled' => $busy,
        '#ajax' => array(
          'callback' => array($this, 'ajaxTranslaorPluginSelect'),
          'wrapper' => 'tmgmt-plugin-wrapper',
        ),
      );
      $form['plugin_wrapper']['settings'] = array(
        '#type' => 'fieldset',
        '#title' => t('@plugin plugin settings', array('@plugin' => $definition['label'])),
        '#tree' => TRUE,
      );
      // Add the translator plugin settings form.
      $plugin_ui = $this->translatorManager->createUIInstance($entity->plugin);
      $form['plugin_wrapper']['settings'] += $plugin_ui->pluginSettingsForm($form['plugin_wrapper']['settings'], $form_state, $entity, $busy);
    }
    // Add a submit button and a cancel link to the form.
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Save translator'),
      '#disabled' => empty($available),
    );
    $form['actions']['delete'] = array(
      '#type' => 'submit',
      '#value' => t('Delete'),
      '#submit' => array('tmgmt_ui_submit_redirect'),
      '#redirect' => 'admin/tmgmt/translators/manage/' . $entity->id() . '/delete',
      '#access' => !$entity->isNew(),
    );
    $form['actions']['cancel'] = array(
      '#type' => 'link',
      '#title' => t('Cancel'),
      '#href' => 'admin/config/regional/tmgmt_translator',
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validate(array $form, array &$form_state) {
    parent::validate($form, $form_state);
    if (empty($form_state['values']['plugin'])) {
      form_set_error('plugin', t('You have to select a translator plugin.'));
    }
  }


  /**
   * Overrides Drupal\Core\Entity\EntityFormController::save().
   */
  public function save(array $form, array &$form_state) {
    $entity = $this->entity;
    $status = $entity->save();

    if ($status === SAVED_UPDATED) {
      drupal_set_message(format_string('%label configuration has been updated.', array('%label' => $entity->label())));
    }
    else {
      drupal_set_message(format_string('%label configuration has been created.', array('%label' => $entity->label())));
    }

    $form_state['redirect'] = 'admin/config/regional/tmgmt_translator';
  }

  /**
   * {@inheritdoc}
   */
  public function delete(array $form, array &$form_state) {
    $entity = $this->entity;
    $form_state['redirect'] = 'admin/config/regional/tmgmt_translator/manage/' . $entity->id() . '/delete';
  }


  /**
   * Ajax callback for loading the translator plugin settings form for the
   * currently selected translator plugin.
   */
  function ajaxTranslaorPluginSelect($form, &$form_state) {
    return $form['plugin_wrapper'];
  }

}
