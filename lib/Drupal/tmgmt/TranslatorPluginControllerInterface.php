<?php

/**
 * @file
 * Contains Drupal\tmgmt\TranslatorPluginControllerInterface.
 */

namespace Drupal\tmgmt;

use Drupal\tmgmt\Plugin\Core\Entity\Job;
use Drupal\tmgmt\Plugin\Core\Entity\JobItem;
use Drupal\tmgmt\Plugin\Core\Entity\Translator;

/**
 * Interface for service plugin controllers.
 *
 * @ingroup tmgmt_translator
 */
interface TranslatorPluginControllerInterface extends PluginBaseInterface {

  /**
   * Checks whether a translator is available.
   *
   * @param Translator $translator
   *   The translator entity.
   *
   * @return boolean
   *   TRUE if the translator plugin is available, FALSE otherwise.
   */
  public function isAvailable(Translator $translator);

  /**
   * Return a reason why the translator is not available.
   *
   * @param Translator $translator
   *   The translator entity.
   *
   * Might be called when isAvailable() returns FALSE to get a reason that
   * can be displayed to the user.
   *
   * @todo Remove this once http://drupal.org/node/1420364 is done.
   */
  public function getNotAvailableReason(Translator $translator);

  /**
   * Check whether this service can handle a particular translation job.
   *
   * @param Translator $translator
   *   The Translator entity that should handle the translation.
   * @param Job $job
   *   The Job entity that should be translated.
   *
   * @return boolean
   *   TRUE if the job can be processed and translated, FALSE otherwise.
   */
  public function canTranslate(Translator $translator, Job $job);

  /**
   * Return a reason why the translator is not able to translate this job.
   *
   * @param Job $job
   *   The job entity.
   *
   * Might be called when canTranslate() returns FALSE to get a reason that
   * can be displayed to the user.
   *
   * @todo Remove this once http://drupal.org/node/1420364 is done.
   */
  public function getNotCanTranslateReason(Job $job);

  /**
   * Specifies default mappings for local to remote language codes.
   *
   * @return array
   *   An array of local => remote language codes.
   */
  public function getDefaultRemoteLanguagesMappings();

  /**
   * Gets all supported languages of the translator.
   *
   * @param Translator $translator
   *   Translator entity for which to get supported languages.
   *
   * @return array
   *   An array of language codes which are provided by the translator
   *   (remote language codes).
   */
  public function getSupportedRemoteLanguages(Translator $translator);

  /**
   * Gets existing remote languages mappings.
   *
   * @param Translator $translator
   *   Translator entity for which to get mappings.
   *
   * @return array
   *   An array of local => remote language codes.
   */
  public function getRemoteLanguagesMappings(Translator $translator);

  /**
   * @param Translator $translator
   *   Translator entity for which to get remote language.
   * @param $language
   *   Local language code.
   *
   * @return string
   *   Remote language code.
   */
  public function mapToRemoteLanguage(Translator $translator, $language);

  /**
   * Maps remote language to local language.
   *
   * @param Translator $translator
   *   Translator entity for which to get local language.
   * @param $language
   *   Remote language code.
   *
   * @return string
   *   Local language code.
   */
  public function mapToLocalLanguage(Translator $translator, $language);

  /**
   * Returns all available target languages that are supported by this service
   * when given a source language.
   *
   * @param Translator $translator
   *   The translator entity.
   * @param $source_language
   *   The source language.
   *
   * @return array
   *   An array of remote languages in ISO format.
   */
  public function getSupportedTargetLanguages(Translator $translator, $source_language);

  /**
   * Returns supported language pairs.
   *
   * This info may be used by other plugins to find out what language pairs
   * can handle the translator.
   *
   * @param Translator $translator
   *   The translator entity.
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
  public function getSupportedLanguagePairs(Translator $translator);


  /**
   * @abstract
   *
   * Submits the translation request and sends it to the translation provider.
   *
   * @param Job $job
   *   The job that should be submitted.
   */
  public function requestTranslation(Job $job);

  /**
   * Cancels a translation job.
   *
   * @param Job $job
   *   The job that should have its translation cancelled.
   *
   * @return boolean
   *   TRUE if the job could be cancelled, FALSE otherwise.
   */
  public function cancelTranslation(Job $job);

  /**
   * Defines default settings.
   *
   * @return array
   *   An array of default settings.
   */
  public function defaultSettings();

  /**
   * Returns if the translator has any settings for the passed job.
   */
  public function hasCheckoutSettings(Job $job);

  /**
   * Accept a single data item.
   *
   * @todo Using job item breaks the current convention which uses jobs.
   *
   * @param $job_item
   *   The Job item the accepted data item belongs to.
   * @param $key
   *   The key of the accepted data item.
   *   The key is an array containing the keys of a nested array hierarchy path.
   *
   * @return
   *   TRUE if the approving was succesfull, FALSE otherwise.
   *   In case of an error, it is the responsibility of the translator to
   *   provide informations about the failure by adding a message to the job
   *   item.
   */
  public function acceptedDataItem(JobItem $job_item, array $key);

}
