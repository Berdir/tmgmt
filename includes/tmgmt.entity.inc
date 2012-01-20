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
  public $translator_plugin;

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
    return tmgmt_translator_plugin_controller($this->translator_plugin);
  }

}

/**
 * Entity class for the tmgmt_job entity.
 *
 * @ingroup job
 */
class TMGMTJobItem extends Entity implements TMGMTDataProvider {

  /**
   * The source plugin that provides the item.
   *
   * @see TMGMTMap::getPluginController().
   *
   * @var varchar
   */
  public $source_plugin;

  /**
   * The identifier of the translation job.
   *
   * @var int
   */
  public $tjid;

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
   * Can be used by the source plugin to store the data instead of creating it
   * on demand.
   *
   * If additional information is added in the UI, like adding comments, it will
   * also be saved here.
   *
   * Always use TMGMTJobItem::getSourceData() to load the data, which will use
   * this property if present and otherwise get it from the source.
   *
   * @var array
   */
  public $data = array();

  /**
   * The translated data in the same structure.
   *
   * Only the parts that actually are translated are present in this array.
   *
   * @var array
   */
  public $translated_data = array();

  /**
   * Overrides Entity::label()
   */
  public function label() {
    $controller = $this->getSourceController();
    return $controller->getTitle($this);
  }

  /**
   * Overrides Entity::uri()
   */
  public function uri() {
    $controller = $this->getSourceController();
    return $controller->getUri($this);
  }

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
    // If something is stored in data, return it immediately.
    if (!empty($this->data)) {
      return $this->data;
    }
    return $this->getSourceController()->getData($this);
  }

  /**
   * Returns the configured plugin controller.
   *
   * @return TMGMTSourcePluginControllerInterface
   */
  public function getSourceController() {
    return tmgmt_source_plugin_controller($this->source_plugin);
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
   *   (Optional) Either a flattened key (a 'key1][key2][key3' string) or a nested
   *   one, e.g. array('key1', 'key2', 'key2'). Defaults to an empty array which
   *   means that it will replace the whole translated data array.
   */
  public function addTranslatedData($translated_data, $key = array()) {
    if (empty($key)) {
      $key = array();
    }
    if (!is_array($key)) {
      $key = explode(TMGMT_ARRAY_DELIMITER, $key);
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
class TMGMTJob extends Entity implements TMGMTDataProvider {

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
   *
   * @var type
   */
  public $uid = 0;

  public $label;

  /**
   * Overrides Entity::__construct().
   */
  public function __construct(array $values = array()) {
    parent::__construct($values, 'tmgmt_job');
  }

  /**
   * Implements EntityAPIControllerInterface::label().
   */
  public function label() {
    $wrapper = entity_metadata_wrapper($this->entityType, $this);
    return t('@source into @target translation job', array('@source' => $wrapper->source_language->label(), '@target' => $wrapper->target_language->label()));
  }

  /**
   * Implements EntityAPIControllerInterface::uri().
   */
  public function uri() {
    return array('path' => 'admin/tmgmt/jobs/' . $this->tjid);
  }

  /**
   * Returns if the state of this transaction is new.
   *
   * @return
   *   TRUE if the state is new, FALSE otherwise.
   */
  public function isNew() {
    return $this->isState(TMGMT_JOB_STATE_NEW);
  }

  /**
   * Returns if the state of this transaction is prepared.
   *
   * @return
   *   TRUE if the state is prepared, FALSE otherwise.
   */
  public function isPrepared() {
    return $this->isState(TMGMT_JOB_STATE_PREPARED);
  }

  /**
   * Returns if the state of this transaction is submitted.
   *
   * @return
   *   TRUE if the state is submitted, FALSE otherwise.
   */
  public function isSubmitted() {
    return $this->isState(TMGMT_JOB_STATE_SUBMITTED);
  }

  /**
   * Returns if the state of this transaction is rejected.
   *
   * @return
   *   TRUE if the state is rejected, FALSE otherwise.
   */
  public function isRejected() {
    return $this->isState(TMGMT_JOB_STATE_REJECTED);
  }

  /**
   * Returns if the state of this transaction is needs review.
   *
   * @return
   *   TRUE if the state is needs review, FALSE otherwise.
   */
  public function isNeedsReview() {
    return $this->isState(TMGMT_JOB_STATE_REVIEW);
  }

  /**
   * Returns if the state of this transaction is accepted.
   *
   * @return
   *   TRUE if the state is accepted, FALSE otherwise.
   */
  public function isAccepted() {
    return $this->isState(TMGMT_JOB_STATE_ACCEPTED);
  }

  /**
   * Returns if the state of this transaction is cancelled.
   *
   * @return
   *   TRUE if the state is cancelled, FALSE otherwise.
   */
  public function isCancelled() {
    return $this->isState(TMGMT_JOB_STATE_CANCELLED);
  }

  /**
   * Propagates the returned job item translations to the sources.
   */
  public function propagateTranslatedData() {
    if ($this->isAccepted()) {
      foreach ($this->getItems() as $item) {
        $item->getSourceController()->saveTranslation($item);
      }
    }
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
    // Save the job if it hasn't yet been saved.
    if (empty($this->tjid)) {
      $this->save();
    }
    $item->tjid = $this->tjid;
    $item->save();
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
    return array();
  }



  /**
   * Returns the job items.
   *
   * @return
   *   Array of translation job messages.
   */
  public function getMessages() {
    $query = new EntityFieldQuery();
    $query->entityCondition('entity_type', 'tmgmt_job_message');
    $query->propertyCondition('tjid', $this->tjid);
    $results = $query->execute();
    if (!empty($results['tmgmt_job_message'])) {
      return tmgmt_job_message_load_multiple(array_keys($results['tmgmt_job_message']));
    }
    return array();
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
    if (array_key_exists($state, tmgmt_job_states())) {
      $this->old_state = $this->getState();
      $this->state = $state;
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Checks whether the passed value matches the current state.
   *
   * @param $state
   *   The value to check the current state against.
   *
   * @return bool
   *   TRUE if the passed state matches the current state, FALSE otherwise.
   */
  public function isState($state) {
    return $this->getState() == $state;
  }

  /**
   * Add a log message for this job.
   *
   * @param $message
   *   The message to store in the log. Keep $message translatable by not
   *   concatenating dynamic values into it! Variables in the message should be
   *   added by using placeholder strings alongside the variables argument to
   *   declare the value of the placeholders. See t() for documentation on how
   *   $message and $variables interact.
   *
   * @param $arguments
   *   (Optional) Array of variables to replace in the message on display.
   *
   */
  public function addMessage($message, $arguments = array()) {
    $message = entity_create('tmgmt_job_message', array(
      'tjid' => $this->tjid,
      'state_before' => isset($this->old_state) ? $this->old_state : $this->getState(),
      'state_after' => $this->getState(),
      'message' => $message,
      'arguments' => $arguments,
    ));
    $message->save();
  }

  /**
   * Set the state of the job to needs review.
   *
   * @see TMGMTJob::addLogMessage()
   */
  public function needsReview($message, $arguments = array()) {
    $this->setState(TMGMT_JOB_STATE_REVIEW);
    $this->addMessage($message);
  }

  /**
   * Set the state of the job to prepared.
   *
   * @see TMGMTJob::addLogMessage()
   */
  public function prepared($message = NULL, $arguments = array()) {
    $this->setState(TMGMT_JOB_STATE_PREPARED);
    $this->addMessage($message);
  }

  /**
   * Set the state of the job to submitted.
   *
   * @see TMGMTJob::addLogMessage()
   */
  public function submitted($message = NULL, $arguments = array()) {
    $this->setState(TMGMT_JOB_STATE_SUBMITTED);
    $this->addMessage($message);
  }

  /**
   * Set the state of the job to accepted
   *
   * @see TMGMTJob::addLogMessage()
   */
  public function accepted($message = NULL, $arguments = array()) {
    $this->setState(TMGMT_JOB_STATE_ACCEPTED);
    $this->addMessage($message);
    $this->propagateTranslatedData();
  }

  /**
   * Set the state of the job to finished.
   *
   * @see TMGMTJob::addLogMessage()
   */
  public function finished($message = NULL, $arguments = array()) {
    // @todo: To be defined.
  }

  /**
   * Set the state of the job to rejected.
   *
   * @see TMGMTJob::addLogMessage()
   */
  public function rejected($message = NULL, $arguments = array()) {
    $this->setState(TMGMT_JOB_STATE_REJECTED);
    $this->addMessage($message);
  }

  /**
   * Set the state of the job to cancelled.
   *
   * Use TMGMTJob::cancelTranslation() to cancel a translation.
   *
   * @see TMGMTJob::addLogMessage()
   */
  public function cancelled($message = NULL, $arguments = array()) {
    $this->setState(TMGMT_JOB_STATE_CANCELLED);
    $this->addMessage($message);
  }

  /**
   * Checks whether a job is translatable by checking if it is prepared and
   * the translator can handle it.
   *
   * @return bool
   *   TRUE if the job is prepared and can be translated, FALSE otherwise.
   */
  public function isTranslatable() {
    if ($this->isPrepared() && $plugin = $this->getTranslatorPlugin()) {
      if ($plugin->canTranslate($this)) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Request the translation of a job from the translator.
   *
   * @return
   *   The updated job status.
   */
  public function requestTranslation() {
    $translator = $this->getTranslatorPlugin();
    $translator->requestTranslation($this);
    $this->save();
    return $this->getState();
  }

  /**
   * Attempts to cancel the translation job.
   *
   * Already accepted jobs can not be cancelled, submitted jobs only if
   * supported by the translator plugin.
   *
   * @return
   *   TRUE if the translation job was cancelled, FALSE otherwise.
   */
  public function cancelTranslation() {
    // Already accepted translation jobs can't be cancelled.
    if ($this->isAccepted()) {
      return FALSE;
    }
    // If the job is not currently pending by the translator (submitted), we can
    // cancel without any checks.
    if (!$this->isSubmitted()) {
      $this->canceled('The translation job has been canceled.');
    }
    else {
      // Otherwise, ask the translator if this job can be cancelled.
      $translator = $this->getTranslatorPlugin();
      $translator->cancelTranslation($this);
    }
    $this->save();
    return $this->isCancelled();
  }

  /**
   * Returns the source data of all job items.
   *
   * @return
   *   A nested array with the source data where the most upper key is the job
   *   item id.
   */
  public function getSourceData() {
    $data = array();
    foreach ($this->getItems() as $key => $item) {
      $data[$key] = $item->getSourceData();
    }
    return $data;
  }

  /**
   * Store translated data back into the items.
   *
   * @param $data
   *   Partially or complete translated data, the most upper key needs to be
   *   the translation job item id.
   * @param $key
   *   (Optional) Either a flattened key (a 'key1][key2][key3' string) or a nested
   *   one, e.g. array('key1', 'key2', 'key2'). Defaults to an empty array which
   *   means that it will replace the whole translated data array. The most
   *   upper key entry needs to be the job id (tjiid).
   */
  public function addTranslatedData($data, $key = NULL) {

    if (empty($key)) {
      $key = array();
    }
    if (!is_array($key)) {
      $key = explode(TMGMT_ARRAY_DELIMITER, $key);
    }

    $items = $this->getItems();

    // If there is a key, get the specific item and forward the call.
    if (!empty($key)) {
      $item_id = array_shift($key);
      if (!isset($items[$item_id])) {
        throw new Exception(t('Item id @id does not exist', array('@id' => $item_id)));
      }
      $items[$item_id]->addTranslatedData($data, $key);
    }
    else {
      foreach ($data as $key => $value) {
        if (isset($items[$key])) {
          $items[$key]->addTranslatedData($value);
        }
      }
    }
  }

}

/**
 * Entity class for the tmgmt_job_message entity.
 *
 * @ingroup message
 */
class TMGMTJobMessage extends Entity {

  /**
   * The ID of the message..
   *
   * @var integer
   */
  public $tjmid;

  /**
   * The ID of the job.
   *
   * @var integer
   */
  public $tjid;

  /**
   * The message text.
   *
   * @var string
   */
  public $message;

  /**
   * An array of string replacement arguments as used by t().
   *
   * @var array
   */
  public $variables;

  /**
   * The status before the event occurred.
   *
   * @var int
   */
  public $status_before;

  /**
   * The status after the event occurred.
   *
   * @var int
   */
  public $status_after;

  /**
   * The time when the message object was created as a timestamp.
   *
   * @var int
   */
  public $created;

  /**
   * Overrides Entity::__construct().
   */
  public function __construct(array $values = array()) {
    parent::__construct($values, 'tmgmt_job_message');
    $this->created = REQUEST_TIME;
  }

}
