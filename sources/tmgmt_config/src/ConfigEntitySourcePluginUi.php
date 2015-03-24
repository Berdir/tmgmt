<?php

/**
 * @file
 * Contains \Drupal\tmgmt_config\ConfigEntitySourcePluginUi.
 */

namespace Drupal\tmgmt_config;

use Drupal\Component\Utility\String;
use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Url;
use Drupal\tmgmt\SourcePluginUiBase;
use Drupal\tmgmt\TMGMTException;
use Drupal\tmgmt_config\Plugin\tmgmt\Source\ConfigEntitySource;

/**
 * Abstract entity ui controller class for source plugin that provides
 * getEntity() method to retrieve list of entities of specific type. It also
 * allows to implement alter hook to alter the entity query for a specific type.
 *
 * @ingroup tmgmt_source
 */
class ConfigEntitySourcePluginUi extends SourcePluginUiBase {

  /**
   * Entity source list items limit.
   *
   * @var int
   */
  public $pagerLimit = 25;

  /**
   * Builds search form for entity sources overview.
   *
   * @param array $form
   *   Drupal form array.
   * @param $form_state
   *   Drupal form_state array.
   * @param string $type
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

    if ($type == ConfigEntitySource::SIMPLE_CONFIG) {
      $label_key = 'name';
      $label = t('Simple configuration');
    }
    else {
      $entity_type = \Drupal::entityManager()->getDefinition($type);

      $label_key = $entity_type->getKey('label');
      $label = $entity_type->getLabel();
    }


    if (!empty($label_key)) {
      $form['search_wrapper']['search'][$label_key] = array(
        '#type' => 'textfield',
        '#title' => t('@entity_name title', array('@entity_name' => $label)),
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

    $options = array();
    foreach (\Drupal::languageManager()->getLanguages() as $langcode => $language) {
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
        'untranslated' => $this->t('Untranslated'),
        'translated' => $this->t('Translated'),
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
   *
   * @return bool
   *   Returns true, if redirect has been set.
   */
  public function overviewSearchFormRedirect(array $form, FormStateInterface $form_state, $type) {
    if ($form_state->getTriggeringElement()['#id'] == 'edit-search-cancel') {
      $form_state->setRedirect('tmgmt.source_overview', array('plugin' => 'config', 'item_type' => $type));
      return TRUE;
    }
    elseif ($form_state->getTriggeringElement()['#id'] == 'edit-search-submit') {
      $query = array();

      foreach ($form_state->getValue('search') as $key => $value) {
        $query[$key] = $value;
      }
      $form_state->setRedirect('tmgmt.source_overview', array('plugin' => 'config', 'item_type' => $type), array('query' => $query));
      return TRUE;
    }
    return FALSE;
  }


  /**
   * Gets overview form header.
   *
   * @return array
   *   Header array definition as expected by theme_tablesort().
   */
  public function overviewFormHeader($type) {
    $languages = array();
    foreach (\Drupal::languageManager()->getLanguages() as $langcode => $language) {
      $languages['langcode-' . $langcode] = array(
        'data' => String::checkPlain($language->getName()),
      );
    }

    $header = array(
      'title' => array('data' => $this->t('Title (in source language)')),
    );

    $header += $languages;

    return $header;
  }

  /**
   * Builds a table row for overview form.
   *
   * @param \Drupal\Core\Config\Entity\ConfigEntityInterface $entity
   *   Data needed to build the list row.
   *
   * @return array
   *   A single table row for the overview.
   */
  public function overviewRow(ConfigEntityInterface $entity) {
    $label = $entity->label() ?: $this->t('@type: @id', array(
      '@type' => $entity->getEntityTypeId(),
      '@id' => $entity->id(),
    ));

    // Get current job items for the entity to determine translation statuses.
    $source_lang = $entity->language()->getId();
    $current_job_items = tmgmt_job_item_load_latest('config', $entity->getEntityTypeId(), $entity->getConfigDependencyName(), $source_lang);

    $row = array(
      'id' => $entity->id(),
      'title' => $entity->link($label),
    );

    // Load entity translation specific data.
    foreach (\Drupal::languageManager()->getLanguages() as $langcode => $language) {

      $translation_status = 'current';

      if ($langcode == $source_lang) {
        $translation_status = 'original';
      }
      elseif (!$this->isTranslated($langcode, $entity->getConfigDependencyName())) {
        $translation_status = 'missing';
      }

      // @todo Find a way to support marking configuration translations as outdated.

      $array = array(
        '#theme' => 'tmgmt_translation_language_status_single',
        '#translation_status' => $translation_status,
        '#job_item' => isset($current_job_items[$langcode]) ? $current_job_items[$langcode] : NULL,
      );
      $row['langcode-' . $langcode] = array(
        'data' => \Drupal::service('renderer')->render($array),
        'class' => array('langstatus-' . $langcode),
      );
    }
    return $row;
  }

  /**
   * Builds a table row for simple configuration.
   *
   * @param array $definition
   *   A definition.
   *
   * @return array
   *   A single table row for the overview.
   */
  public function overviewRowSimple(array $definition) {
    // Get current job items for the entity to determine translation statuses.
    $config_id = $definition['names'][0];
    $source_lang = \Drupal::config($definition['names'][0])->get('langcode') ?: 'en';
    $current_job_items = tmgmt_job_item_load_latest('config', ConfigEntitySource::SIMPLE_CONFIG, $definition['id'], $source_lang);

    $row = array(
      'id' => $definition['id'],
      'title' => $definition['title'],
    );

    // Load entity translation specific data.
    foreach (\Drupal::languageManager()->getLanguages() as $langcode => $language) {

      $translation_status = 'current';

      if ($langcode == $source_lang) {
        $translation_status = 'original';
      }
      elseif (!$this->isTranslated($langcode, $config_id)) {
        $translation_status = 'missing';
      }

      // @todo Find a way to support marking configuration translations as outdated.

      $array = array(
        '#theme' => 'tmgmt_translation_language_status_single',
        '#translation_status' => $translation_status,
        '#job_item' => isset($current_job_items[$langcode]) ? $current_job_items[$langcode] : NULL,
      );
      $row['langcode-' . $langcode] = array(
        'data' => \Drupal::service('renderer')->render($array),
        'class' => array('langstatus-' . $langcode),
      );
    }
    return $row;
  }

  /**
   * Checks, if an entity is translated.
   *
   * @param string $langcode
   *   Language code.
   * @param string $name
   *   Configuration name.
   *
   * @return bool
   *   Returns true, if it is translatable.
   */
  public function isTranslated($langcode, $name) {
    $config = \Drupal::languageManager()->getLanguageConfigOverride($langcode, $name);
    return !$config->isNew();
  }

  /**
   * {@inheritdoc}
   */
  public function overviewForm(array $form, FormStateInterface $form_state, $type) {
    $form += $this->overviewSearchFormPart($form, $form_state, $type);

    $form['#attached']['library'][] = 'tmgmt/admin';
    // @todo: This should be added in tmgmt_color_legend().
    if (\Drupal::theme()->getActiveTheme()->getName() == 'seven') {
      $form['#attached']['library'][] = 'tmgmt/admin.seven';
    }

    $form['items'] = array(
      '#type' => 'tableselect',
      '#header' => $this->overviewFormHeader($type),
      '#empty' => $this->t('No entities matching given criteria have been found.'),
      '#attributes' => array('id' => 'tmgmt-entities-list'),
    );

    // Load search property params which will be passed into
    $search_property_params = array();
    $exclude_params = array('q', 'page');
    foreach (\Drupal::request()->query->all() as $key => $value) {
      // Skip exclude params, and those that have empty values, as these would
      // make it into query condition instead of being ignored.
      if (in_array($key, $exclude_params) || $value === '') {
        continue;
      }
      $search_property_params[$key] = $value;
    }

    // If the source is of type '_simple_config', we get all definitions that
    // don't have an entity type and display them through overviewRowSimple().
    if ($type == ConfigEntitySource::SIMPLE_CONFIG) {
      $definitions = \Drupal::service('plugin.manager.config_translation.mapper')->getDefinitions();
      foreach ($definitions as $definition_id => $definition) {
        if (!isset($definition['entity_type'])) {
          $form['items']['#options'][$definition_id] = $this->overviewRowSimple($definition);
        }
      }
    }
    // If there is an entity type, list all entities for that.
    else {
      foreach ($this->getTranslatableEntities($type, $search_property_params) as $entity) {
        $form['items']['#options'][$entity->id()] = $this->overviewRow($entity);
      }
    }

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
    $items = array();
    if ($type == ConfigEntitySource::SIMPLE_CONFIG) {
      foreach (array_filter($form_state->getValue('items')) as $item) {
        $definition = \Drupal::service('plugin.manager.config_translation.mapper')->getDefinition($item);
        $item_id = $definition['id'];
        $items[$item_id]['label'] = $definition['title'];;
        $items[$item_id]['langcode'] = \Drupal::config($definition['names'][0])->get('langcode') ?: 'en';
      }
    }
    else {
      $entities = entity_load_multiple($type, array_filter($form_state->getValue('items')));
      foreach ($entities as $entity) {
        /* @var $entity \Drupal\Core\Entity\EntityInterface */
        $item_id = $entity->getConfigDependencyName();
        $items[$item_id]['label'] = $entity->label();
        $items[$item_id]['langcode'] = $entity->language()->getId();
      }
    }
    $source_lang_registry = array();

    // Loop through entities and create individual jobs for each source language.
    foreach ($items as $id => $extra) {
      $source_lang = $extra['langcode'];

      try {
        // For given source lang no job exists yet.
        if (!isset($source_lang_registry[$source_lang])) {
          // Create new job.
          $job = tmgmt_job_create($source_lang, LanguageInterface::LANGCODE_NOT_SPECIFIED, \Drupal::currentUser()->id());
          // Add initial job item.
          $job->addItem('config', $type, $id);
          // Add job identifier into registry
          $source_lang_registry[$source_lang] = $job->id();
          // Add newly created job into jobs queue.
          $jobs[$job->id()] = $job;
        }
        // We have a job for given source lang, so just add new job item for the
        // existing job.
        else {
          $jobs[$source_lang_registry[$source_lang]]->addItem('config', $type, $id);
        }
      } catch (TMGMTException $e) {
        watchdog_exception('tmgmt', $e);
        drupal_set_message($this->t('Unable to add job item for entity %name: %error.', array(
          '%name' => $extra['label'],
          '%error' => $e->getMessage()
        )), 'error');
      }
    }

    // If necessary, do a redirect.
    $redirects = tmgmt_job_checkout_multiple($jobs);
    if ($redirects) {
      tmgmt_redirect_queue_set($redirects, Url::fromRoute('<current>')->getInternalPath());
      $form_state->setRedirectUrl(Url::fromUri('base:' . tmgmt_redirect_queue_dequeue()));

      drupal_set_message(\Drupal::translation()->formatPlural(count($redirects), $this->t('One job needs to be checked out.'), $this->t('@count jobs need to be checked out.')));
    }
  }

  /**
   * Gets translatable entities of a given type.
   *
   * Additionally you can specify entity property conditions, pager and limit.
   *
   * @param string $entity_type_id
   *   Drupal entity type.
   * @param array $property_conditions
   *   Entity properties. There is no value processing so caller must make sure
   *   the provided entity property exists for given entity type and its value
   *   is processed.
   *
   * @return \Drupal\Core\Config\Entity\ConfigEntityInterface[]
   *   Array of translatable entities.
   */
  function getTranslatableEntities($entity_type_id, $property_conditions = array()) {

    $target_status = NULL;
    $target_language = NULL;

    // We unset the target_status and target_language, because we can't filter
    // them this way.
    if (isset($property_conditions['target_status']) && isset($property_conditions['target_language'])) {
      $target_status = $property_conditions['target_status'];
      $target_language = $property_conditions['target_language'];
    }
    unset($property_conditions['target_status']);
    unset($property_conditions['target_language']);

    $search = \Drupal::entityQuery($entity_type_id);
    // unset($property_conditions['target_status']);

    foreach ($property_conditions as $property_name => $property_value) {
      $search->condition($property_name, $property_value, 'CONTAINS');
    }

    $result = $search->execute();
    $entities = array();

    if (!empty($result)) {
      // Load the entities.
      $entities = \Drupal::entityManager()->getStorage($entity_type_id)->loadMultiple($result);

      // @todo Optimize the code below (code duplication).

      // Remove all entities, that are already translated, because we are looking
      // for untranslated entities.
      if ($target_status == 'untranslated') {
        $entities = array_filter($entities, function (ConfigEntityInterface $entity) use ($target_language) {
          return !$this->isTranslated($target_language, $entity->getConfigDependencyName());
        });
      }
      // Show just the translated entities, and remove the others.
      elseif ($target_status == 'translated') {
        $entities = array_filter($entities, function (ConfigEntityInterface $entity) use ($target_language) {
          return $this->isTranslated($target_language, $entity->getConfigDependencyName());
        });
      }
    }
    return $entities;
  }
}
