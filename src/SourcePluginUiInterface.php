<?php


/**
 * @file
 * Contains Drupal\tmgmt\SourcePluginUiInterface.
 */

namespace Drupal\tmgmt;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\tmgmt\Entity\JobItem;
use TMGMTPluginBaseInterface;

/**
 * Interface for source ui controllers.
 *
 * @ingroup tmgmt_source
 */
interface SourcePluginUiInterface extends PluginInspectionInterface {

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

  /**
   * {@inheritdoc}
   *
   * @see tmgmt_ui_views_default_views().
   */
  public function hook_views_default_views();

}
