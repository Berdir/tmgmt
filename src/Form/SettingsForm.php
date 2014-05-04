<?php

/**
 * @file
 * Contains \Drupal\tmgmjt\Controller\SettingsForm.
 */

namespace Drupal\tmgmt\Form;

use Drupal\Core\Form\ConfigFormBase;

/**
 * Configure tmgmt settings.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormID() {
    return 'tmgmt_ui_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, array &$form_state) {
    $config = $this->configFactory->get('tmgmt.settings');
    $form['workflow'] = array(
      '#type' => 'fieldset',
      '#title' => t('Workflow settings'),
    );
    $form['workflow']['tmgmt_quick_checkout'] = array(
      '#type' => 'checkbox',
      '#title' => t('Allow quick checkout'),
      '#description' => t("Enabling this will skip the checkout form and instead directly process the translation request in cases where there is only one translator available which doesn't provide any additional configuration options."),
      '#default_value' => $config->get('quick_checkout'),
    );
    $form['performance'] = array(
      '#type' => 'fieldset',
      '#title' => t('Performance settings'),
    );
    $form['performance']['tmgmt_purge_finished'] = array(
      '#type' => 'select',
      '#title' => t('Purge finished jobs'),
      '#description' => t('If configured, translation jobs that have been marked as finished will be purged after a given time. The translations itself will not be deleted.'),
      '#options' => array('_never' => t('Never'), '0' => t('Immediately'), '86400' => t('After 24 hours'), '604800' => t('After 7 days'), '2592000' => t('After 30 days'), '31536000' => t('After 365 days')),
      '#default_value' => $config->get('purge_finished'),
    );
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, array &$form_state) {
    $this->configFactory->get('tmgmt.settings')
      ->set('quick_checkout', $form_state['values']['tmgmt_quick_checkout'])
      ->set('purge_finished', $form_state['values']['tmgmt_purge_finished'])
      ->save();

    parent::submitForm($form, $form_state);
  }

}

