<?php

/**
 * @file
 * Contains \Drupal\tmgmt\SourcePluginUiBase.
 */

namespace Drupal\tmgmt;

use Drupal\Core\Form\FormState;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\tmgmt\Entity\JobItem;
use Drupal\tmgmt\SourcePluginUiInterface;

/**
 * Default ui controller class for source plugin.
 *
 * @ingroup tmgmt_source
 */
class SourcePluginUiBase extends PluginBase implements SourcePluginUiInterface {

  /**
   * {@inheritdoc}
   */
  public function reviewForm(array $form, FormStateInterface $form_state, JobItemInterface $item) {
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function reviewDataItemElement(array $form, FormStateInterface $form_state, $data_item_key, $parent_key, array $data_item, JobItemInterface $item) {
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function reviewFormValidate(array $form, FormStateInterface $form_state, JobItemInterface $item) {
    // Nothing to do here by default.
  }

  /**
   * {@inheritdoc}
   */
  public function reviewFormSubmit(array $form, FormStateInterface $form_state, JobItemInterface $item) {
    // Nothing to do here by default.
  }

  /**
   * {@inheritdoc}
   */
  public function overviewForm(array $form, FormStateInterface $form_state, $type) {
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function overviewFormValidate(array $form, FormStateInterface $form_state, $type) {
    // Nothing to do here by default.
  }

  /**
   * {@inheritdoc}
   */
  public function overviewFormSubmit(array $form, FormStateInterface $form_state, $type) {
    // Nothing to do here by default.
  }

  /**
   * {@inheritdoc}
   */
  public function hook_views_default_views() {
    return array();
  }

}
