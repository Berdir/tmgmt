<?php

/**
 * @file
 * Contains Drupal\tmgmt\TranslatorUIControllerInterface.
 */

namespace Drupal\tmgmt;

use Drupal\Component\Plugin\PluginBase as ComponentPluginBase;
use Drupal\tmgmt\Entity\Job;
use Drupal\tmgmt\Entity\JobItem;
use Drupal\tmgmt\Entity\Translator;

/**
 * Default ui controller class for translator plugins.
 *
 * @ingroup tmgmt_translator
 */
class TranslatorPluginUiBase extends ComponentPluginBase implements TranslatorPluginUiInterface {

  /**
   * {@inheritdoc}
   */
  public function pluginSettingsForm($form, &$form_state, Translator $translator, $busy = FALSE) {

    $controller = $translator->getController();
    // If current translator is configured to provide remote language mapping
    // provide the form to configure mappings, unless it does not exists yet.
    if (!empty($controller) && tmgmt_provide_remote_languages_mappings($translator)) {

      $form['remote_languages_mappings'] = array(
        '#tree' => TRUE,
        '#type' => 'fieldset',
        '#title' => t('Remote languages mappings'),
        '#description' => t('Here you can specify mappings of your local language codes to the translator language codes.'),
        '#collapsible' => TRUE,
        '#collapsed' => TRUE,
      );

      $options = array();
      foreach ($controller->getSupportedRemoteLanguages($translator) as $language) {
        $options[$language] = $language;
      }

      foreach ($controller->getRemoteLanguagesMappings($translator) as $local_language => $remote_language) {
        $form['remote_languages_mappings'][$local_language] = array(
          '#type' => 'textfield',
          '#title' => tmgmt_language_label($local_language) . ' (' . $local_language . ')',
          '#default_value' => $remote_language,
          '#size' => 6,
        );

        if (!empty($options)) {
          $form['remote_languages_mappings'][$local_language]['#type'] = 'select';
          $form['remote_languages_mappings'][$local_language]['#options'] = $options;
          $form['remote_languages_mappings'][$local_language]['#empty_option'] = ' - ';
          unset($form['remote_languages_mappings'][$local_language]['#size']);
        }
      }
    }

    if (!element_children($form)) {
      $form['#description'] = t("The @plugin plugin doesn't provide any settings.", array('@plugin' => $this->pluginInfo['label']));
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function checkoutSettingsForm($form, &$form_state, Job $job) {
    if (!element_children($form)) {
      $form['#description'] = t("The @translator translator doesn't provide any checkout settings.", array('@translator' => $job->getTranslator()->label()));
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function checkoutInfo(Job $job) {
    return array();
  }

  /**
   * Provides a simple wrapper for the checkout info fieldset.
   *
   * @param \Drupal\tmgmt\Entity\Job $job
   *   Translation job object.
   * @param $form
   *   Partial form structure to be wrapped in the fieldset.
   *
   * @return
   *   The provided form structure wrapped in a collapsed fieldset.
   */
  public function checkoutInfoWrapper(Job $job, $form) {
    $label = $job->getTranslator()->label();
    $form += array(
      '#title' => t('@translator translation job information', array('@translator' => $label)),
      '#type' => 'fieldset',
      '#collapsible' => TRUE,
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function reviewForm($form, &$form_state, JobItem $item) {
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function reviewDataItemElement($form, &$form_state, $data_item_key, $parent_key, array $data_item, JobItem $item) {
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function reviewFormValidate($form, &$form_state, JobItem $item) {
    // Nothing to do here by default.
  }

  /**
   * {@inheritdoc}
   */
  public function reviewFormSubmit($form, &$form_state, JobItem $item) {
    // Nothing to do here by default.
  }

}
