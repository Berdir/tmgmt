<?php

/**
 * @file
 * Contains \Drupal\tmgmt_entity\EntitySourcePluginUiBase.
 */

namespace Drupal\tmgmt_entity;

use Drupal\Core\Url;
use Drupal\tmgmt\SourcePluginUiBase;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Abstract entity ui controller class for source plugin that provides
 * getEntity() method to retrieve list of entities of specific type. It also
 * allows to implement alter hook to alter the entity query for a specific type.
 *
 * @ingroup tmgmt_source
 */
abstract class EntitySourcePluginUiBase extends SourcePluginUiBase {

  /**
   * Entity source list items limit.
   *
   * @var int
   */
  public $pagerLimit = 25;

  /**
   * Gets entities data of provided type needed to build overview form list.
   *
   * @param $type
   *   Entity type for which to get list of entities.
   * @param array $property_conditions
   *   Array of key => $value pairs passed into
   *   tmgmt_entity_get_translatable_entities() as the second parameter.
   *
   * @return array
   *   Array of entities.
   */
  public function getEntitiesTranslationData($type, $property_conditions = array()) {

    $return_value = array();
    $entities = tmgmt_entity_get_translatable_entities($type, $property_conditions, TRUE);

    $bundles = tmgmt_entity_get_translatable_bundles($type);

    // For retrieved entities add translation specific data.
    foreach ($entities as $entity) {
      // This occurs on user entity type.
      if (!$entity->id()) {
        continue;
      }

      // Get existing translations and current job items for the entity
      // to determine translation statuses
      $translations = $entity->getTranslationLanguages();
      $source_lang = $entity->language()->id;
      $current_job_items = tmgmt_job_item_load_latest('entity', $type, $entity->id(), $source_lang);

      // Load basic entity data.
      $return_value[$entity->id()] = array(
        'entity_type' => $type,
        'entity_id' => $entity->id(),
        'entity_label' => $entity->label(),
        'entity_url' => $entity->url(),
      );

      if (count($bundles) > 1) {
        $return_value[$entity->id()]['bundle'] = isset($bundles[$entity->bundle()]) ? $bundles[$entity->bundle()] : t('Unknown');
      }

      // Load entity translation specific data.
      foreach (language_list() as $langcode => $language) {

        $translation_status = 'current';

        if ($langcode == $source_lang) {
          $translation_status = 'original';
        }
        elseif (!isset($translations[$langcode])) {
          $translation_status = 'missing';
        }

        elseif (!empty($translations->data[$langcode]['translate'])) {
          $translation_status = 'outofdate';
        }

        $return_value[$entity->id()]['current_job_items'][$langcode] = isset($current_job_items[$langcode]) ? $current_job_items[$langcode] : NULL;
        $return_value[$entity->id()]['translation_statuses'][$langcode] = $translation_status;
      }
    }

    return $return_value;
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
  public function overviewSearchFormPart($form, &$form_state, $type) {

    // Add search form specific styling.
    $form['#attached']['css'][] = drupal_get_path('module', 'tmgmt_entity') . '/css/tmgmt_entity.admin.entity_source_search_form.css';

    // Add entity type value into form array so that it is available in
    // the form alter hook.
    $form_state['entity_type'] = $type;

    $form['search_wrapper'] = array(
      '#prefix' => '<div class="tmgmt-sources-wrapper tmgmt-entity-sources-wrapper">',
      '#suffix' => '</div>',
      '#weight' => -15,
    );
    $form['search_wrapper']['search'] = array(
      '#tree' => TRUE,
    );

    $form['search_wrapper']['search_submit'] = array(
      '#type' => 'submit',
      '#value' => t('Search'),
      '#weight' => 10,
    );
    $form['search_wrapper']['search_cancel'] = array(
      '#type' => 'submit',
      '#value' => t('Cancel'),
      '#weight' => 11,
    );

    $entity_type = \Drupal::entityManager()->getDefinition($type);

    $label_key = $entity_type->getKey('label');

    if (!empty($label_key)) {
      $form['search_wrapper']['search'][$label_key] = array(
        '#type' => 'textfield',
        '#title' => t('@entity_name title', array('@entity_name' => $entity_type->getLabel())),
        '#size' => 25,
        '#default_value' => isset($_GET[$label_key]) ? $_GET[$label_key] : NULL,
      );
    }

    $language_options = array();
    foreach (language_list() as $langcode => $language) {
      $language_options[$langcode] = $language->name;
    }

    $form['search_wrapper']['search']['langcode'] = array(
      '#type' => 'select',
      '#title' => t('Source Language'),
      '#options' => $language_options,
      '#empty_option' => t('All'),
      '#default_value' => isset($_GET['langcode']) ? $_GET['langcode'] : NULL,
    );

    $bundle_key = $entity_type->getKey('bundle');
    $bundle_options = tmgmt_entity_get_translatable_bundles($type);

    if (count($bundle_options) > 1) {
      $form['search_wrapper']['search'][$bundle_key] = array(
        '#type' => 'select',
        '#title' => t('@entity_name type', array('@entity_name' => $entity_type->getLabel())),
        '#options' => $bundle_options,
        '#empty_option' => t('All'),
        '#default_value' => isset($_GET[$bundle_key]) ? $_GET[$bundle_key] : NULL,
      );
    }
    // In case entity translation is not enabled for any of bundles
    // display appropriate message.
    elseif (count($bundle_options) == 0) {
      drupal_set_message(t('Entity translation is not enabled for any of existing content types. To use this functionality go to Content types administration and enable entity translation for desired content types.'), 'warning');
      unset($form['search_wrapper']);
      return $form;
    }

    $options = array();
    foreach (language_list() as $langcode => $language) {
      $options[$langcode] = $language->name;
    }

    $form['search_wrapper']['search']['target_language'] = array(
      '#type' => 'select',
      '#title' => t('Target language'),
      '#options' => $options,
      '#empty_option' => t('Any'),
      '#default_value' => isset($_GET['target_language']) ? $_GET['target_language'] : NULL,
    );
    $form['search_wrapper']['search']['target_status'] = array(
      '#type' => 'select',
      '#title' => t('Target status'),
      '#options' => array(
        'untranslated_or_outdated' => t('Untranslated or outdated'),
        'untranslated' => t('Untranslated'),
        'outdated' => t('Outdated'),
      ),
      '#default_value' => isset($_GET['target_status']) ? $_GET['target_status'] : NULL,
      '#states' => array(
        'invisible' => array(
          ':input[name="search[target_language]"]' => array('value' => ''),
        ),
      ),
    );

    return $form;
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
  public function overviewSearchFormRedirect($form, &$form_state, $type) {
    if ($form_state['triggering_element']['#id'] == 'edit-search-cancel') {
      $form_state['redirect_route'] = new Url('tmgmt.source_overview', array('plugin' => 'entity', 'item_type' => $type));
      return TRUE;
    }
    elseif ($form_state['triggering_element']['#id'] == 'edit-search-submit') {
      $query = array();

      foreach ($form_state['values']['search'] as $key => $value) {
        $query[$key] = $value;
      }
      $form_state['redirect_route'] = new Url('tmgmt.source_overview', array('plugin' => 'entity', 'item_type' => $type), array('query' => $query));
      return TRUE;
    }
  }

}
