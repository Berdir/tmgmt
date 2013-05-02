<?php

/**
 * @file
 * Contains the abstract translator base plugin class.
 */

/**
 * Default controller class for service plugins.
 *
 * @ingroup tmgmt_translator
 */
abstract class TMGMTDefaultTranslatorPluginController extends TMGMTPluginBase implements TMGMTTranslatorPluginControllerInterface {

  protected $supportedRemoteLanguages = array();
  protected $remoteLanguagesMappings = array();

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
  public function canTranslate(TMGMTTranslator $translator, TMGMTJob $job) {
    // The job is only translatable if the translator is available too.
    if ($this->isAvailable($translator) && array_key_exists($job->target_language, $translator->getSupportedTargetLanguages($job->source_language))) {
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
    $job->setState(TMGMT_JOB_STATE_CANCELLED);
    return TRUE;
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::getDefaultRemoteLanguagesMappings().
   */
  public function getDefaultRemoteLanguagesMappings() {
    return array();
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::getSupportedLanguages().
   */
  public function getSupportedRemoteLanguages(TMGMTTranslator $translator) {
    return array();
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::getRemoteLanguagesMappings().
   */
  public function getRemoteLanguagesMappings(TMGMTTranslator $translator) {
    if (!empty($this->remoteLanguagesMappings)) {
      return $this->remoteLanguagesMappings;
    }

    foreach (language_list() as $language => $info) {
      $this->remoteLanguagesMappings[$language] = $this->mapToRemoteLanguage($translator, $language);
    }

    return $this->remoteLanguagesMappings;
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::mapToRemoteLanguage().
   */
  public function mapToRemoteLanguage(TMGMTTranslator $translator, $language) {
    if (!tmgmt_provide_remote_languages_mappings($translator)) {
      return $language;
    }

    if (!empty($translator->settings['remote_languages_mappings'][$language])) {
      return $translator->settings['remote_languages_mappings'][$language];
    }

    $default_mappings = $this->getDefaultRemoteLanguagesMappings();

    if (isset($default_mappings[$language])) {
      return $default_mappings[$language];
    }

    return $language;
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::mapToLocalLanguage().
   */
  public function mapToLocalLanguage(TMGMTTranslator $translator, $language) {
    if (!tmgmt_provide_remote_languages_mappings($translator)) {
      return $language;
    }

    if (isset($translator->settings['remote_languages_mappings']) && is_array($translator->settings['remote_languages_mappings'])) {
      $mappings = $translator->settings['remote_languages_mappings'];
    }
    else {
      $mappings = $this->getDefaultRemoteLanguagesMappings();
    }

    if ($remote_language = array_search($language, $mappings)) {
      return $remote_language;
    }

    return $language;
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::getSupportedTargetLanguages().
   */
  public function getSupportedTargetLanguages(TMGMTTranslator $translator, $source_language) {
    $languages = entity_metadata_language_list();
    unset($languages[LANGUAGE_NONE], $languages[$source_language]);
    return drupal_map_assoc(array_keys($languages));
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::getSupportedLanguagePairs().
   *
   * Default implementation that gets target languages for each remote language.
   * This approach is ineffective and therefore it is advised that a plugin
   * should provide own implementation.
   */
  public function getSupportedLanguagePairs(TMGMTTranslator $translator) {
    $language_pairs = array();

    foreach ($this->getSupportedRemoteLanguages($translator) as $source_language) {
      foreach ($this->getSupportedTargetLanguages($translator, $source_language) as $target_language) {
        $language_pairs[] = array('source_language' => $source_language, 'target_language' => $target_language);
      }
    }

    return $language_pairs;
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::getNotCanTranslateReason().
   */
  public function getNotCanTranslateReason(TMGMTJob $job) {
    $wrapper = entity_metadata_wrapper('tmgmt_job', $job);
    return t('@translator can not translate from @source to @target.', array('@translator' => $job->getTranslator()->label(), '@source' => $wrapper->source_language->label(), '@target' => $wrapper->target_language->label()));
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::getNotAvailableReason().
   */
  public function getNotAvailableReason(TMGMTTranslator $translator) {
    return t('@translator is not available. Make sure it is properly !configured.', array('@translator' => $this->pluginInfo['label'], '!configured' => l(t('configured'), 'admin/config/regional/tmgmt/translators/manage/' . $translator->name)));
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::defaultSettings().
   */
  public function defaultSettings() {
    $defaults = array('auto_accept' => FALSE);
    // Check if any default settings are defined in the plugin info.
    if (isset($this->pluginInfo['default settings'])) {
      return array_merge($defaults, $this->pluginInfo['default settings']);
    }
    return $defaults;
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::checkoutInfo().
   */
  public function hasCheckoutSettings(TMGMTJob $job) {
    return TRUE;
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::acceptedDataItem().
   */
  public function acceptetDataItem(TMGMTJobItem $job_item, array $key) {
    return TRUE;
  }
}
