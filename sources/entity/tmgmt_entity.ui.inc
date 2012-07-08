<?php

/**
 * Abstract entity ui controller class for source plugin that provides
 * getEntity() method to retrieve list of entities of specific type. It also
 * allows to implement alter hook to alter the entity query for a specific type.
 *
 * @ingroup tmgmt_source
 */
abstract class TMGMTEntityDefaultSourceUIController extends TMGMTDefaultSourceUIController {

  /**
   * Entity source list items limit.
   *
   * @var int
   */
  public $pagerLimit = 20;

  /**
   * Gets entities data of provided type needed to build overview form list.
   *
   * @param $type
   *   Entity type for which to get list of entities.
   *
   * @return array
   *   Array of entities.
   */
  public function getEntitiesTranslationData($type) {

    // Init query basics.
    $translation_entity_query = new EntityFieldQuery();
    $translation_entity_query->entityCondition('entity_type', $type);
    $translation_entity_query->pager($this->pagerLimit);

    // Collect search params and add query conditions.
    foreach ($this->getSearchFormSubmittedParams() as $param_name => $param_value) {
      $translation_entity_query->propertyCondition($param_name, $param_value);
    }

    // Allow to alter the query for a specific entity type.
    drupal_alter('tmgmt_entity_' . $type . '_list_query', $translation_entity_query);

    $result = $translation_entity_query->execute();
    $entities = array();
    $return_value = array();

    if (isset($result[$type])) {
      $entity_ids = array_keys($result[$type]);
      $entities = entity_load($type, $entity_ids);
    }

    // For retrieved entities add translation specific data.
    foreach ($entities as $entity) {

      $entity_id = entity_id($type, $entity);
      $entity_uri = entity_uri($type, $entity);

      // This occurs on user entity type.
      if (empty($entity_id)) {
        continue;
      }

      /**
       * @var EntityTranslationDefaultHandler $handler
       */
      $handler = entity_translation_get_handler($type, $entity);

      // Get existing translations and current job items for the entity
      // to determine translation statuses
      $translations = $handler->getTranslations();
      $source_lang = $translations->original;
      $current_job_items = tmgmt_job_item_load_latest('entity', $type, $entity_id, $source_lang);

      // Load basic entity data.
      $return_value[$entity_id] = array(
        'entity_id' => $entity_id,
        // Run check_plain in case entity label is not sanitized.
        'entity_label' => check_plain(entity_label($type, $entity)),
        'entity_uri' => $entity_uri['path'],
      );

      // Load entity translation specific data.
      foreach (language_list() as $langcode => $language) {

        $translation_status = 'current';

        if ($langcode == $source_lang) {
          $translation_status = 'original';
        }
        elseif (!isset($translations->data[$langcode])) {
          $translation_status = 'missing';
        }
        elseif (!$translations->data[$langcode]) {
          $translation_status = 'outofdate';
        }

        $return_value[$entity_id]['current_job_items'][$langcode] = isset($current_job_items[$langcode]) ? $current_job_items[$langcode]: NULL;
        $return_value[$entity_id]['translation_statuses'][$langcode] = $translation_status;
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
    drupal_add_css(drupal_get_path('module', 'tmgmt_entity') . '/css/tmgmt_entity.admin.entity_source_search_form.css');

    $options = array();
    foreach (language_list() as $langcode => $language) {
      $options[$langcode] = $language->name;
    }

    $default_values = $this->getSearchFormSubmittedParams();

    $form['search_wrapper'] = array(
      '#prefix' => '<div class="tmgmt-entity-sources-wrapper">',
      '#suffix' => '</div>',
    );
    $form['search_wrapper']['search'] = array(
      '#tree' => TRUE,
    );
    $form['search_wrapper']['search']['language'] = array(
      '#type' => 'select',
      '#title' => t('Source Language'),
      '#options' => $options,
      '#empty_option' => t('All'),
      '#default_value' => isset($default_values['language']) ? $default_values['language'] : NULL,
    );
    $form['search_wrapper']['search_submit'] = array(
      '#type' => 'submit',
      '#value' => t('Search'),
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
    if ($form_state['triggering_element']['#id'] == 'edit-search-submit') {

      $query = array();

      foreach ($form_state['values']['search'] as $key => $value) {
        $query[$key] = $value;
      }

      drupal_goto($_GET['q'], array('query' => $query));
    }
  }

  /**
   * Gets submitted search params.
   *
   * @return array
   */
  public function getSearchFormSubmittedParams() {
    $params = array();

    if (isset($_GET['language'])) {
      $params['language'] = $_GET['language'];
    }

    return $params;
  }
}
