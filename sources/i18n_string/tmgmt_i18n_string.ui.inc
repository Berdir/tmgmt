<?php

/**
 * @file
 * Provides the I18nString source controller.
 */

class TMGMTI18nStringDefaultSourceUIController extends TMGMTDefaultSourceUIController {
  /**
   * Gets overview form header.
   *
   * @return array
   *   Header array definition as expected by theme_tablesort().
   */
  public function overviewFormHeader() {
    $languages = array();
    foreach (language_list() as $langcode => $language) {
      $languages['langcode-' . $langcode] = array(
        'data' => check_plain($language->name),
      );
    }

    $header = array(
      'title' => array('data' => t('Label (in source language)')),
      'type' => array('data' => t('Type')),
    ) + $languages;

    return $header;
  }

  public function overviewForm($form, &$form_state, $type) {
    $form += $this->overviewSearchFormPart($form, $form_state, $type);

    $form['items'] = array(
      '#type' => 'tableselect',
      '#header' => $this->overviewFormHeader($type),
      '#empty' => t('No strings matching given criteria have been found.')
    );

    foreach ($this->getTranslationData($type) as $id => $data) {
      $form['items']['#options'][$id] = $this->overviewRow($data);
    }

    $form['pager'] = array('#markup' => theme('pager', array('tags' => NULL)));

    return $form;
  }

  public function getTranslationData($type) {
    $objects = array();
    $source_language = variable_get_value('i18n_string_source_language');

    $translations = tmgmt_i18n_string_select_query($type);
    $results = $translations['query']->execute()->fetchAll();

    foreach ($results as $object) {
      $type = tmgmt_i18n_string_source_get_object_wrapper($object->textgroup, $object->type);
      $wrapper = i18n_get_object($type, $object->objectid);

      if ($wrapper instanceof i18n_string_object_wrapper) {
        $id = implode(':', array($object->textgroup, $object->type, $object->objectid));

        // Get existing translations and current job items for the entity
        // to determine translation statuses

        //      $translations = $handler->getTranslations();
        //      $source_lang = $translations->original;
        $current_job_items = tmgmt_job_item_load_latest('i18n_string', 'i18n_string', $id, $source_language);

        $objects[$id] = array(
          'id' => $id,
          'object' => $wrapper->get_strings(array('empty' => TRUE)),
        );
        // Load entity translation specific data.
        foreach (language_list() as $langcode => $language) {

          $translation_status = 'current';

          if ($langcode == $source_language) {
            $translation_status = 'original';
          }
          elseif (!isset($translations->data[$langcode])) {
            $translation_status = 'missing';
          }
          elseif (!$translations->data[$langcode]) {
            $translation_status = 'outofdate';
          }

          $objects[$id]['current_job_items'][$langcode] = isset($current_job_items[$langcode]) ? $current_job_items[$langcode] : NULL;
          $objects[$id]['translation_statuses'][$langcode] = $translation_status;
        }
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
  public function overviewSearchFormPart($form, &$form_state, $type) {

    // Add search form specific styling.
    // drupal_add_css(drupal_get_path('module', 'tmgmt_entity') . '/css/tmgmt_entity.admin.entity_source_search_form.css');

    $options = array();
    foreach (language_list() as $langcode => $language) {
      $options[$langcode] = $language->name;
    }

    $default_values = $this->getSearchFormSubmittedParams();

    $form['search_wrapper'] = array(
      '#prefix' => '<div class="tmgmt-i18n_string-sources-wrapper">',
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
    // @todo: implement dynamic types.
    $form['search_wrapper']['search']['type'] = array(
      '#type' => 'select',
      '#title' => t('Type'),
      '#options' => array(
        'taxonomy_term' => 'Taxonomy',
        'view' => 'View',
      ),
      '#empty_option' => t('All'),
//      '#default_value' => isset($default_values['language']) ? $default_values['language'] : NULL,
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
    $params = array();

    if (isset($_GET['language'])) {
      $params['language'] = $_GET['language'];
    }

    return $params;
  }

  /**
   * Builds a table row for overview form.
   *
   * @param array $data
   *   Data needed to build the list row.
   *
   * @return array
   */
  public function overviewRow($data) {
    // Set the default item key, assume it's the first.
    $item_title = reset($data['object']);
    $type = i18n_object_info($item_title->textgroup . '_' . $item_title->type, 'title');

    $row = array(
      'id' => $data['id'],
      'title' => $item_title->get_string(),
      'type' => empty($type) ? t('Unknown') : $type,
    );

    foreach (language_list() as $langcode => $language) {
      $row['langcode-' . $langcode] = theme('tmgmt_ui_translation_language_status_single', array(
        'translation_status' => $data['translation_statuses'][$langcode],
        'job_item' => isset($data['current_job_items'][$langcode]) ? $data['current_job_items'][$langcode] : NULL,
      ));
    }

    return $row;
  }

  /**
   * Implements TMGMTSourceUIControllerInterface::overviewFormSubmit().
   */
  public function overviewFormSubmit($form, &$form_state, $type) {

    // Handle search redirect.
    $this->overviewSearchFormRedirect($form, $form_state, $type);
    $items = array_filter($form_state['values']['items']);
    $type = 'i18n_string';
    dsm($items);


    $jobs = array();
    // $entities = entity_load($type, $form_state['values']['items']);
    $source_lang_registry = array();
    // Loop through entities and create individual jobs for each source language.
    foreach ($items as $item) {

      /**
       * @var EntityTranslationDefaultHandler $handler
       */
//      $handler = entity_translation_get_handler($type, $item);
      $source_lang = $this->sourceLanguage();
//      $entity_id = entity_id($type, $item);

      // For given source lang no job exists yet.
      if (!isset($source_lang_registry[$source_lang])) {
        // Create new job.
        $job = tmgmt_job_create($source_lang, NULL, $GLOBALS['user']->uid);
        // Add initial job item.
        $job->addItem('i18n_string', $type, $item);
        // Add job identifier into registry
        $source_lang_registry[$source_lang] = $job->tjid;
        // Add newly created job into jobs queue.
        $jobs[$job->tjid] = $job;
      }
      // We have a job for given source lang, so just add new job item for the
      // existing job.
      else {
        $jobs[$source_lang_registry[$source_lang]]->addItem('i18n_string', $type, $item);
      }
    }
    dsm($jobs);
    // If necessary, do a redirect.
    $redirects = tmgmt_ui_job_checkout_multiple($jobs);
    if ($redirects) {
      tmgmt_ui_redirect_queue_set($redirects, current_path());
      $form_state['redirect'] = tmgmt_ui_redirect_queue_dequeue();

      drupal_set_message(format_plural(count($redirects), t('One job needs to be checked out.'), t('@count jobs need to be checked out.')));
    }
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

  public function sourceLanguage() {
    return variable_get_value('i18n_string_source_language');
  }

}
