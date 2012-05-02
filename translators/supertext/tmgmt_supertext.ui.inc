<?php

/**
 * @file
 * Provides the Supertext translation plugin controller.
 */

/**
 * Supertext translator plugin controller.
 */
class TMGMTSupertextTranslatorUIController extends TMGMTDefaultTranslatorUIController {

  /**
   * Overrides TMGMTDefaultTranslatorUIController::settingsForm().
   */
  public function pluginSettingsForm($form, &$form_state, TMGMTTranslator $translator, $busy = FALSE) {
    $plugin = tmgmt_translator_plugin_controller($this->pluginType);
    $form['api_username'] = array(
      '#type' => 'textfield',
      '#title' => t('Supertext email'),
      '#default_value' => $translator->getSetting('api_username', ''),
      '#description' => t("Please enter the email address of your Supertext account."),
    );
    $form['api_token'] = array(
      '#type' => 'textfield',
      '#title' => t('Supertext API Token'),
      '#default_value' => $translator->getSetting('api_token', ''),
      '#description' => t('Please enter your API Token (You can get it from !link).', array('!link' => l(t('Supertext Account'), 'https://www.supertext.ch/customer/accountsettings.aspx'))),

    );
    $form['currency'] = array(
      '#type' => 'select',
      '#title' => t('Your default currency'),
      '#options' => array(
        'EUR' => t('Euro'),
        'CHF' => t('Swiss Franc'),
      ),
      '#default_value' => $translator->getSetting('currency', ''),
      '#description' => t('In what currency do you want to order.'),
    );
    // Map drupal languages to languages that supertext can translate.
    // E.g. DE -> DE-CH
    // @TODO only make requests if nothing is set and add some caching.
    $languages = language_list();
    foreach ($languages as $language) {
      $mapping = $plugin->getMappedLanguages($language, $translator);
      if ($mapping) {
        if (count($mapping) == 1) {
          $form['language_mapping_' . $language->language] = array(
            '#type' => 'value',
            '#value' => array_shift(array_keys($mapping)),
          );
        }
        else {
          $form['language_mapping_' . $language->language] = array(
            '#type' => 'select',
            '#title' => t('Select your mapping for @language_name,', array('@language_name' => $language->name)),
            '#options' => $mapping,
            '#default_value' => $translator->getSetting('language_mapping_' . $language->language, ''),
            '#description' => t('We need a more exact definition of @language_name, there are different versions of it.', array('@language_name' => $language->name)),
          );
        }
      }

    }
    $form['use_sandbox'] = array(
      '#type' => 'checkbox',
      '#title' => t('Use Sandbox'),
      '#default_value' => $translator->getSetting('use_sandbox', TRUE),
    );
    // No true validation, we just display a warning.
    //
    // @todo Implement the pluginSettingsFormValidate() function when
    // http://drupal.org/node/1416372 is commited.
    $form['api_username']['#element_validate'][] = 'tmgmt_supertext_pluginsettingsform_validate_api_username';
    $form['api_token']['#element_validate'][] = 'tmgmt_supertext_pluginsettingsform_validate_api_token';

    return parent::pluginSettingsForm($form, $form_state, $translator, $busy);
  }

  /**
   * Overrides TMGMTDefaultTranslatorUIController::checkoutSettingsForm().
   */
  public function checkoutSettingsForm($form, &$form_state, TMGMTJob $job) {
    $plugin = tmgmt_translator_plugin_controller($this->pluginType);
    $object = $plugin->generateSupertextOrderObject($job);
    $translator = $job->getTranslator();
    $response = $plugin->supertextHttpRequest('translation/quote', $translator, $object);
    if ($response->code == "200") {
      $responsedata = json_decode($response->data);
      $settings['supertext-job'] = array(
        '#type' => 'value',
      );
      if (!$translator->getSetting('api_username')) {
        $account_status = t("There is no API email set in the Supertext Translation Form. You still can see the quote, but will not be able to submit a job.");
      }
      elseif (!$translator->getSetting('api_token')) {
        $account_status = t("There is no API token set in the Supertext Translation Form. You still can see the quote, but will not be able to submit a job.");
      }
      elseif (!$responsedata->AccountValid) {
        $account_status = t("The entered API email and token are not valid. You still can see the quote, but will not be able to submit a job.");
      }
      if (isset($account_status)) {
        $settings['account-status'] = array(
          '#type' => 'item',
          '#title' => t('Account Status'),
          '#markup' => $account_status,
          '#prefix' => '<div class="messages error">',
          '#suffix' => "</div>",
        );
      }
      if (isset($responsedata->Options)) {
        $defaultradio = TRUE;
        foreach ($responsedata->Options as $option_item) {
          $settings[$option_item->OrderTypeId . "_markup"] = array(
            '#type' => 'item',
            '#title' => $option_item->Name,
            '#markup' => $option_item->Description,
          );
          foreach ($option_item->DeliveryOptions as $delivery_key => $delivery_item) {
            $name = format_date(strtotime($delivery_item->DeliveryDate)) . ', Price ' . $delivery_item->Price . " " . $responsedata->Currency;
            $settings[$option_item->OrderTypeId . ":" . $delivery_item->DeliveryId] = array(
              '#type' => 'radio',
              '#title' => $name,
              '#return_value' => $option_item->OrderTypeId . ":" . $delivery_item->DeliveryId,
              '#default_value' => $defaultradio,
              '#attributes' => array('name' => array('settings[supertext-job]')),
            );
            // Just set the first option to true, whatever it is.
            $defaultradio = FALSE;
          }
        }
      }
      return $settings;
    }
    else {
      drupal_set_message(t("Could not get quote from Supertext. Error: @error", array('@error' => $response->error)), 'error');
      return array();
    }
  }
}
