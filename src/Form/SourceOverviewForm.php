<?php

/**
 * @file
 * Contains \Drupal\tmgmt\Controller\SourceOverviewForm.
 */

namespace Drupal\tmgmt\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Source overview form.
 */
class SourceOverviewForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormID() {
    return 'tmgmt_overview_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $plugin = NULL, $item_type = NULL) {
    $source = \Drupal::service('plugin.manager.tmgmt.source')->createInstance($plugin);
    $definition = \Drupal::service('plugin.manager.tmgmt.source')->getDefinition($plugin);

    $form['#title'] = $this->t('@type overview (@plugin)', array('@type' => $source->getItemTypeLabel($item_type), '@plugin' => $definition['label']));
    $form['actions'] = array(
      '#type' => 'details',
      '#title' => t('Operations'),
      '#open' => TRUE,
      '#weight' => -10,
      '#attributes' => array('class' => array('tmgmt-source-operations-wrapper'))
    );
    tmgmt_ui_add_cart_form($form['actions'], $form_state, $plugin, $item_type);
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Request translation'),
    );

    $source_ui = \Drupal::service('plugin.manager.tmgmt.source')->createUIInstance($plugin);
    $form_state->set('plugin', $plugin);
    $form_state->set('item_type', $item_type);
    return $source_ui->overviewForm($form, $form_state, $item_type);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    /*if (!$form_state->getValue('values')) {
      $form_state->setErrorByName('items', t("You didn't select any source objects"));
    }*/
    list($plugin, $item_type) = $form_state->getBuildInfo()['args'];
    // Execute the validation method on the source plugin controller.
    $source_ui = \Drupal::service('plugin.manager.tmgmt.source')->createUIInstance($plugin);
    $source_ui->overviewFormValidate($form, $form_state, $item_type);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    list($plugin, $item_type) = $form_state->getBuildInfo()['args'];
    // Execute the submit method on the source plugin controller.
    $source_ui = \Drupal::service('plugin.manager.tmgmt.source')->createUIInstance($plugin);
    $source_ui->overviewFormSubmit($form, $form_state, $item_type);
  }

}

