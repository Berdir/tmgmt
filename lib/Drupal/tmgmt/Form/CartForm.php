<?php

/**
 * @file
 * Contains \Drupal\tmgmt\Controller\SourceOverviewForm.
 */

namespace Drupal\tmgmt\Form;

use Drupal\Core\Form\FormBase;
use Drupal\tmgmt\Entity\JobItem;

/**
 * Source overview form.
 */
class Cartform extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormID() {
    return 'tmgmt_cart_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, array &$form_state, $plugin = NULL, $item_type = NULL) {
    $languages = tmgmt_available_languages();
    $options = array();
    foreach (tmgmt_ui_cart_get()->getJobItemsFromCart() as $item) {
      $uri = $item->getSourceUri();
      $options[$item->tjiid] = array(
        $item->getSourceType(),
        (!empty($uri['path']) ? l($item->label(), $uri['path']) : $item->label()),
        isset($languages[$item->getSourceLangCode()]) ? $languages[$item->getSourceLangCode()] : t('Unknown'),
      );
    }

    $form['items'] = array(
      '#type' => 'tableselect',
      '#header' => array(t('Type'), t('Content'), t('Language')),
      '#empty' => t('There are no items in your cart.'),
      '#options' => $options,
    );

    $form['target_language'] = array(
      '#type' => 'select',
      '#title' => t('Request translation into language/s'),
      '#multiple' => TRUE,
      '#options' => $languages,
      '#description' => t('If the item\'s source language will be the same as the target language the item will be ignored.'),
    );

    $form['empty_cart'] = array(
      '#type' => 'submit',
      '#value' => t('Empty cart'),
      '#submit' => array(array($this, 'submitEmptyCart')),
    );

    $form['remove_selected'] = array(
      '#type' => 'submit',
      '#value' => t('Remove selected'),
      '#submit' => array(array($this, 'submitRemoveSelected')),
      '#validate' => array('tmgmt_ui_cart_source_overview_validate'),
    );

    $form['request_translation'] = array(
      '#type' => 'submit',
      '#value' => t('Request translation'),
      '#submit' => array(array($this, 'submitRequestTranslation')),
      '#validate' => array('tmgmt_ui_cart_source_overview_validate'),
    );
    return $form;

  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, array &$form_state) {

  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, array &$form_state) {

  }

  /**
   * Form submit callback to remove the selected items.
   */
  function submitRemoveSelected($form, &$form_state) {
    $job_item_ids = array_filter($form_state['values']['items']);
    tmgmt_ui_cart_get()->removeJobItems($job_item_ids);
    entity_delete_multiple('tmgmt_job_item', $job_item_ids);
    drupal_set_message(t('Job items were removed from the cart.'));
  }

  /**
   * Form submit callback to remove the selected items.
   */
  function submitEmptyCart($form, &$form_state) {
    entity_delete_multiple('tmgmt_job_item', array_keys(tmgmt_ui_cart_get()->getJobItemsFromCart()));
    tmgmt_ui_cart_get()->emptyCart();
    drupal_set_message(t('All job items were removed from the cart.'));
  }

  /**
   * Custom form submit callback for tmgmt_ui_cart_cart_form().
   */
  function submitRequestTranslation($form, &$form_state) {
    $target_languages = array_filter($form_state['values']['target_language']);

    $job_items_by_source_language = array();
    // Group the selected items by source language.
    foreach (tmgmt_job_item_load_multiple(array_filter($form_state['values']['items'])) as $job_item) {
      $job_items_by_source_language[$job_item->getSourceLangCode()][$job_item->tjiid] = $job_item;
    }

    $jobs = array();
    $remove_job_item_ids = array();
    // Loop over all target languages, create a job for each source and target
    // language combination add add the relevant job items to it.
    foreach ($target_languages as $target_language) {
      foreach ($job_items_by_source_language as $source_language => $job_items) {
        // Skip in case the source language is the same as the target language.
        if ($source_language == $target_language) {
          continue;
        }


        $job = tmgmt_job_create($source_language, $target_language, $this->currentUser()->id());
        $job_empty = TRUE;
        /** @var JobItem $job_item */
        foreach ($job_items as $id => $job_item) {
          try {
            // As the same item might be added to multiple jobs, we need to
            // re-create them and delete the old ones, after removing them from
            // the cart.
            $job->addItem($job_item->plugin, $job_item->item_type, $job_item->item_id);
            $remove_job_item_ids[$job_item->tjiid] = $job_item->tjiid;
            $job_empty = FALSE;
          }
          catch (Exception $e) {
            // If an item fails for one target language, then it is also going
            // to fail for others, so remove it from the array.
            unset($job_items_by_source_language[$source_language][$id]);
            drupal_set_message($e->getMessage(), 'error');
          }
        }

        if (!$job_empty) {
          $jobs[] = $job;
        }
      }
    }

    // Remove job items from the cart.
    if ($remove_job_item_ids) {
      tmgmt_ui_cart_get()->removeJobItems($remove_job_item_ids);
      entity_delete_multiple('tmgmt_job_item', $remove_job_item_ids);
    }

    // Start the checkout process if any jobs were created.
    if ($jobs) {
      tmgmt_ui_job_checkout_and_redirect($form_state, $jobs);
    }
  }

}

