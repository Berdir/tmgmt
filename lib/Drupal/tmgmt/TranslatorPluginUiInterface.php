<?php

/**
 * @file
 * Contains Drupal\tmgmt\TranslatorUIControllerInterface.
 */

namespace Drupal\tmgmt;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\tmgmt\Entity\Job;
use Drupal\tmgmt\Entity\JobItem;
use Drupal\tmgmt\Entity\Translator;

/**
 * Interface for translator ui controllers.
 *
 * @ingroup tmgmt_translator
 */
interface TranslatorPluginUiInterface extends PluginInspectionInterface {

  /**
   * Form callback for the plugin settings form.
   */
  public function pluginSettingsForm($form, &$form_state, Translator $translator, $busy = FALSE);

  /**
   * Form callback for the checkout settings form.
   */
  public function checkoutSettingsForm($form, &$form_state, Job $job);

  /**
   * Retrieves information about a translation job.
   *
   * Services based translators with remote states should place a Poll button
   * here to sync the job state.
   *
   * @param \Drupal\tmgmt\Entity\Job $job
   *   The translation job.
   */
  public function checkoutInfo(Job $job);

  /**
   * Form callback for the job item review form.
   */
  public function reviewForm($form, &$form_state, JobItem $item);

  /**
   * Form callback for the data item element form.
   */
  public function reviewDataItemElement($form, &$form_state, $data_item_key, $parent_key, array $data_item, JobItem $item);

  /**
   * Validation callback for the job item review form.
   */
  public function reviewFormValidate($form, &$form_state, JobItem $item);

  /**
   * Submit callback for the job item review form.
   */
  public function reviewFormSubmit($form, &$form_state, JobItem $item);

}
