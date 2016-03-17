<?php

/**
 * @file
 * Provides the user translator plugin controller.
 */

namespace Drupal\tmgmt_local\Plugin\tmgmt\Translator;

use Drupal\tmgmt\JobInterface;
use Drupal\tmgmt\TranslatorInterface;
use Drupal\tmgmt\TranslatorPluginBase;
use Drupal\tmgmt_local\LocalTaskInterface;

/**
 * Drupal user provider.
 *
 * @TranslatorPlugin(
 *   id = "local",
 *   label = @Translation("Drupal user"),
 *   description = @Translation("Allows local users to process translation jobs."),
 *   ui = "\Drupal\tmgmt_local\LocalTranslatorUi",
 *   default_settings = {},
 *   map_remote_languages = FALSE
 * )
 */
class LocalTranslator extends TranslatorPluginBase {

  protected $language_pairs = array();

  /**
   * {@inheritdoc}
   */
  public function requestTranslation(JobInterface $job) {
    $tuid = $job->getSetting('translator');

    // Create local task for this job.
    $local_task = tmgmt_local_task_create(array(
      'uid' => $job->getOwnerId(),
      'tuid' => $tuid,
      'tjid' => $job->id(),
      'title' => $job->label(),
    ));
    // If we have translator then switch to pending state.
    if ($tuid) {
      $local_task->status = LocalTaskInterface::STATUS_PENDING;
    }
    $local_task->save();

    // Create task items.
    foreach ($job->getItems() as $item) {
      $local_task->addTaskItem($item);
    }

    // The translation job has been successfully submitted.
    $job->submitted();
  }

  /**
   * {@inheritdoc}
   */
  public function getSupportedTargetLanguages(TranslatorInterface $translator, $source_language) {
    $languages = tmgmt_local_supported_target_languages($source_language);
    if (\Drupal::config('tmgmt_local.settings')->get('allow_all')) {
      $languages += parent::getSupportedTargetLanguages($translator, $source_language);
    }
    return $languages;
  }

  /**
   * {@inheritdoc}
   */
  public function getSupportedLanguagePairs(TranslatorInterface $translator) {

    if (!empty($this->language_pairs)) {
      return $this->language_pairs;
    }

    $roles = user_roles(TRUE, 'provide translation services');

    $query = db_select('field_data_tmgmt_translation_skills', 'ts');

    $query->join('users', 'u', 'u.uid = ts.entity_id AND u.status = 1');

    $query->addField('ts', 'tmgmt_translation_skills_language_from', 'source_language');
    $query->addField('ts', 'tmgmt_translation_skills_language_to', 'target_language');

    $query->condition('ts.deleted', 0);
    $query->condition('ts.entity_type', 'user');

    if (!in_array(DRUPAL_AUTHENTICATED_RID, array_keys($roles))) {
      $query->join('users_roles', 'ur', 'ur.uid = u.uid AND ur.rid');
      $or_conditions = db_or()->condition('ur.rid', array_keys($roles), 'IN')
        ->condition('u.uid', 1);
      $query->condition($or_conditions);
    }

    foreach ($query->execute()->fetchAll() as $item) {
      $this->language_pairs[] = array(
        'source_language' => $item->source_language,
        'target_language' => $item->target_language,
      );
    }

    return $this->language_pairs;
  }

}
