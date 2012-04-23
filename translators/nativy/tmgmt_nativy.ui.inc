<?php

/**
 * @file
 * Provides the Nativy Translator ui controller.
 */

/**
 * Nativity translator ui controller.
 */
class TMGMTNativyTranslatorUIController extends TMGMTDefaultTranslatorUIController {

  /**
   * Overrides TMGMTDefaultTranslatorUIController::pluginSettingsForm().
   */
  public function pluginSettingsForm($form, &$form_state, TMGMTTranslator $translator, $busy = FALSE) {
    $form['api_key'] = array(
      '#type' => 'textfield',
      '#title' => t('API key'),
      '#default_value' => $translator->getSetting('api_key'),
      '#description' => t('Please enter your Nativy connect API key.'),
    );
    $form['private_key'] = array(
      '#type' => 'textfield',
      '#title' => t('Private key'),
      '#default_value' => $translator->getSetting('private_key'),
      '#description' => t('Please enter your Nativy Private key.'),
    );
    $form['use_test'] = array(
      '#type' => 'checkbox',
      '#title' => t('Use the test environment'),
      '#default_value' => $translator->getSetting('use_test'),
      '#description' => t('Check to use the testing environment.'),
    );
    return parent::pluginSettingsForm($form, $form_state, $translator, $busy);
  }

  protected function getJobSetting(TMGMTJob $job, $setting, $default = '') {
    return isset($job->settings[$setting]) ? $job->settings[$setting] : $default;
  }

  /**
   * Defines plugin job settings form.
   *
   * @param $form array
   * @param $form_state array
   * @return array
   *   Settings form.
   */
  public function checkoutSettingsForm($form, &$form_state, TMGMTJob $job) {

    $settings['requested'] = array(
      '#type' => 'value',
      '#value' => TRUE,
    );

    $settings['short_description'] = array(
      '#type' => 'textfield',
      '#title' => t('Short description'),
      '#default_value' => $this->getJobSetting($job, 'short_description'),
    );

    $settings['long_description'] = array(
      '#type' => 'textarea',
      '#title' => t('Long description'),
      '#default_value' => $this->getJobSetting($job, 'long_description'),
    );

    $settings['correction_required'] = array(
      '#type' => 'checkbox',
      '#title' => t('Require a corrector'),
      '#description' => t('If checked, a corrector will be assigned to the order. A corrector proofreads the translation of the translator and makes suggestions to him if there is something incorrect with the translation.'),
      '#default_value' => $this->getJobSetting($job, 'correction_required', FALSE),
    );

    $settings['request_order'] = array(
      '#type' => 'submit',
      '#value' => t('Request offer'),
      '#valudate' => array('tmgmt_job_form_validate'),
      '#submit' => array('tmgmt_nativy_job_settings_submit'),
    );

    if ($this->getJobSetting($job, 'requested', FALSE)) {
      $response = $job->getTranslatorController()->getOrder($job);

      if (empty($response)) {
        drupal_set_message(t('Failed to retrieve an offer from Nativy.'), 'error');
        return;
      }

      $options = array();
      foreach ($response as $offer) {
        $options[$offer->rank] = $offer->translator_firstname;
      }

      $settings['rank'] = array(
        '#type' => 'radios',
        '#title' => t('Rank'),
        '#options' => $options,
        '#required' => TRUE,
      );

      foreach ($response as $offer) {
        $details = array(
          '#type' => 'fieldset',
          '#title' => $offer->translator_firstname,
          '#states' => array(
            'visible' => array(
              'input[name="settings[rank]"]' => array('value' => $offer->rank),
            ),
          ),
        );

        $details['orderId'] = array(
          '#type' => 'value',
          '#value' => $offer->orderid,
        );

        $details['image'] = array(
          '#theme' => 'image',
          '#path' => $offer->translator_picture_link,
        );

        $details['price'] = array(
          '#type' => 'item',
          '#title' => t('Price'),
          '#markup' => $offer->price_sum_gross,
        );

        $details['payment_link'] = array(
          '#type' => 'value',
          '#value' => $offer->payment_link,
        );

        $settings['rank_details'][$offer->rank] = $details;
      }
    }
    return $settings;
  }

  /**
   * Overrides TMGMTDefaultTranslatorUIController::pluginSettingsForm().
   */
  public function checkoutInfo(TMGMTJob $job) {
    $label = $job->getTranslator()->label();
    $form['#title'] = t('@translator translation job information', array('@translator' => $label));
    $form['#type'] = 'fieldset';

    if ($order = $job->getTranslatorController()->nativyRetrieveOrder($job)) {
      $status = t('Unknown status');
      switch ($order->status) {
        case 'orders_status_open':
          $status = t('Open');
          break;
        case 'orders_status_confirmationpending':
          $status = t('Confirmation pending');
          break;
        case 'orders_status_inprogress':
          $status = t('In progress');
          break;
        case 'orders_status_translationcomplete':
          $status = t('Complete');
          break;
        case 'orders_status_canceled':
          $status = t('Canceled');
          break;
      }

      $form['status'] = array(
        '#type' => 'item',
        '#title' => t('Status'),
        '#markup' => $status,
      );
    }
    else {
      $form['status'] = array(
        '#type' => 'item',
        '#title' => t('Status'),
        '#markup' => t('Unable to retrieve order from Nativy.'),
      );
    }

    return $form;
  }

}

