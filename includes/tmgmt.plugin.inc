<?php

/**
 * @file
 * Contains the classes and interfaces for the services and source plugins.
 */

/**
 * Interface for source plugin controllers.
 *
 * @ingroup tmgmt_source
 */
interface TMGMTSourcePluginControllerInterface {

  /**
   * Constructor.
   *
   * @param $plugin
   *   The machine-readable name of the translator plugin.
   */
  public function __construct($plugin);

  /**
   * Returns the info of the type of the plugin.
   *
   * @see tmgmt_source_plugin_info()
   */
  public function pluginInfo();

  /**
   * Returns the type of the plugin.
   */
  public function pluginType();

  /**
   * Returns an array with the data structured for translation.
   *
   * @see TMGMTJobItem::getSourceData()
   */
  public function getData(TMGMTJobItem $job_item);

  /**
   * Saves a translation.
   *
   * @return boolean
   *   TRUE if the translation was saved successfully, FALSE otherwise.
   */
  public function saveTranslation(TMGMTJobItem $job_item);

  /**
   * Return a title for this job item.
   */
  public function getLabel(TMGMTJobItem $job_item);

  /**
   * Returns the Uri for this job item.
   *
   * @see entity_uri()
   */
  public function getUri(TMGMTJobItem $job_item);

}

/**
 * Default controller class for source plugins.
 *
 * @ingroup tmgmt_source
 */
abstract class TMGMTDefaultSourcePluginController implements TMGMTSourcePluginControllerInterface {

  protected $pluginType;
  protected $pluginInfo;

  /**
   * Implements TMGMTSourcePluginControllerInterface::__construct().
   */
  public function __construct($plugin) {
    $this->pluginType = $plugin;
    $this->pluginInfo = tmgmt_source_plugin_info($plugin);
  }

  /**
   * Implements TMGMTSourcePluginControllerInterface::pluginInfo().
   */
  public function pluginInfo() {
    return $this->pluginInfo;
  }

  /**
   * Implements TMGMTSourcePluginControllerInterface::pluginType().
   */
  public function pluginType() {
    return $this->pluginType;
  }

  /**
   * Implements TMGMTSourcePluginControllerInterface::getLabel().
   */
  public function getLabel(TMGMTJobItem $job_item) {
    return t('@plugin item unavailable (@item)', array('@plugin' => $this->pluginInfo['label'], '@item' => $job_item->item_type . ':' . $job_item->item_id));
  }

  /**
   * Implements TMGMTSourcePluginControllerInterface::getUri().
   */
  public function getUri(TMGMTJobItem $job_item) {
    return FALSE;
  }

}

/**
 * Interface for service plugin controllers.
 *
 * @ingroup tmgmt_translator
 */
interface TMGMTTranslatorPluginControllerInterface {

  /**
   * Constructor.
   *
   * @param $plugin
   *   The machine-readable name of the translator plugin.
   */
  public function __construct($plugin);

  /**
   * Returns the info of the type of the plugin.
   *
   * @see tmgmt_translator_plugin_info()
   */
  public function pluginInfo();

  /**
   * Returns the type of the plugin.
   */
  public function pluginType();

  /**
   * Checks whether a translator is available.
   *
   * @param TMGMTTranslator $translator
   *   The translator entity.
   *
   * @return boolean
   *   TRUE if the translator plugin is available, FALSE otherwise.
   */
  public function isAvailable(TMGMTTranslator $translator);

  /**
   * Return a reason why the translator is not available.
   *
   * @param TMGMTTranslator $translator
   *   The translator entity.
   *
   * Might be called when isAvailable() returns FALSE to get a reason that
   * can be displayed to the user.
   */
  public function getNotAvailableReason(TMGMTTranslator $translator);

  /**
   * Check whether this service can handle a particular translation job.
   *
   * @param TMGMTJob $job
   *   The TMGMTJob entity that should be translated.
   *
   * @return boolean
   *   TRUE if the job can be processed and translated, FALSE otherwise.
   */
  public function canTranslate(TMGMTJob $job);

  /**
   * Returns all available target languages that are supported by this service
   * when given a source language.
   *
   * @param TMGMTTranslator $translator
   *   The translator entity.
   * @param $source_language
   *   The source language.
   *
   * @return array
   *   An array of languages in ISO format.
   */
  public function getSupportedTargetLanguages(TMGMTTranslator $translator, $source_language);

  /**
   * Returns all languages that are supported by this service.
   *
   * @param TMGMTTranslator $translator
   *   The translator entity.
   *
   * @return array
   *   An array of languages in ISO format.
   */
  public function getSupportedLanguages(TMGMTTranslator $translator);

  /**
   * @abstract
   *
   * Submits the translation request and sends it to the translation provider.
   *
   * @param TMGMTJob $job
   *   The job that should be submitted.
   */
  public function requestTranslation(TMGMTJob $job);

  /**
   * Cancels a translation job.
   *
   * @param TMGMTJob $job
   *   The job that should have its translation cancelled.
   *
   * @return boolean
   *   TRUE if the job could be cancelled, FALSE otherwise.
   */
  public function cancelTranslation(TMGMTJob $job);

  /**
   * Defines default settings.
   *
   * @return array
   *   An array of default settings.
   */
  public function defaultPluginSettings();

  /**
   * Retrieves information about a translation job.
   *
   * @param TMGMTJob $job
   *   The translation job.
   */
  public function checkoutInfo(TMGMTJob $job);

  /**
   * A plugin can implement this method if it needs a settings form.
   *
   * This is not actually an interface method and that is why it is commented,
   * but we leave it here for API documentation.
   */
  public function pluginSettingsForm($form, &$form_state, TMGMTTranslator $translator);

  /**
   * A plugin can implement this method if it needs a job settings form.
   *
   * This is not actually an interface method and that is why it is commented,
   * but we leave it here for API documentation.
   */
  public function checkoutSettingsForm($form, &$form_state, TMGMTJob $job);

  /**
   * Returns if the translator has any settings for the passed job.
   */
  public function hasCheckoutSettings(TMGMTJob $job);

}

/**
 * Default controller class for service plugins.
 *
 * @ingroup tmgmt_translator
 */
abstract class TMGMTDefaultTranslatorPluginController implements TMGMTTranslatorPluginControllerInterface {

  protected $pluginType;
  protected $pluginInfo;

  /**
   * Impelements TMGMTTranslatorPluginController::__construct().
   */
  public function __construct($plugin) {
    $this->pluginType = $plugin;
    $this->pluginInfo = tmgmt_translator_plugin_info($plugin);
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::pluginInfo().
   */
  public function pluginInfo() {
    return $this->pluginInfo;
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::pluginType().
   */
  public function pluginType() {
    return $this->pluginType;
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::isAvailable().
   */
  public function isAvailable(TMGMTTranslator $translator) {
    // Assume that the translation service is always available.
    return TRUE;
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::canTranslate().
   */
  public function canTranslate(TMGMTJob $job) {
    // The job is only translatable if the translator is available too.
    if ($this->isAvailable($job->getTranslator()) && array_key_exists($job->target_language, $this->getSupportedTargetLanguages($job->getTranslator(), $job->source_language))) {
      // We can only translate this job if the target language of the job is in
      // one of the supported languages.
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::cancelTranslation().
   */
  public function cancelTranslation(TMGMTJob $job) {
    // Assume that we can cancel a translation job at any time.
    return TRUE;
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::getSupportedTargetLanguages().
   */
  public function getSupportedTargetLanguages(TMGMTTranslator $translator, $source_language) {
    $languages = $this->getSupportedLanguages($translator);
    unset($languages[$source_language]);
    return $languages;
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::getSupportedLanguages().
   */
  public function getSupportedLanguages(TMGMTTranslator $translator) {
    $languages = entity_metadata_language_list();
    return drupal_map_assoc(array_keys($languages));
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::getNotAvailableReason().
   */
  public function getNotAvailableReason(TMGMTTranslator $translator) {
    return t('@plugin needs to be configured before it can be used', array('@plugin' => $this->pluginInfo['label']));
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::defaultCheckoutSettings().
   */
  public function defaultCheckoutSettings() {
    // Check if any default settings are defined in the plugin info.
    if (isset($this->pluginInfo['default checkout settings'])) {
      return $this->pluginInfo['default checkout settings'];
    }
    return array();
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::defaultPluginSettings().
   */
  public function defaultPluginSettings() {
    // Check if any default settings are defined in the plugin info.
    if (isset($this->pluginInfo['default plugin settings'])) {
      return $this->pluginInfo['default plugin settings'];
    }
    return array();
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::pluginSettingsForm().
   */
  public function pluginSettingsForm($form, &$form_state, TMGMTTranslator $translator) {
    $form['#title'] = t('@plugin plugin settings', array('@plugin' => $this->pluginInfo['label']));
    if (empty($form)) {
      $form['#description'] = t("The @plugin plugin doesn't provide any settings.", array('@plugin' => $this->pluginInfo['label']));
    }
    else {
      $form['#description'] = t('These settings are handled by the @plugin plugin and apply whenever this translator is used in a job.', array('@plugin' => $this->pluginInfo['label']));
    }
    return $form;
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::checkoutSettingsForm().
   */
  public function checkoutSettingsForm($form, &$form_state, TMGMTJob $job) {
    $form['#title'] = t('@translator checkout settings', array('@translator' => $job->getTranslator()->label()));
    if (empty($form)) {
      $form['#description'] = t("@translator doesn't provide any checkout settings.", array('@translator' => $translator->label()));
    }
    return $form;
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::checkoutInfo().
   */
  public function checkoutInfo(TMGMTJob $job) {
    $label = $job->getTranslator()->label();
    $form['#title'] = t('@translator translation job information', array('@translator' => $label));
    $form['#description'] = t('There is no relevant information to be displayed.');
    return $form;
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::checkoutInfo().
   */
  public function hasCheckoutSettings(TMGMTJob $job) {
    return TRUE;
  }
}
