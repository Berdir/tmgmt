<?php

/**
 * @file
 * Contains Drupal\tmgmt_test\TestTranslatorUIController.
 */

namespace Drupal\tmgmt_test;

use Drupal\Core\Form\FormStateInterface;
use Drupal\tmgmt\JobInterface;
use Drupal\tmgmt\JobItemInterface;
use Drupal\tmgmt\TranslatorInterface;
use Drupal\tmgmt\TranslatorPluginUiBase;
use Drupal\tmgmt\Entity\Job;
use Drupal\tmgmt\Entity\JobItem;
use Drupal\tmgmt\Entity\Translator;

/**
 * Test translator UI controller.
 */
class TestTranslatorUi extends TranslatorPluginUiBase {

  /**
   * {@inheritdoc}
   */
  public function pluginSettingsForm(array $form, FormStateInterface $form_state, TranslatorInterface $translator, $busy = FALSE) {
    $form['expose_settings'] = array(
      '#type' => 'checkbox',
      '#title' => t('Display settings'),
      '#default_value' => TRUE,
    );

    $form['action'] = array(
      '#type' => 'select',
      '#title' => t('Default action'),
      '#options' => array(
        'translate' => t('Translate'),
        'submit' => t('Submit'),
        'reject' => t('Reject'),
        'fail' => t('Fail'),
        'not_translatable' => t('Not translatable'),
      ),
    );
    return parent::pluginSettingsForm($form, $form_state, $translator, $busy);
  }

  /**
   * {@inheritdoc}
   */
  public function checkoutSettingsForm(array $form, FormStateInterface $form_state, JobInterface $job) {
    if ($job->getTranslator()->getSetting('expose_settings')) {
      $form['action'] = array(
        '#type' => 'select',
        '#title' => t('Action'),
        '#options' => array(
          'translate' => t('Translate'),
          'submit' => t('Submit'),
          'reject' => t('Reject'),
          'fail' => t('Fail'),
          'not_translatable' => t('Not translatable'),
        ),
        '#default_value' => $job->getTranslator()->getSetting('action'),
      );
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function reviewDataItemElement(array $form, FormStateInterface $form_state, $data_item_key, $parent_key, array $data_item, JobItemInterface $item) {
    $form['below'] = array(
      '#markup' => t('Testing output of review data item element @key from the testing translator.', array('@key' => $data_item_key))
    );

    return $form;
  }

}
