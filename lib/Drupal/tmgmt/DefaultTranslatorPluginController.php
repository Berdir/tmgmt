<?php

/**
 * @file
 * Contains Drupal\tmgmt\DefaultTranslatorPluginController.
 */

namespace Drupal\tmgmt;

use Drupal\tmgmt\Plugin\Core\Entity\Job;
use Drupal\tmgmt\Plugin\Core\Entity\JobItem;
use Drupal\tmgmt\Plugin\Core\Entity\Translator;

/**
 * Default controller class for service plugins.
 *
 * @ingroup tmgmt_translator
 */
abstract class DefaultTranslatorPluginController extends PluginBase implements TranslatorPluginControllerInterface {

  protected $supportedRemoteLanguages = array();
  protected $remoteLanguagesMappings = array();

  /**
   * Implements TranslatorPluginControllerInterface::isAvailable().
   */
  public function isAvailable(Translator $translator) {
    // Assume that the translation service is always available.
    return TRUE;
  }

  /**
   * Implements TranslatorPluginControllerInterface::canTranslate().
   */
  public function canTranslate(Translator $translator, Job $job) {
    // The job is only translatable if the translator is available too.
    if ($this->isAvailable($translator) && array_key_exists($job->target_language, $translator->getSupportedTargetLanguages($job->source_language))) {
      // We can only translate this job if the target language of the job is in
      // one of the supported languages.
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Implements TranslatorPluginControllerInterface::cancelTranslation().
   */
  public function cancelTranslation(Job $job) {
    // Assume that we can cancel a translation job at any time.
    $job->setState(TMGMT_JOB_STATE_CANCELLED);
    return TRUE;
  }

  /**
   * Implements TranslatorPluginControllerInterface::getDefaultRemoteLanguagesMappings().
   */
  public function getDefaultRemoteLanguagesMappings() {
    return array();
  }

  /**
   * Implements TranslatorPluginControllerInterface::getSupportedLanguages().
   */
  public function getSupportedRemoteLanguages(Translator $translator) {
    return array();
  }

  /**
   * Implements TranslatorPluginControllerInterface::getRemoteLanguagesMappings().
   */
  public function getRemoteLanguagesMappings(Translator $translator) {
    if (!empty($this->remoteLanguagesMappings)) {
      return $this->remoteLanguagesMappings;
    }

    foreach (language_list() as $language => $info) {
      $this->remoteLanguagesMappings[$language] = $this->mapToRemoteLanguage($translator, $language);
    }

    return $this->remoteLanguagesMappings;
  }

  /**
   * Implements TranslatorPluginControllerInterface::mapToRemoteLanguage().
   */
  public function mapToRemoteLanguage(Translator $translator, $language) {
    if (!tmgmt_provide_remote_languages_mappings($translator)) {
      return $language;
    }

    if (isset($translator->settings['remote_languages_mappings'][$language])) {
      return $translator->settings['remote_languages_mappings'][$language];
    }

    $default_mappings = $this->getDefaultRemoteLanguagesMappings();

    if (isset($default_mappings[$language])) {
      return $default_mappings[$language];
    }

    return $language;
  }

  /**
   * Implements TranslatorPluginControllerInterface::mapToLocalLanguage().
   */
  public function mapToLocalLanguage(Translator $translator, $language) {
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
   * Implements TranslatorPluginControllerInterface::getSupportedTargetLanguages().
   */
  public function getSupportedTargetLanguages(Translator $translator, $source_language) {
    $languages = entity_metadata_language_list();
    unset($languages[LANGUAGE_NONE], $languages[$source_language]);
    return drupal_map_assoc(array_keys($languages));
  }

  /**
   * Implements TranslatorPluginControllerInterface::getNotCanTranslateReason().
   */
  public function getNotCanTranslateReason(Job $job) {
    $wrapper = entity_metadata_wrapper('tmgmt_job', $job);
    return t('@translator can not translate from @source to @target.', array('@translator' => $job->getTranslator()->label(), '@source' => $wrapper->source_language->label(), '@target' => $wrapper->target_language->label()));
  }

  /**
   * Implements TranslatorPluginControllerInterface::getNotAvailableReason().
   */
  public function getNotAvailableReason(Translator $translator) {
    return t('@translator is not available. Make sure it is properly !configured.', array('@translator' => $this->pluginInfo['label'], '!configured' => l(t('configured'), 'admin/config/regional/tmgmt/translators/manage/' . $translator->name)));
  }

  /**
   * Implements TranslatorPluginControllerInterface::defaultSettings().
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
   * Implements TranslatorPluginControllerInterface::checkoutInfo().
   */
  public function hasCheckoutSettings(Job $job) {
    return TRUE;
  }

  /**
   * Implements TranslatorPluginControllerInterface::acceptedDataItem().
   */
  public function acceptedDataItem(JobItem $job_item, array $key) {
    return TRUE;
  }
}

