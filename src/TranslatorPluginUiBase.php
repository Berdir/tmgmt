<?php

/**
 * @file
 * Contains Drupal\tmgmt\TranslatorUIControllerInterface.
 */

namespace Drupal\tmgmt;

use Drupal\Component\Plugin\PluginBase as ComponentPluginBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\tmgmt\Entity\Job;
use Drupal\tmgmt\Entity\JobItem;
use Drupal\tmgmt\Entity\Translator;
use Drupal\Core\Render\Element;

/**
 * Default ui controller class for translator plugins.
 *
 * @ingroup tmgmt_translator
 */
class TranslatorPluginUiBase extends ComponentPluginBase implements TranslatorPluginUiInterface {

  /**
   * {@inheritdoc}
   */
  public function pluginSettingsForm(array $form, FormStateInterface $form_state, TranslatorInterface $translator, $busy = FALSE) {
    if (!Element::children($form)) {
      $form['#description'] = t("The @plugin plugin doesn't provide any settings.", array('@plugin' => $this->pluginDefinition['label']));
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function checkoutSettingsForm(array $form, FormStateInterface $form_state, JobInterface $job) {
    if (!Element::children($form)) {
      $form['#description'] = t("The @translator translator doesn't provide any checkout settings.", array('@translator' => $job->getTranslator()->label()));
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function checkoutInfo(JobInterface $job) {
    return array();
  }

  /**
   * Provides a simple wrapper for the checkout info fieldset.
   *
   * @param \Drupal\tmgmt\JobInterface $job
   *   Translation job object.
   * @param $form
   *   Partial form structure to be wrapped in the fieldset.
   *
   * @return
   *   The provided form structure wrapped in a collapsed fieldset.
   */
  public function checkoutInfoWrapper(JobInterface $job, $form) {
    $label = $job->getTranslator()->label();
    $form += array(
      '#title' => t('@translator translation job information', array('@translator' => $label)),
      '#type' => 'details',
      '#open' => FALSE,

    );
    return $form;
  }

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

}
