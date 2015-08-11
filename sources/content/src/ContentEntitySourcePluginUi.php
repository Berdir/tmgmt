<?php

/**
 * @file
 * Contains \Drupal\tmgmt_content\ContentEntitySourcePluginUi.
 */

namespace Drupal\tmgmt_content;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Url;
use Drupal\tmgmt\SourcePluginUiBase;
use Drupal\tmgmt\TMGMTException;

/**
 * Content entity source plugin UI.
 *
 * Provides getEntity() method to retrieve list of entities of specific type.
 * It also allows to implement alter hook to alter the entity query for a
 * specific type.
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
   * {@inheritdoc}
   */
  public function overviewSearchFormPart(array $form, FormStateInterface $form_state, $type) {
    $form = parent::overviewSearchFormPart($form, $form_state, $type);

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
    $bundle_options = $this->getTranslatableBundles($type);

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
   * Gets overview form header.
   *
   * @return array
   *   Header array definition as expected by theme_tablesort().
   */
  public function overviewFormHeader($type) {
    $entity_type = \Drupal::entityManager()->getDefinition($type);

    $header = array(
      'title' => array('data' => $this->t('Title (in source language)')),
    );

    // Show the bundle if there is more than one for this entity type.
    if (count($this->getTranslatableBundles($type)) > 1) {
      $header['bundle'] = array('data' => $this->t('@entity_name type', array('@entity_name' => $entity_type->getLabel())));
    }

    $header += $this->getLanguageHeader();

    return $header;
  }

  /**
   * Builds a table row for overview form.
   *
   * @param array ContentEntityInterface $entity
   *   Data needed to build the list row.
   * @param array $bundles
   *   The array of bundles.
   *
   * @return array
   */
  public function overviewRow(ContentEntityInterface $entity, array $bundles) {
    $label = $entity->label() ?: $this->t('@type: @id', array(
      '@type' => $entity->getEntityTypeId(),
      '@id' => $entity->id(),
    ));

    // Get existing translations and current job items for the entity
    // to determine translation statuses
    $translations = $entity->getTranslationLanguages();
    $source_lang = $entity->language()->getId();
    $current_job_items = tmgmt_job_item_load_latest('content', $entity->getEntityTypeId(), $entity->id(), $source_lang);

    $row = array(
      'id' => $entity->id(),
      'title' => $entity->link($label),
    );

    if (isset($data['bundle'])) {
      $row['bundle'] = $data['bundle'];
    }

    if (count($bundles) > 1) {
      $row['bundle'] = isset($bundles[$entity->bundle()]) ? $bundles[$entity->bundle()] : t('Unknown');
    }

    // Load entity translation specific data.
    foreach (\Drupal::languageManager()->getLanguages() as $langcode => $language) {

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
   * {@inheritdoc}
   */
  public function overviewForm(array $form, FormStateInterface $form_state, $type) {
    $form = parent::overviewForm($form, $form_state, $type);

    // Build a list of allowed search conditions and get their values from the request.
    $entity_type = \Drupal::entityManager()->getDefinition($type);
    $whitelist = array('langcode', 'target_language', 'target_status');
    $whitelist[] = $entity_type->getKey('bundle');
    $whitelist[] = $entity_type->getKey('label');
    $search_property_params = array_filter(\Drupal::request()->query->all());
    $search_property_params = array_intersect_key($search_property_params, array_flip($whitelist));
    $bundles = $this->getTranslatableBundles($type);

    foreach ($this->getTranslatableEntities($type, $search_property_params) as $entity) {
      // This occurs on user entity type.
      if ($entity->id()) {
        $form['items']['#options'][$entity->id()] = $this->overviewRow($entity, $bundles);
      }
    }

    $form['pager'] = array('#type' => 'pager');

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
          $job = tmgmt_job_create($source_lang, LanguageInterface::LANGCODE_NOT_SPECIFIED, \Drupal::currentUser()->id());
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
      $form_state->setRedirectUrl(Url::fromUri('base:' . tmgmt_redirect_queue_dequeue()));

      drupal_set_message(\Drupal::translation()->formatPlural(count($redirects), $this->t('One job needs to be checked out.'), $this->t('@count jobs need to be checked out.')));
    }
  }

  /**
   * A function to get entity translatable bundles.
   *
   * Note that for comment entity type it will return the same as for node as
   * comment bundles have no use (i.e. in queries).
   *
   * @param string $entity_type
   *   Drupal entity type.
   *
   * @return array
   *   Array of key => values, where key is type and value its label.
   */
  function getTranslatableBundles($entity_type) {

    // If given entity type does not have entity translations enabled, no reason
    // to continue.
    $enabled_types = \Drupal::service('plugin.manager.tmgmt.source')->createInstance('content')->getItemTypes();
    if (!isset($enabled_types[$entity_type])) {
      return array();
    }

    $translatable_bundle_types = array();
    $content_translation_manager = \Drupal::service('content_translation.manager');
    foreach (\Drupal::entityManager()->getBundleInfo($entity_type) as $bundle_type => $bundle_definition) {
      if ($content_translation_manager->isEnabled($entity_type, $bundle_type)) {
        $translatable_bundle_types[$bundle_type] = $bundle_definition['label'];
      }
    }
    return $translatable_bundle_types;
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
   * @param bool $pager
   *   Flag to determine if pager will be used.
   *
   * @return array ContentEntityInterface[]
   *   Array of translatable entities.
   */
  function getTranslatableEntities($entity_type_id, $property_conditions = array(), $pager = FALSE) {

    // If given entity type does not have entity translations enabled, no reason
    // to continue.
    $enabled_types = \Drupal::service('plugin.manager.tmgmt.source')->createInstance('content')->getItemTypes();
    if (!isset($enabled_types[$entity_type_id])) {
      return array();
    }

    $langcodes = array_keys(\Drupal::languageManager()->getLanguages());
    $languages = array_combine($langcodes, $langcodes);

    $entity_type = \Drupal::entityManager()->getDefinition($entity_type_id);
    $label_key = $entity_type->getKey('label');

    $id_key = $entity_type->getKey('id');
    $query = db_select($entity_type->getBaseTable(), 'e');
    $query->addTag('tmgmt_entity_get_translatable_entities');
    $query->addField('e', $id_key);

    $langcode_table_alias = 'e';
    if ($data_table = $entity_type->getDataTable()) {
      $langcode_table_alias = $query->innerJoin($data_table, 'data_table', '%alias.' . $id_key . ' = e.' . $id_key . ' AND %alias.default_langcode = 1');
    }

    $property_conditions += array('langcode' => $langcodes);

    // Searching for sources with missing translation.
    if (!empty($property_conditions['target_status']) && !empty($property_conditions['target_language']) && in_array($property_conditions['target_language'], $languages)) {

      $translation_table_alias = db_escape_field('translation_' . $property_conditions['target_language']);
      $query->leftJoin($data_table, $translation_table_alias, "%alias.$id_key= e.$id_key AND %alias.langcode = :language",
        array(':language' => $property_conditions['target_language']));

      // Exclude entities with having source language same as the target language
      // we search for.
      $query->condition($langcode_table_alias . '.langcode', $property_conditions['target_language'], '<>');

      if ($property_conditions['target_status'] == 'untranslated_or_outdated') {
        $or = db_or();
        $or->isNull("$translation_table_alias.langcode");
        $or->condition("$translation_table_alias.content_translation_outdated", 1);
        $query->condition($or);
      }
      elseif ($property_conditions['target_status'] == 'outdated') {
        $query->condition("$translation_table_alias.content_translation_outdated", 1);
      }
      elseif ($property_conditions['target_status'] == 'untranslated') {
        $query->isNull("$translation_table_alias.langcode");
      }
    }

    // Remove the condition so we do not try to add it again below.
    unset($property_conditions['target_language']);
    unset($property_conditions['target_status']);

    // Searching for the source label.
    if (!empty($label_key) && isset($property_conditions[$label_key])) {
      $search_tokens = explode(' ', $property_conditions[$label_key]);
      $or = db_or();

      foreach ($search_tokens as $search_token) {
        $search_token = trim($search_token);
        if (strlen($search_token) > 2) {
          $or->condition($label_key, "%$search_token%", 'LIKE');
        }
      }

      if ($or->count() > 0) {
        $query->condition($or);
      }

      unset($property_conditions[$label_key]);
    }

    if ($bundle_key = $entity_type->getKey('bundle')) {
      $bundles = array();
      $content_translation_manager = \Drupal::service('content_translation.manager');
      foreach (array_keys(\Drupal::entityManager()->getBundleInfo($entity_type_id)) as $bundle) {
        if ($content_translation_manager->isEnabled($entity_type_id, $bundle)) {
          $bundles[] = $bundle;
        }
      }
      if (!$bundles) {
        return array();
      }

      // If we have type property add condition.
      if (isset($property_conditions[$bundle_key])) {
        $query->condition('e.' . $bundle_key, $property_conditions[$bundle_key]);
        // Remove the condition so we do not try to add it again below.
        unset($property_conditions[$bundle_key]);
      }
      // If not, query db only for translatable node types.
      else {
        $query->condition('e.' . $bundle_key, $bundles, 'IN');
      }
    }

    // Add remaining query conditions which are expected to be handled in a
    // generic way.
    foreach ($property_conditions as $property_name => $property_value) {
      $alias = $property_name == 'langcode' ? $langcode_table_alias : 'e';
      $query->condition($alias . '.' . $property_name, (array) $property_value, 'IN');
    }

    if ($pager) {
      $query = $query->extend('Drupal\Core\Database\Query\PagerSelectExtender')->limit(\Drupal::config('tmgmt.settings')->get('source_list_limit'));
    }
    else {
      $query->range(0, \Drupal::config('tmgmt.settings')->get('source_list_limit'));
    }

    $query->orderBy($entity_type->getKey('id'), 'DESC');
    $result = $query->execute();
    $entity_ids = $result->fetchCol();
    $entities = array();

    if (!empty($entity_ids)) {
      $entities = entity_load_multiple($entity_type_id, $entity_ids);
    }
    return $entities;
  }

}
