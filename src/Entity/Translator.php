<?php

/*
 * @file
 * Contains Drupal\tmgmt\Plugin\Core\Entity\Translator.
 */

namespace Drupal\tmgmt\Entity;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;

/**
 * Entity class for the tmgmt_translator entity.
 *
 * @ConfigEntityType(
 *   id = "tmgmt_translator",
 *   label = @Translation("Translator"),
 *   handlers = {
 *     "form" = {
 *       "edit" = "Drupal\tmgmt\Form\TranslatorForm",
 *       "add" = "Drupal\tmgmt\Form\TranslatorForm",
 *       "delete" = "Drupal\tmgmt\Form\TranslatorDeleteForm"
 *     },
 *     "list_builder" = "Drupal\tmgmt\Entity\Controller\TranslatorListBuilder",
 *     "access" = "Drupal\tmgmt\Entity\Controller\TranslatorAccessControlHandler",
 *   },
 *   uri_callback = "tmgmt_translator_uri",
 *   config_prefix = "translator",
 *   entity_keys = {
 *     "id" = "name",
 *     "label" = "label",
 *     "weight" = "weight"
 *   },
 *   links = {
 *     "edit-form" = "tmgmt.translator_entity",
 *     "add-form" = "tmgmt.translator_add",
 *     "delete-form" = "tmgmt.translator_delete",
 *   }
 * )
 *
 * @ingroup tmgmt_translator
 */
class Translator extends ConfigEntityBase {

  /**
   * Machine readable name of the translator.
   *
   * @var string
   */
  protected $name;

  /**
   * The UUID of this translator.
   *
   * @var string
   */
  protected $uuid;

  /**
   * Label of the translator.
   *
   * @var string
   */
  protected $label;

  /**
   * Description of the translator.
   *
   * @var string
   */
  protected $description;

  /**
   * Weight of the translator.
   *
   * @var int
   */
  protected $weight;

  /**
   * Plugin name of the translator.
   *
   * @type string
   */
  protected $plugin;

  /**
   * Translator type specific settings.
   *
   * @var array
   */
  protected $settings = array();

  /**
   * The supported target languages caches.
   *
   * @var array
   */
  protected $languageCache;

  /**
   * The supported language pairs caches.
   *
   * @var array
   */
  protected $languagePairsCache;

  /**
   * Whether the language cache in the database is outdated.
   *
   * @var boolean
   */
  protected $languageCacheOutdated;

  /**
   * {@inheritdoc}
   */
  public function id() {
    return $this->name;
  }

  /**
   * Returns the array of settings.
   *
   * See the documentation of the translator plugin for supported or
   * required settings.
   *
   * @return array
   *   The array of settings.
   */
  public function getSettings() {
    return $this->settings;
  }

  /**
   * Sets the array of settings.
   *
   * @param array $settings
   *   The array of settings.
   *
   * @return static
   *   The object itself for chaining.
   */
  public function setSettings(array $settings) {
    $this->settings = $settings;
    return $this;
  }

  /**
   * Retrieves a setting value from the translator settings. Pulls the default
   * values (if defined) from the plugin controller.
   *
   * @param string|array $name
   *   The name of the setting, an array with multiple keys for nested settings.
   *
   * @return
   *   The setting value or $default if the setting value is not set. Returns
   *   NULL if the setting does not exist at all.
   */
  public function getSetting($name) {
    if (is_array($name)) {
      if (NestedArray::keyExists($this->settings, $name)) {
        return NestedArray::getValue($this->settings, $name);
      }
      elseif ($controller = $this->getController()) {
        $defaults = $controller->defaultSettings();
        return NestedArray::getValue($defaults, $name);
      }
    }
    else {
      if (isset($this->settings[$name])) {
        return $this->settings[$name];
      }
      elseif ($controller = $this->getController()) {
        $defaults = $controller->defaultSettings();
        if (isset($defaults[$name])) {
          return $defaults[$name];
        }
      }
    }
  }

  /**
   * Sets a definition setting.
   *
   * @param string|array $setting_name
   *   The definition setting to set.
   * @param mixed $value
   *   The value to set.
   *
   * @return static
   *   The object itself for chaining.
   */
  public function setSetting($setting_name, $value) {
    NestedArray::setValue($this->settings, (array) $setting_name, $value);
    return $this;
  }

  /**
   * Returns the translator plugin ID.
   *
   * @return string
   *   The translator plugin ID used by this translator.
   */
  public function getPluginId() {
    return $this->plugin;
  }

  /**
   * {@inheritdoc}
   */
  public static function preDelete(EntityStorageInterface $storage, array $entities) {
    // We are never going to have many entities here, so we can risk a loop.
    foreach ($entities as $key => $name) {
      if (tmgmt_translator_busy($key)) {
        // The translator can't be deleted because it is currently busy. Remove
        // it from the ids so it wont get deleted in the parent implementation.
        unset($entities[$key]);
      }
    }
    parent::preDelete($storage, $entities);
  }

  /**
   * Returns the translator plugin controller of this translator.
   *
   * @return \Drupal\tmgmt\TranslatorPluginInterface
   */
  public function getController() {
    try {
      if (!empty($this->plugin)) {
        return \Drupal::service('plugin.manager.tmgmt.translator')->createInstance($this->plugin);
      }
    }
    catch (PluginException $e) {
    }
    return FALSE;
  }

  /**
   * Returns the supported target languages for this translator.
   *
   * @return array
   *   An array of supported target languages in ISO format.
   */
  public function getSupportedTargetLanguages($source_language) {
    if ($controller = $this->getController()) {
      if (isset($this->pluginInfo['cache languages']) && empty($this->pluginInfo['cache languages'])) {
        // This plugin doesn't support language caching.
        return $controller->getSupportedTargetLanguages($this, $source_language);
      }
      else {
        // Retrieve the supported languages from the cache.
        if (empty($this->languageCache) && $cache = \Drupal::cache('data')->get('tmgmt_languages:' . $this->name)) {
          $this->languageCache = $cache->data;
        }
        // Even if we successfully queried the cache it might not have an entry
        // for our source language yet.
        if (!isset($this->languageCache[$source_language])) {
          $this->languageCache[$source_language] = $controller->getSupportedTargetLanguages($this, $source_language);
          $this->updateCache();
        }
      }
      return $this->languageCache[$source_language];
    }
  }

  /** Gets the supported language pairs for this translator.
   *
   * @return array
   *   List of language pairs where a pair is an associative array of
   *   source_language and target_language.
   *   Example:
   *   array(
   *     array('source_language' => 'en-US', 'target_language' => 'de-DE'),
   *     array('source_language' => 'en-US', 'target_language' => 'de-CH'),
   *   )
   */
  public function getSupportedLanguagePairs() {
    if ($controller = $this->getController()) {
      if (isset($this->pluginInfo['cache languages']) && empty($this->pluginInfo['cache languages'])) {
        // This plugin doesn't support language caching.
        return $controller->getSupportedLanguagePairs($this);
      }
      else {
        // Retrieve the supported languages from the cache.
        if (empty($this->languagePairsCache) && $cache = \Drupal::cache('data')->get('tmgmt_language_pairs:' . $this->name)) {
          $this->languagePairsCache = $cache->data;
        }
        // Even if we successfully queried the cache data might not be yet
        // available.
        if (empty($this->languagePairsCache)) {
          $this->languagePairsCache = $controller->getSupportedLanguagePairs($this);
          $this->updateCache();
        }
      }
      return $this->languagePairsCache;
    }
  }

  /**
   * Clears the language cache for this translator.
   */
  public function clearLanguageCache() {
    $this->languageCache = array();
    \Drupal::cache('data')->delete('tmgmt_languages:' . $this->name);
    \Drupal::cache('data')->delete('tmgmt_language_pairs:' . $this->name);
  }


  /**
   * Check whether this translator can handle a particular translation job.
   *
   * @param $job
   *   The Job entity that should be translated.
   *
   * @return boolean
   *   TRUE if the job can be processed and translated, FALSE otherwise.
   */
  public function canTranslate(Job $job) {
    if ($controller = $this->getController()) {
      return $controller->canTranslate($this, $job);
    }
    return FALSE;
  }

  /**
   * Checks whether a translator is available.
   *
   * @return boolean
   *   TRUE if the translator plugin is available, FALSE otherwise.
   */
  public function isAvailable() {
    if ($controller = $this->getController()) {
      return $controller->isAvailable($this);
    }
    return FALSE;
  }

  /**
   * Returns if the plugin has any settings for this job.
   */
  public function hasCheckoutSettings(Job $job) {
    if ($controller = $this->getController()) {
      return $controller->hasCheckoutSettings($job);
    }
    return FALSE;
  }

  /**
   * @todo Remove this once http://drupal.org/node/1420364 is done.
   */
  public function getNotAvailableReason() {
    if ($controller = $this->getController()) {
      return $controller->getNotAvailableReason($this);
    }
    return FALSE;
  }

  /**
   * @todo Remove this once http://drupal.org/node/1420364 is done.
   */
  public function getNotCanTranslateReason(Job $job) {
    if ($controller = $this->getController()) {
      return $controller->getNotCanTranslateReason($job);
    }
    return FALSE;
  }

  /**
   * Determines if the translator plugin supports remote language mappings.
   *
   * @return bool
   *   In case translator does not explicitly state that it does not provide the
   *   mapping feature it will return TRUE.
   */
  public function provideRemoteLanguagesMapping() {
    if (!isset($this->settings['map_remote_languages'])) {
      return TRUE;
    }

    return $this->settings['map_remote_languages'];
 }

  /**
   * Maps local language to remote language.
   *
   * @param $language
   *   Local language code.
   *
   * @return string
   *   Remote language code.
   *
   * @ingroup tmgmt_remote_languages_mapping
   */
  public function mapToRemoteLanguage($language) {
    return $this->getController()->mapToRemoteLanguage($this, $language);
  }

  /**
   * Maps remote language to local language.
   *
   * @param $language
   *   Remote language code.
   *
   * @return string
   *   Local language code.
   *
   * @ingroup tmgmt_remote_languages_mapping
   */
  public function mapToLocalLanguage($language) {
    return $this->getController()->mapToLocalLanguage($this, $language);
  }

  /**
   * Updates the language cache.
   */
  protected function updateCache() {
    if ($controller = $this->getController()) {
      $info = $controller->getPluginDefinition();
      if (!isset($info['language cache']) || !empty($info['language cache'])) {
        \Drupal::cache('data')->set('tmgmt_languages:' . $this->name, $this->languageCache, Cache::PERMANENT, $this->getEntityType()->getListCacheTags());
        \Drupal::cache('data')->set('tmgmt_language_pairs:' . $this->name, $this->languagePairsCache, Cache::PERMANENT, $this->getEntityType()->getListCacheTags());
      }
    }
  }

}
