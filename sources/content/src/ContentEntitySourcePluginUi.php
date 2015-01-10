<?php

/**
 * @file
 * Contains \Drupal\tmgmt_content\ContentEntitySourcePluginUi.
 */

namespace Drupal\tmgmt_content;

use Drupal\Component\Utility\String;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Url;
use Drupal\tmgmt\SourcePluginUiBase;
use Drupal\tmgmt\TMGMTException;

/**
 * Abstract entity ui controller class for source plugin that provides
 * getEntity() method to retrieve list of entities of specific type. It also
 * allows to implement alter hook to alter the entity query for a specific type.
 *
 * @ingroup tmgmt_source
 */
class ContentEntitySourcePluginUi extends SourcePluginUiBase {

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
   *   tmgmt_content_get_translatable_entities() as the second parameter.
   *
   * @return array
   *   Array of entities.
   */
  public function getEntitiesTranslationData($type, $property_conditions = array()) {

    $return_value = array();
    $entities = tmgmt_content_get_translatable_entities($type, $property_conditions, TRUE);

    $bundles = tmgmt_content_get_translatable_bundles($type);

    // For retrieved entities add translation specific data.
    foreach ($entities as $entity) {
      // This occurs on user entity type.
      if (!$entity->id()) {
        continue;
      }

      // Get existing translations and current job items for the entity
      // to determine translation statuses
      $translations = $entity->getTranslationLanguages();
      $source_lang = $entity->language()->getId();
      $current_job_items = tmgmt_job_item_load_latest('content', $type, $entity->id(), $source_lang);

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
  public function overviewSearchFormPart(array $form, FormStateInterface $form_state, $type) {

    // Add search form specific styling.
    $form['#attached']['library'][] = 'tmgmt_content/entity_source_search_form';

    // Add entity type value into form array so that it is available in
    // the form alter hook.
    $form_state->set('entity_type', $type);

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
    foreach (\Drupal::languageManager()->getLanguages() as $langcode => $language) {
      $language_options[$langcode] = $language->getName();
    }

    $form['search_wrapper']['search']['langcode'] = array(
      '#type' => 'select',
      '#title' => t('Source Language'),
      '#options' => $language_options,
      '#empty_option' => t('All'),
      '#default_value' => isset($_GET['langcode']) ? $_GET['langcode'] : NULL,
    );

    $bundle_key = $entity_type->getKey('bundle');
    $bundle_options = tmgmt_content_get_translatable_bundles($type);

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
      drupal_set_message($this->t('Entity translation is not enabled for any of existing content types. To use this functionality go to Content types administration and enable entity translation for desired content types.'), 'warning');
      unset($form['search_wrapper']);
      return $form;
    }

    $options = array();
    foreach (language_list() as $langcode => $language) {
      $options[$langcode] = $language->getName();
    }

    $form['search_wrapper']['search']['target_language'] = array(
      '#type' => 'select',
      '#title' => $this->t('Target language'),
      '#options' => $options,
      '#empty_option' => $this->t('Any'),
      '#default_value' => isset($_GET['target_language']) ? $_GET['target_language'] : NULL,
    );
    $form['search_wrapper']['search']['target_status'] = array(
      '#type' => 'select',
      '#title' => $this->t('Target status'),
      '#options' => array(
        'untranslated_or_outdated' => $this->t('Untranslated or outdated'),
        'untranslated' => $this->t('Untranslated'),
        'outdated' => $this->t('Outdated'),
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
  public function overviewSearchFormRedirect(array $form, FormStateInterface $form_state, $type) {
    if ($form_state->getTriggeringElement()['#id'] == 'edit-search-cancel') {
      $form_state->setRedirect('tmgmt.source_overview', array('plugin' => 'content', 'item_type' => $type));
      return TRUE;
    }
    elseif ($form_state->getTriggeringElement()['#id'] == 'edit-search-submit') {
      $query = array();

      foreach ($form_state->getValue('search') as $key => $value) {
        $query[$key] = $value;
      }
      $form_state->setRedirect('tmgmt.source_overview', array('plugin' => 'content', 'item_type' => $type), array('query' => $query));
      return TRUE;
    }
  }


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
        'data' => String::checkPlain($language->getName()),
      );
    }

    $entity_type = \Drupal::entityManager()->getDefinition($type);

    $header = array(
      'title' => array('data' => $this->t('Title (in source language)')),
    );

    // Show the bundle if there is more than one for this entity type.
    if (count(tmgmt_content_get_translatable_bundles($type)) > 1) {
      $header['bundle'] = array('data' => $this->t('@entity_name type', array('@entity_name' => $entity_type->getLabel())));
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
    $label = $data['entity_label'] ? $data['entity_label'] : $this->t('@type: @id', array(
      '@type' => $data['entity_type'],
      '@id' => $data['entity_id']
    ));

    $row = array(
      'id' => $data['entity_id'],
      'title' => \Drupal::l($label, Url::fromUri('base:/' . $data['entity_url'])),
    );

    if (isset($data['bundle'])) {
      $row['bundle'] = $data['bundle'];
    }

    $languages = \Drupal::languageManager()->getLanguages();
    foreach ($languages as $langcode => $language) {
      $array = array(
        '#theme' => 'tmgmt_translation_language_status_single',
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
  public function overviewForm(array $form, FormStateInterface $form_state, $type) {

    $form += $this->overviewSearchFormPart($form, $form_state, $type);

    $form['#attached']['library'][] = 'tmgmt/admin';

    $form['items'] = array(
      '#type' => 'tableselect',
      '#header' => $this->overviewFormHeader($type),
      '#empty' => $this->t('No entities matching given criteria have been found.'),
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
  public function overviewFormValidate(array $form, FormStateInterface $form_state, $type) {
    $target_language = $form_state->getValue(array('search', 'target_language'));
    if (!empty($target_language) && $form_state->getValue(array('search', 'langcode')) == $target_language) {
      $form_state->setErrorByName('search[target_language]', $this->t('The source and target languages must not be the same.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function overviewFormSubmit(array $form, FormStateInterface $form_state, $type) {
    // Handle search redirect.
    if ($this->overviewSearchFormRedirect($form, $form_state, $type)) {
      return;
    }

    $jobs = array();
    $entities = entity_load_multiple($type, $form_state->getValue('items'));
    $source_lang_registry = array();

    // Loop through entities and create individual jobs for each source language.
    foreach ($entities as $entity) {
      /* @var $entity \Drupal\Core\Entity\EntityInterface */
      $source_lang = $entity->language()->getId();

      try {
        // For given source lang no job exists yet.
        if (!isset($source_lang_registry[$source_lang])) {
          // Create new job.
          $job = tmgmt_job_create($source_lang, LanguageInterface::LANGCODE_NOT_SPECIFIED, $GLOBALS['user']->id());
          // Add initial job item.
          $job->addItem('content', $type, $entity->id());
          // Add job identifier into registry
          $source_lang_registry[$source_lang] = $job->id();
          // Add newly created job into jobs queue.
          $jobs[$job->id()] = $job;
        }
        // We have a job for given source lang, so just add new job item for the
        // existing job.
        else {
          $jobs[$source_lang_registry[$source_lang]]->addItem('content', $type, $entity->id());
        }
      } catch (TMGMTException $e) {
        watchdog_exception('tmgmt', $e);
        drupal_set_message($this->t('Unable to add job item for entity %name: %error.', array(
          '%name' => $entity->label(),
          '%error' => $e->getMessage()
        )), 'error');
      }
    }

    // If necessary, do a redirect.
    $redirects = tmgmt_job_checkout_multiple($jobs);
    if ($redirects) {
      tmgmt_redirect_queue_set($redirects, Url::fromRoute('<current>')->getInternalPath());
      $form_state->setRedirectUrl(Url::fromUri('base://' . tmgmt_redirect_queue_dequeue()));

      drupal_set_message(format_plural(count($redirects), $this->t('One job needs to be checked out.'), $this->t('@count jobs need to be checked out.')));
    }
  }

}
