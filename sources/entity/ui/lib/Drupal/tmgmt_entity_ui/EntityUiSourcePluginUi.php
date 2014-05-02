<?php

/**
 * @file
 * Contains \Drupal\tmgmt_entity_ui\EntityUiSourcePluginUi.
 */

namespace Drupal\tmgmt_entity_ui;

use Drupal\tmgmt\TMGMTException;
use Drupal\tmgmt_entity\EntitySourcePluginUiBase;
use EntityTranslationDefaultHandler;

/**
 * Generic entity ui controller class for source plugin.
 *
 * @ingroup tmgmt_source
 */
class EntityUiSourcePluginUi extends EntitySourcePluginUiBase {

  /**
   * Gets overview form header.
   *
   * @return array
   *   Header array definition as expected by theme_tablesort().
   */
  public function overviewFormHeader($type) {
    $languages = array();
    foreach (language_list() as $langcode => $language) {
      $languages['langcode-' . $langcode] = array(
        'data' => check_plain($language->name),
      );
    }

    $entity_type = \Drupal::entityManager()->getDefinition($type);

    $header = array(
      'title' => array('data' => t('Title (in source language)')),
    );

    // Show the bundle if there is more than one for this entity type.
    if (count(tmgmt_entity_get_translatable_bundles($type)) > 1) {
      $header['bundle'] = array('data' => t('@entity_name type', array('@entity_name' => $entity_type->getLabel())));
    }

    $header += $languages;

    return $header;
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
    $label = $data['entity_label'] ? $data['entity_label'] : t('@type: @id', array(
      '@type' => $data['entity_type'],
      '@id' => $data['entity_id']
    ));

    $row = array(
      'id' => $data['entity_id'],
      'title' => l($label, $data['entity_url']),
    );

    if (isset($data['bundle'])) {
      $row['bundle'] = $data['bundle'];
    }

    $languages = \Drupal::languageManager()->getLanguages();
    foreach ($languages as $langcode => $language) {
      $array = array(
        '#theme' => 'tmgmt_ui_translation_language_status_single',
        '#translation_status' => $data['translation_statuses'][$langcode],
        '#job_item' => isset($data['current_job_items'][$langcode]) ? $data['current_job_items'][$langcode] : NULL,
      );
      $row['langcode-' . $langcode] = array(
        'data' => drupal_render($array),
        'class' => array('langstatus-' . $langcode),
      );
    }

    return $row;
  }

  /**
   * {@inheritdoc}
   */
  public function overviewForm($form, &$form_state, $type) {

    $form += $this->overviewSearchFormPart($form, $form_state, $type);

    $form['#attached']['css'][] = drupal_get_path('module', 'tmgmt_ui') . '/css/tmgmt_ui.admin.css';

    $form['items'] = array(
      '#type' => 'tableselect',
      '#header' => $this->overviewFormHeader($type),
      '#empty' => t('No entities matching given criteria have been found.'),
      '#attributes' => array('id' => 'tmgmt-entities-list'),
    );

    // Load search property params which will be passed into
    $search_property_params = array();
    $exclude_params = array('q', 'page');
    foreach ($_GET as $key => $value) {
      // Skip exclude params, and those that have empty values, as these would
      // make it into query condition instead of being ignored.
      if (in_array($key, $exclude_params) || $value === '') {
        continue;
      }
      $search_property_params[$key] = $value;
    }

    foreach ($this->getEntitiesTranslationData($type, $search_property_params) as $data) {
      $form['items']['#options'][$data['entity_id']] = $this->overviewRow($data);
    }

    $form['pager'] = array('#theme' => 'pager');

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function overviewFormValidate($form, &$form_state, $type) {
    if (!empty($form_state['values']['search']['target_language']) && $form_state['values']['search']['langcode'] == $form_state['values']['search']['target_language']) {
      \Drupal::formBuilder()->setErrorByName('search[target_language]', $form_state, t('The source and target languages must not be the same.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function overviewFormSubmit($form, &$form_state, $type) {
    // Handle search redirect.
    if ($this->overviewSearchFormRedirect($form, $form_state, $type)) {
      return;
    }

    $jobs = array();
    $entities = entity_load_multiple($type, $form_state['values']['items']);
    $source_lang_registry = array();

    // Loop through entities and create individual jobs for each source language.
    foreach ($entities as $entity) {
      /* @var $entity \Drupal\Core\Entity\EntityInterface */
      $source_lang = $entity->language()->id;

      try {
        // For given source lang no job exists yet.
        if (!isset($source_lang_registry[$source_lang])) {
          // Create new job.
          $job = tmgmt_job_create($source_lang, NULL, $GLOBALS['user']->id());
          // Add initial job item.
          $job->addItem('entity', $type, $entity->id());
          // Add job identifier into registry
          $source_lang_registry[$source_lang] = $job->id();
          // Add newly created job into jobs queue.
          $jobs[$job->id()] = $job;
        }
        // We have a job for given source lang, so just add new job item for the
        // existing job.
        else {
          $jobs[$source_lang_registry[$source_lang]]->addItem('entity', $type, $entity->id());
        }
      } catch (TMGMTException $e) {
        watchdog_exception('tmgmt', $e);
        drupal_set_message(t('Unable to add job item for entity %name: %error.', array(
          '%name' => $entity->label(),
          '%error' => $e->getMessage()
        )), 'error');
      }
    }

    // If necessary, do a redirect.
    $redirects = tmgmt_ui_job_checkout_multiple($jobs);
    if ($redirects) {
      tmgmt_ui_redirect_queue_set($redirects, current_path());
      $form_state['redirect'] = tmgmt_ui_redirect_queue_dequeue();

      drupal_set_message(format_plural(count($redirects), t('One job needs to be checked out.'), t('@count jobs need to be checked out.')));
    }
  }

}
