<?php

/**
 * @file
 * Contains \Drupal\tmgmt\SourcePluginUiBase.
 */

namespace Drupal\tmgmt;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\Component\Utility\SafeMarkup;

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
    $form += $this->overviewSearchFormPart($form, $form_state, $type);

    $form['#attached']['library'][] = 'tmgmt/admin';

    $form['items'] = array(
      '#type' => 'tableselect',
      '#header' => $this->overviewFormHeader($type),
      '#empty' => $this->t('No source items matching given criteria have been found.'),
    );

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

  /**
   * Builds search form for entity sources overview.
   *
   * @param array $form
   *   Drupal form array.
   * @param FormStateInterface $form_state
   *   Drupal form_state array.
   * @param string $type
   *   Entity type.
   *
   * @return array
   *   Drupal form array.
   */
  public function overviewSearchFormPart(array $form, FormStateInterface $form_state, $type) {
    // Add entity type and plugin_id value into form array
    // so that it is available in the form alter hook.
    $form_state->set('entity_type', $type);
    $form_state->set('plugin_id', $this->pluginId);

    // Add search form specific styling.
    $form['#attached']['library'][] = 'tmgmt/source_search_form';

    $form['search_wrapper'] = array(
      '#prefix' => '<div class="tmgmt-sources-wrapper">',
      '#suffix' => '</div>',
      '#weight' => -15,
    );
    $form['search_wrapper']['search'] = array(
      '#tree' => TRUE,
    );
    $form['search_wrapper']['search_submit'] = array(
      '#type' => 'submit',
      '#value' => t('Search'),
      '#weight' => 90,
    );
    $form['search_wrapper']['search_cancel'] = array(
      '#type' => 'submit',
      '#value' => t('Cancel'),
      '#weight' => 100,
    );

    return $form;
  }

  /**
   * Gets languages form header.
   *
   * @return array
   *   Array with the languages for the header.
   */
  protected function getLanguageHeader() {
    $languages = array();
    foreach (\Drupal::languageManager()->getLanguages() as $langcode => $language) {
      $languages['langcode-' . $langcode] = array(
        'data' => $language->getName(),
      );
    }

    return $languages;
  }

  /**
   * Performs redirect with search params appended to the uri.
   *
   * In case of triggering element is edit-search-submit it redirects to
   * current location with added query string containing submitted search form
   * values.
   *
   * @param array $form
   *   Drupal form array.
   * @param FormStateInterface $form_state
   *   Drupal form_state array.
   * @param $type
   *   Entity type.
   *
   * @return bool
   *   Returns TRUE, if redirect has been set.
   */
  public function overviewSearchFormRedirect(array $form, FormStateInterface $form_state, $type) {
    if ($form_state->getTriggeringElement()['#id'] == 'edit-search-cancel') {
      $form_state->setRedirect('tmgmt.source_overview', array('plugin' => $this->pluginId, 'item_type' => $type));
      return TRUE;
    }
    elseif ($form_state->getTriggeringElement()['#id'] == 'edit-search-submit') {
      $query = array();

      foreach ($form_state->getValue('search') as $key => $value) {
        $query[$key] = $value;
      }
      $form_state->setRedirect('tmgmt.source_overview', array('plugin' => $this->pluginId, 'item_type' => $type), array('query' => $query));
      return TRUE;
    }
    return FALSE;
  }

}
