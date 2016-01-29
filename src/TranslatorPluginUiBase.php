<?php

/**
 * @file
 * Contains Drupal\tmgmt\TranslatorUIControllerInterface.
 */

namespace Drupal\tmgmt;

use Drupal\Component\Plugin\PluginBase as ComponentPluginBase;
use Drupal\Core\Form\FormStateInterface;
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

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    return $form;
  }

  /**
   * Ajax callback to fetch the options provided by a translator.
   */
  public function ajaxUpdateSettings(array $form, FormStateInterface $form_state) {
    return $form['plugin_wrapper'];
  }

  /**
   * Handles submit call of "Connect" button.
   */
  public function submitConnect(array $form, FormStateInterface $form_state) {
    // When this method is called the form already passed validation and we can
    // assume that credentials are valid.
    drupal_set_message(t('Successfully connected!'));
    $form_state->setRebuild();
  }

  /**
   * Adds a "Connect" button to a form.
   *
   * @return array
   *   A form array containing "Connect" button.
   */
  public function addConnectButton() {
    $form['connect'] = array(
      '#type' => 'submit',
      '#value' => t('Connect'),
      '#submit' => array(array($this, 'submitConnect')),
      '#limit_validation_errors' => array(array('settings')),
      '#executes_submit_callback' => TRUE,
      '#ajax' => array(
        'callback' => array($this, 'ajaxUpdateSettings'),
        'wrapper' => 'tmgmt-plugin-wrapper',
      ),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    // Nothing to do here by default.
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    // Nothing to do here by default.
  }

}
