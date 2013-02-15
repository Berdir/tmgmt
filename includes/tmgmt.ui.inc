<?php


/**
 * Interface for source ui controllers.
 *
 * @ingroup tmgmt_source
 */
interface TMGMTSourceUIControllerInterface extends TMGMTPluginBaseInterface {

  /**
   * Form callback for the job item review form.
   */
  public function reviewForm($form, &$form_state, TMGMTJobItem $item);

  /**
   * Validation callback for the job item review form.
   */
  public function reviewFormValidate($form, &$form_state, TMGMTJobItem $item);

  /**
   * Submit callback for the job item review form.
   */
  public function reviewFormSubmit($form, &$form_state, TMGMTJobItem $item);

  /**
   * Implements hook_menu().
   *
   * @see tmgmt_ui_menu().
   */
  public function hook_menu();

  /**
   * Implements hook_forms().
   *
   * @see tmgmt_ui_forms().
   */
  public function hook_forms();

  /**
   * Implements hook_views_default_views().
   *
   * @see tmgmt_ui_views_default_views().
   */
  public function hook_views_default_views();

}

/**
 * Default ui controller class for source plugin.
 *
 * @ingroup tmgmt_source
 */
class TMGMTDefaultSourceUIController extends TMGMTPluginBase implements TMGMTSourceUIControllerInterface {

  /**
   * Implements TMGMTSourceUIControllerInterface::reviewForm().
   */
  public function reviewForm($form, &$form_state, TMGMTJobItem $item) {
    return $form;
  }

  /**
   * Implements TMGMTSourceUIControllerInterface::reviewFormValidate().
   */
  public function reviewFormValidate($form, &$form_state, TMGMTJobItem $item) {
    // Nothing to do here by default.
  }

  /**
   * Implements TMGMTSourceUIControllerInterface::reviewFormSubmit().
   */
  public function reviewFormSubmit($form, &$form_state, TMGMTJobItem $item) {
    // Nothing to do here by default.
  }

  /**
   * Implements TMGMTSourceUIControllerInterface::overviewForm().
   */
  public function overviewForm($form, &$form_state, $type) {
    return $form;
  }

  /**
   * Implements TMGMTSourceUIControllerInterface::overviewFormValidate().
   */
  public function overviewFormValidate($form, &$form_state, $type) {
    // Nothing to do here by default.
  }

  /**
   * Implements TMGMTSourceUIControllerInterface::overviewFormSubmit().
   */
  public function overviewFormSubmit($form, &$form_state, $type) {
    // Nothing to do here by default.
  }

  /**
   * Implements TMGMTSourceUIControllerInterface::hook_menu().
   */
  public function hook_menu() {
    $items = array();
    if ($types = tmgmt_source_translatable_item_types($this->pluginType)) {
      $defaults = array(
        'file' => isset($this->pluginInfo['file']) ? $this->pluginInfo['file'] : $this->pluginInfo['module'] . '.pages.inc',
        'file path' => isset($this->pluginInfo['file path']) ? $this->pluginInfo['file path'] : drupal_get_path('module', $this->pluginInfo['module']),
        'page callback' => 'drupal_get_form',
        'access callback' => 'tmgmt_job_access',
        'access arguments' => array('create'),
      );
      foreach ($types as $type => $name) {
        if (empty($items['admin/config/regional/tmgmt/' . $this->pluginType])) {
          // Make the first item type of this source the default menu tab.
          $items['admin/config/regional/tmgmt/' . $this->pluginType] = $defaults + array(
            'title' => t('@type sources', array('@type' => ucfirst($this->pluginInfo['label']))),
            'page arguments' => array('tmgmt_ui_' . $this->pluginType . '_source_' . $type . '_overview_form', $this->pluginType, $type),
            'type' => MENU_LOCAL_TASK,
          );
          $items['admin/config/regional/tmgmt/' . $this->pluginType . '/' . $type] = array(
            'title' => check_plain($name),
            'type' => MENU_DEFAULT_LOCAL_TASK,
          );
        }
        else {
          $items['admin/config/regional/tmgmt/' . $this->pluginType . '/' . $type] = $defaults + array(
            'title' => check_plain($name),
            'page arguments' => array('tmgmt_ui_' . $this->pluginType . '_source_' . $type . '_overview_form', $this->pluginType, $type),
            'type' => MENU_LOCAL_TASK,
          );
        }
      }
    }
    return $items;
  }

  /**
   * Implements TMGMTSourceUIControllerInterface::hook_form().
   */
  public function hook_forms() {
    $info = array();
    if ($types = tmgmt_source_translatable_item_types($this->pluginType)) {
      foreach (array_keys($types) as $type) {
        $info['tmgmt_ui_' . $this->pluginType . '_source_' . $type . '_overview_form'] = array(
          'callback' => 'tmgmt_ui_source_overview_form',
          'wrapper_callback' => 'tmgmt_ui_source_overview_form_defaults',
        );
      }
    }
    return $info;
  }

  /**
   * Implements TMGMTSourceUIControllerInterface::hook_views_default_views().
   */
  public function hook_views_default_views() {
    return array();
  }

}

/**
 * Interface for translator ui controllers.
 *
 * @ingroup tmgmt_translator
 */
interface TMGMTTranslatorUIControllerInterface extends TMGMTPluginBaseInterface {

  /**
   * Form callback for the plugin settings form.
   */
  public function pluginSettingsForm($form, &$form_state, TMGMTTranslator $translator, $busy = FALSE);

  /**
   * Form callback for the checkout settings form.
   */
  public function checkoutSettingsForm($form, &$form_state, TMGMTJob $job);

  /**
   * Retrieves information about a translation job.
   *
   * @param TMGMTJob $job
   *   The translation job.
   */
  public function checkoutInfo(TMGMTJob $job);

  /**
   * Form callback for the job item review form.
   */
  public function reviewForm($form, &$form_state, TMGMTJobItem $item);

  /**
   * Validation callback for the job item review form.
   */
  public function reviewFormValidate($form, &$form_state, TMGMTJobItem $item);

  /**
   * Submit callback for the job item review form.
   */
  public function reviewFormSubmit($form, &$form_state, TMGMTJobItem $item);

}

/**
 * Default ui controller class for translator plugins.
 *
 * @ingroup tmgmt_translator
 */
class TMGMTDefaultTranslatorUIController extends TMGMTPluginBase implements TMGMTTranslatorUIControllerInterface {

  /**
   * Implements TMGMTTranslatorUIControllerInterface::pluginSettingsForm().
   */
  public function pluginSettingsForm($form, &$form_state, TMGMTTranslator $translator, $busy = FALSE) {

    if (!empty($translator->plugin)) {
      $controller = tmgmt_translator_plugin_controller($translator->plugin);
    }

    // If current translator is configured to provide remote language mapping
    // provide the form to configure mappings, unless it does not exists yet.
    if (!empty($controller) && tmgmt_provide_remote_languages_mappings($translator)) {

      $form['remote_languages_mappings'] = array(
        '#tree' => TRUE,
        '#type' => 'fieldset',
        '#title' => t('Remote languages mappings'),
        '#description' => t('Here you can specify mappings of your local language codes to the translator language codes.'),
        '#collapsible' => TRUE,
        '#collapsed' => TRUE,
      );

      $options = array();
      foreach ($controller->getSupportedRemoteLanguages($translator) as $language) {
        $options[$language] = $language;
      }

      foreach ($controller->getRemoteLanguagesMappings($translator) as $local_language => $remote_language) {
        $form['remote_languages_mappings'][$local_language] = array(
          '#type' => 'textfield',
          '#title' => tmgmt_language_label($local_language) . ' (' . $local_language . ')',
          '#default_value' => $remote_language,
          '#size' => 6,
        );

        if (!empty($options)) {
          $form['remote_languages_mappings'][$local_language]['#type'] = 'select';
          $form['remote_languages_mappings'][$local_language]['#options'] = $options;
          $form['remote_languages_mappings'][$local_language]['#empty_option'] = ' - ';
          unset($form['remote_languages_mappings'][$local_language]['#size']);
        }
      }
    }

    if (!element_children($form)) {
      $form['#description'] = t("The @plugin plugin doesn't provide any settings.", array('@plugin' => $this->pluginInfo['label']));
    }
    return $form;
  }

  /**
   * Implements TMGMTTranslatorUIControllerInterface::checkoutSettingsForm().
   */
  public function checkoutSettingsForm($form, &$form_state, TMGMTJob $job) {
    if (!element_children($form)) {
      $form['#description'] = t("The @translator translator doesn't provide any checkout settings.", array('@translator' => $job->getTranslator()->label()));
    }
    return $form;
  }

  /**
   * Implements TMGMTTranslatorUIControllerInterface::checkoutInfo().
   */
  public function checkoutInfo(TMGMTJob $job) {
    return array();
  }

  /**
   * Provides a simple wrapper for the checkout info fieldset.
   *
   * @param TMGMTJob $job
   *   Translation job object.
   * @param $form
   *   Partial form structure to be wrapped in the fieldset.
   *
   * @return
   *   The provided form structure wrapped in a collapsed fieldset.
   */
  public function checkoutInfoWrapper(TMGMTJob $job, $form) {
    $label = $job->getTranslator()->label();
    $form += array(
      '#title' => t('@translator translation job information', array('@translator' => $label)),
      '#type' => 'fieldset',
      '#collapsible' => TRUE,
    );
    return $form;
  }

  /**
   * Implements TMGMTTranslatorUIControllerInterface::reviewForm().
   */
  public function reviewForm($form, &$form_state, TMGMTJobItem $item) {
    return $form;
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::reviewFormValidate().
   */
  public function reviewFormValidate($form, &$form_state, TMGMTJobItem $item) {
    // Nothing to do here by default.
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::reviewFormSubmit().
   */
  public function reviewFormSubmit($form, &$form_state, TMGMTJobItem $item) {
    // Nothing to do here by default.
  }

}
