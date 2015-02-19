<?php

/**
 * @file
 * Provides the I18nString source controller.
 */

namespace Drupal\tmgmt_locale;

use Drupal\Component\Utility\String;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\tmgmt\SourcePluginUiBase;

/**
 * Class TMGMTI18nStringDefaultSourceUIController
 *
 * UI Controller fo i18n strings translation jobs.
 */
class LocaleSourcePluginUi extends SourcePluginUiBase {

  /**
   * Gets locale strings.
   *
   * @param string $textgroup
   *   The locale textgroup.
   * @param string $search_label
   *   Label to search for.
   * @param string $missing_target_language
   *   Missing translation language.
   *
   * @return array
   *   List of i18n strings data.
   */
  function getStrings($search_label = NULL, $missing_target_language = NULL) {
    $langcodes = array_keys( \Drupal::languageManager()->getLanguages());
    $languages = array_combine($langcodes, $langcodes);
    $select = db_select('locales_source', 'ls')
      ->fields('ls', array('lid', 'source'));

    if (!empty($search_label)) {
      $select->condition('ls.source', "%$search_label%", 'LIKE');
    }
    if (!empty($missing_target_language) && in_array($missing_target_language, $languages)) {
      $select->isNull("lt_$missing_target_language.language");
    }

    // Join locale targets for each language.
    // We want all joined fields to be named as langcodes, but langcodes could
    // contain hyphens in their names, which is not allowed by the most database
    // engines. So we create a langcode-to-filed_alias map, and rename fields
    // later.
    $langcode_to_filed_alias_map = array();
    foreach ($languages as $langcode) {
      $table_alias = $select->leftJoin('locales_target', db_escape_field("lt_$langcode"), "ls.lid = %alias.lid AND %alias.language = '$langcode'");
      $langcode_to_filed_alias_map[$langcode] = $select->addField($table_alias, 'language');
    }
    unset($field_alias);

    $rows = $select
      ->extend('Drupal\Core\Database\Query\PagerSelectExtender')
      ->limit( \Drupal::config('tmgmt.settings')->get('source_list_limit', 20))
      ->execute()
      ->fetchAll();
    foreach ($rows as $row) {
      foreach ($langcode_to_filed_alias_map as $langcode => $field_alias) {
        $row->{$langcode} = $row->{$field_alias};
        unset($row->{$field_alias});
      }
    }
    unset($row);

    return $rows;
  }

  /**
   * Gets overview form header.
   *
   * @return array
   *   Header array definition as expected by theme_tablesort().
   */
  public function overviewFormHeader() {
    $languages = array();
    foreach ($this->getLanguages() as $langcode => $language) {
      $languages['langcode-' . $langcode] = array(
        'data' => String::checkPlain($language->getName()),
      );
    }

    $header = array(
        'source' => array('data' => t('Source text')),
      ) + $languages;

    return $header;
  }

  /**
   * Implements TMGMTSourceUIControllerInterface::overviewForm().
   */
  public function overviewForm(array $form, FormStateInterface $form_state, $type) {
    $form += $this->overviewSearchFormPart($form, $form_state, $type);

    $form['items'] = array(
      '#type' => 'tableselect',
      '#header' => $this->overviewFormHeader($type),
      '#empty' => t('No strings matching given criteria have been found.')
    );

    $form['#attached']['library'][] = 'tmgmt/admin';

    $search_data = $this->getSearchFormSubmittedParams();

    $strings = $this->getStrings($search_data['label'], $search_data['missing_target_language']);

    foreach ($this->getTranslationData($strings, $type) as $id => $data) {
      $form['items']['#options'][$id] = $this->overviewRow($type, $data);
    }

    $form['pager'] = array('#theme' => 'pager');

    return $form;
  }

  /**
   * Helper function to create translation data list for the sources page list.
   *
   * @param array $strings
   *   Result of the search query returned by tmgmt_i18n_string_get_strings().
   * @param string $type
   *   I18n object type.
   *
   * @return array
   *   Structured array with translation data.
   */
  protected function getTranslationData($strings, $type) {
    $objects = array();
    // Source language of locale strings is always english.
    $source_language = 'en';

    foreach ($strings as $string) {
      $id = $string->lid;

      // Get existing translations and current job items for the entity
      // to determine translation statuses
      $current_job_items = tmgmt_job_item_load_latest('locale', $type, $id, $source_language);

      $objects[$id] = array(
        'id' => $id,
        'object' => $string
      );
      // Load entity translation specific data.
      foreach ($this->getLanguages() as $langcode => $language) {

        $translation_status = 'current';

        if ($langcode == $source_language) {
          $translation_status = 'original';
        }
        elseif ($string->{$langcode} === NULL) {
          $translation_status = 'missing';
        }

        $objects[$id]['current_job_items'][$langcode] = isset($current_job_items[$langcode]) ? $current_job_items[$langcode] : NULL;
        $objects[$id]['translation_statuses'][$langcode] = $translation_status;
      }
    }

    return $objects;
  }

  /**
   * Builds search form for entity sources overview.
   *
   * @param array $form
   *   Drupal form array.
   * @param $form_state
   *   Drupal form_state array.
   * @param $type
   *   Entity type.
   *
   * @return array
   *   Drupal form array.
   */
  public function overviewSearchFormPart(array $form, FormStateInterface $form_state, $type) {

    $options = array();
    foreach (language_list() as $langcode => $language) {
      $options[$langcode] = $language->getName();
    }

    $default_values = $this->getSearchFormSubmittedParams();

    $form['search_wrapper'] = array(
      '#prefix' => '<div class="tmgmt-sources-wrapper tmgmt-i18n_string-sources-wrapper">',
      '#suffix' => '</div>',
      '#weight' => -15,
    );
    $form['search_wrapper']['search'] = array(
      '#tree' => TRUE,
    );
    $form['search_wrapper']['search']['label'] = array(
      '#type' => 'textfield',
      '#title' => t('Source text'),
      '#default_value' => isset($default_values['label']) ? $default_values['label'] : NULL,
    );

    // Unset English as it is the source language for all locale strings.
    unset($options['en']);

    $form['search_wrapper']['search']['missing_target_language'] = array(
      '#type' => 'select',
      '#title' => t('Not translated to'),
      '#options' => $options,
      '#empty_option' => '--',
      '#default_value' => isset($default_values['missing_target_language']) ? $default_values['missing_target_language'] : NULL,
    );
    $form['search_wrapper']['search_submit'] = array(
      '#type' => 'submit',
      '#value' => t('Search'),
    );

    return $form;
  }

  /**
   * Gets submitted search params.
   *
   * @return array
   */
  public function getSearchFormSubmittedParams() {
    $params = array(
      'label' => NULL,
      'missing_target_language' => NULL,
    );

    if (isset($_GET['label'])) {
      $params['label'] = $_GET['label'];
    }
    if (isset($_GET['missing_target_language'])) {
      $params['missing_target_language'] = $_GET['missing_target_language'];
    }

    return $params;
  }

  /**
   * Builds a table row for overview form.
   *
   * @param string $type
   *   i18n type.
   * @param array $data
   *   Data needed to build the list row.
   *
   * @return array
   */
  public function overviewRow($type, $data) {
    // Set the default item key, assume it's the first.
    $source = $data['object'];

    $row = array(
      'id' => $data['id'],
      'source' => String::checkPlain($source->source),
    );


    foreach (language_list() as $langcode => $language) {
      $array = array(
        '#theme' => 'tmgmt_translation_language_status_single',
        '#translation_status' => $data['translation_statuses'][$langcode],
        '#job_item' => isset($data['current_job_items'][$langcode]) ? $data['current_job_items'][$langcode] : NULL,
      );
      $row['langcode-' . $langcode] = drupal_render($array);
    }

    return $row;
  }

  /**
   * Implements TMGMTSourceUIControllerInterface::overviewFormSubmit().
   */
  public function overviewFormSubmit(array $form, FormStateInterface $form_state, $type) {
    // Handle search redirect.
    if ($this->overviewSearchFormRedirect($form, $form_state, $type)) {
      return;
    }
    $items = array_filter($form_state->getValue('items'));
    $type = $form_state->get('item_type');

    $source_lang = 'en';

    // Create only single job for all items as the source language is just
    // the same for all.
    $job = tmgmt_job_create($source_lang, NULL, \Drupal::currentUser()->id());

    // Loop through entities and create individual jobs for each source language.
    foreach ($items as $item) {
      $job->addItem('locale', $type, $item);
    }

    $url = $job->urlInfo();
    $url->setOption('destination', Url::fromRoute('<current>')->getInternalPath());
    $form_state->setRedirectUrl($url);
    drupal_set_message(t('One job needs to be checked out.'));
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
   * @param $form_state
   *   Drupal form_state array.
   * @param $type
   *   Entity type.
   */
  public function overviewSearchFormRedirect(array $form, FormStateInterface $form_state, $type) {
    if ($form_state->getTriggeringElement()['#id'] == 'edit-search-submit') {

      $query = array();

      foreach ($form_state->getValue('search') as $key => $value) {
        $query[$key] = $value;
      }

      $form_state->setRedirect('tmgmt.source_overview', array('plugin' => 'locale', 'item_type' => $type), array('query' => $query));
      return TRUE;
    }
  }

  /**
   * Returns languages, ensures that english is first.
   *
   * @return array
   */
  protected function getLanguages() {
    // Make sure that en is the first language.
    $languages = \Drupal::languageManager()->getLanguages();
    $en = $languages['en'];
    unset($languages['en']);
    $languages = array('en' => $en) + $languages;
    return $languages;
  }
}
