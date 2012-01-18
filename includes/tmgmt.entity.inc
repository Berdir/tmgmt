<?php

/*
 * @file
 * Entity classes for Translation Management entities.
 */

/**
 * Entity class for the tmgmt_translator entity.
 *
 * @ingroup translator
 */
class TMGMTTranslator extends Entity {

  /**
   * The ID of the translator.
   *
   * @var integer
   */
  public $tid;

  /**
   * Machine readable name of the translator.
   *
   * @var string
   */
  public $name;

  /**
   * Label of the translator.
   *
   * @var string
   */
  public $label;

  /**
   * Plugin name of the translator.
   *
   * @see TMGMTTranslator::getPluginController().
   *
   * @type string
   */
  public $plugin;

  /**
   * Translator type specific settings.
   *
   * @var array
   */
  public $settings;

  /**
   * Overrides Entity::__construct().
   */
  public function __construct(array $values = array()) {
    parent::__construct($values, 'tmgmt_translator');
  }

  /**
   * Returns the translator plugin controller of this translator.
   *
   * @return TMGMTTranslatorPluginControllerInterface
   */
  public function getTranslatorController() {
    return tmgmt_translator_plugin_controller($this->plugin);
  }

}

/**
 * Entity class for the tmgmt_job entity.
 *
 * @ingroup job
 */
class TMGMTJobItem extends Entity {

  /**
   * The source plugin that provides the item.
   *
   * @see TMGMTMap::getPluginController().
   *
   * @var varchar
   */
  public $plugin;

  /**
   * Type of this item, used by the plugin to identify it.
   *
   * @var string
   */
  public $item_type;

  /**
   * Id of the item.
   *
   * @var integer
   */
  public $item_id;

  /**
   * The translated data in the same structure.
   *
   * Only the parts that actually are translated are present in this array.
   *
   * @var array
   */
  public $translated_data;

  /**
   * Array of the data to be translated.
   *
   * The structure is similar to the form API in the way that it is a possibly
   * nested array with the following properties whose presence indicate that the
   * current element is a text that might need to be translated.
   *
   * - #text: The text to be translated.
   * - #label: The label that might be shown to the translator.
   * - #comment: (Optional) A comment with additional information.
   * - #translate: Either TRUE or FALSE. Only text that has this property set to
   *   TRUE must be translated.
   *
   * The key can be an alphanumeric string.
   *
   * @return
   *   A data array structured in the
   *
   * @see TMGMTJobItem::getFlattenedData()
   */
  public function getSourceData() {
    return $this->getSourceController()->getData($this);
  }

  /**
   * Returns the configured plugin controller.
   *
   * @return TMGMTSourcePluginControllerInterface
   */
  public function getSourceController() {
    return tmgmt_source_plugin_controller($this->plugin);
  }

  /**
   * Add translated data to a job item.
   *
   * Note: This does not merge existing data which already exists below the
   * defined key and will replace it.
   *
   * @param $translated_data
   *   Nested array of translated data. Can either be a single text entry, the
   *   whole data structure or parts of it.
   * @param $key
   *   (Optional) Either a flattened key (a 'key1:key2:key3' string) or a nested
   *   one, e.g. array('key1', 'key2', 'key2'). Defaults to an empty array which
   *   means that it will replace the whole translated data array.
   */
  public function addTranslatedData($translated_data, $key = array()) {
    if (empty($key)) {
      $key = array();
    }
    if (!is_array($key)) {
      $key = explode(':', $key);
    }
    if (!is_array($this->translated_data)) {
      $this->translated_data = array();
    }
    drupal_array_set_nested_value($this->translated_data, $key, $translated_data);
    $this->save();
  }

}

/**
 * Entity class for the tmgmt_job entity.
 *
 * @ingroup job
 */
class TMGMTJob extends Entity {

  /**
   * A new translation job, not yet submitted to a translator.
   */
  const STATE_NEW = 0;

  /**
   * A translation job that has been submitted to the translator.
   */
  const STATE_SUBMITTED = 1;

  /**
   * A translation job that has been rejected by the translator.
   *
   * The response contains information why the job was rejected.
   */
  const STATE_REJECTED = 2;

  /**
   * The translation job was translated and needs to be reviewed.
   */
  const STATE_REVIEW = 3;

  /**
   * The translation has been accepted and the job is finished.
   */
  const STATE_ACCEPTED = 4;

  /**
   * The translation job has been cancelled..
   */
  const STATE_CANCELLED = 5;

  /**
   * Translation job identifier.
   *
   * @var integer
   */
  public $tjid;

  /**
   * Current state of the translation job
   * @var type
   */
  public $state;

  /**
   * Language to be translated from.
   *
   * @var string
   */
  public $source_language;

  /**
   * Language into which the data needs to be translated.
   *
   * @var varchar
   */
  public $target_language;

  /**
   * Response message from the translator.
   *
   * Can for example be used if the job has been rejected by the translator.
   *
   * @var string
   */
  public $response;

  /**
   * Reference to the used translator of this job.
   *
   * @see TMGMTJob::getTranslator().
   *
   * @var string
   */
  public $translator;

  /**
   * Translator specific configuration and context information for this job.
   *
   * @var array
   */
  public $translator_context;

  /**
   * Remote identification of this job.
   *
   * @var integer
   */
  public $translator_id;

  /**
   * Overrides Entity::__construct().
   */
  public function __construct(array $values = array()) {
    parent::__construct($values, 'tmgmt_job');
  }


  /**
   * Propagates the returned job item translations to the sources.
   */
  function saveTranslation() {
    if ($this->state == self::STATE_ACCEPTED) {
      foreach ($this->getItems() as $item) {
        $item->getSourceController()->saveTranslation($item);
      }
    }
  }

  /**
   * Updates the state of a job to the state supplied by the response object.
   *
   * @param TMGMTranslatorResponseInterface $response
   *   The response object.
   *
   * @return bool
   *   TRUE if the state of the job entity has been updated, FALSE otherwise.
   */
  function setStateFromResponse(TMGMTranslatorResponseInterface $response) {
    if (($state = $response->toJobstate()) !== NULL) {
      return $this->setState($state);
    }
    return FALSE;
  }

  /**
   * Adds the item to the translation job.
   *
   * @param $item
   *    The translation job item.
   *
   * @return TMGMTJobItem
   *   The updated item
   */
  public function addItem(TMGMTJobItem $item) {
    return tmgmt_job_add_item($this, $item);
  }

  /**
   * Returns the job items.
   *
   * @return
   *   Array of translation job items.
   */
  public function getItems() {
    $query = new EntityFieldQuery();
    $query->entityCondition('entity_type', 'tmgmt_job_item');
    $query->propertyCondition('tjid', $this->tjid);
    $results = $query->execute();

    if (!empty($results['tmgmt_job_item'])) {
      return tmgmt_job_item_load_multiple(array_keys($results['tmgmt_job_item']));
    }
  }

  /**
   * Returns the translator plugin for this job.
   *
   * @return TMGMTTranslatorPluginControllerInterface
   *   The translator plugin controller or FALSE.
   */
  public function getTranslatorPlugin() {
    if ($this->translator) {
      return tmgmt_translator_plugin_controller($this->translator);
    }
    return FALSE;
  }

  /**
   * Returns the state of the job. Can be one of the job state constants.
   *
   * @return
   *   The state of the job.
   */
  public function getState() {
    if (isset($this->state)) {
      return $this->state;
    }
    return NULL;
  }

  /**
   * Updates the state of the job.
   *
   * @param $state
   *   The new state of the job. Has to be one of the state constants defined
   *   in the state
   */
  public function setState($state) {
    // Return TRUE if the state could be set. Return FALSE otherwise.
    switch ($state) {
      case self::STATE_ACCEPTED:
      case self::STATE_NEW:
      case self::STATE_REJECTED:
      case self::STATE_REVIEW:
      case self::STATE_SUBMITTED:
      case self::STATE_CANCELLED;
        $this->state = $state;
        return TRUE;
    }
    return FALSE;
  }

}
