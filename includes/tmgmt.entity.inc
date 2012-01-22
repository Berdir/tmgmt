<?php

/*
 * @file
 * Entity classes for Translation Management entities.
 */

/**
 * Entity class for the tmgmt_translator entity.
 *
 * @ingroup tmgmt_translator
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
   * Description of the translator.
   *
   * @var string
   */
  public $description;

  /**
   * Plugin name of the translator.
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
  public function getPluginController() {
    watchdog($this->translator_plugin, 'asd');
    return tmgmt_translator_plugin_controller($this->translator_plugin);
  }

  /**
   * Returns the supported target languages for this translator.
   *
   * @return array
   *  An array of supported target languages in ISO format.
   */
  public function getSupportedTargetLanguages($source_language) {
    return $this->getPluginController()->getSupportedTargetLanguages($this, $source_language);
  }

  /**
   * Check whether this translator can handle a particular translation job.
   *
   * @param TMGMTJob $job
   *   The TMGMTJob entity that should be translated.
   *
   * @return boolean
   *   TRUE if the job can be processed and translated, FALSE otherwise.
   */
  public function canTranslate(TMGMTJob $job) {
    return $this->getPluginController()->canTranslate($job);
  }

  /**
   * Checks whether a translator is available.
   *
   * @return bool
   *   TRUE if the translator plugin is available, FALSE otherwise.
   */
  public function isAvailable() {
    return $this->getPluginController()->isAvailable($this);
  }

  /**
   * @todo
   */
  public function jobSettingsForm($form, $form_state, $job) {
    return $this->getPluginController()->jobSettingsForm($form, $form_state, $job);
  }

  /**
   * @todo
   */
  public function settingsForm($form, $form_state) {
    return $this->getPluginController()->settingsForm($form, $form_state, $this);
  }

  /**
   * @todo
   */
  public function getNotAvailableReason() {
    return $this->getPluginController()->getNotAvailableReason($this);
  }

  /**
   * Retrieves a setting
   * @param $name
   *   The name of the setting.
   * @param $default
   *   (Optional) The default value to return in case the setting value is not
   *   set. Defaults to NULL.
   * @return
   *   The setting value or $default if the setting value is not set.
   */
  public function getSetting($name, $default = NULL) {
    return isset($this->settings[$name]) ? $this->settings[$name] : $default;
  }

}

/**
 * Entity class for the tmgmt_job entity.
 *
 * @ingroup tmgmt_job_item
 */
class TMGMTJobItem extends Entity implements TMGMTDataProvider {

  /**
   * The source plugin that provides the item.
   *
   * @var varchar
   */
  public $source_plugin;

  /**
   * The identifier of the translation job.
   *
   * @var integer
   */
  public $tjid;

  /**
   * The identifier of the translation job item.
   *
   * @var integer
   */
  public $tjiid;

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
    return $this->getSourceController()->getTitle($this);
  }

  /**
   * Overrides Entity::uri()
   */
  public function uri() {
    return $this->getSourceController()->getUri($this);
  }

  /**
   * Array of the data to be translated.
   *
   * The structure is similar to the form API in the way that it is a possibly
   * nested array with the following properties whose presence indicate that the
   * current element is a text that might need to be translated.   *
   * - #text: The text to be translated.
   * - #label: The label that might be shown to the translator.
   * - #comment: (Optional) A comment with additional information.
   * - #translate: Either TRUE or FALSE. Only text that has this property set to
   *   TRUE must be translated.
   *
   * The key can be an alphanumeric string.
   *
   * @return array
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
   *
   * @return bool
   *   TRUE if the translated data could be added, FALSE otherwise.
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
    if ($this->save()) {
      return TRUE;
    }
    return FALSE;
  }

}

/**
 * Entity class for the tmgmt_job entity.
 *
 * @ingroup tmgmt_job
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
   * @see TMGMTJob::getTranslatorController()
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
   * The user id of the creator of the job.
   *
   * @var integer
   */
  public $uid;

  /**
   * A custom label for this job.
   */
  public $label;

  /**
   * Overrides Entity::__construct().
   */
  public function __construct(array $values = array()) {
    parent::__construct($values, 'tmgmt_job');
    if (empty($this->tjid)) {
      $this->created = REQUEST_TIME;
    }
    if (!isset($this->state)) {
      $this->state = TMGMT_JOB_STATE_NEW;
    }
  }

  /**
   * Overrides Entity::label().
   */
  public function label() {
    if (!empty($this->label)) {
      return $this->label;
    }

    $wrapper = entity_metadata_wrapper($this->entityType, $this);
    $target = $wrapper->target_language->label();
    if (empty($target)) {
      $target = '?';
    }
    return t('Job @source to @target', array('@source' => $wrapper->source_language->label(), '@target' => $target));
  }

  /**
   * Overrides Entity::uri().
   */
  public function uri() {
    return array('path' => 'admin/config/regional/tmgmt/jobs/' . $this->tjid);
  }

  /**
   * Overrides Entity::buildContent().
   */
  public function buildContent($view_mode = 'full', $langcode = NULL) {
    $wrapper = entity_metadata_wrapper('tmgmt_job', $this);

    $content['created'] = array(
      '#type' => 'item',
      '#title' => t('Created'),
      '#markup' => format_date($wrapper->created->value()),
    );
    $content['changed'] = array(
      '#type' => 'item',
      '#title' => t('Last changed'),
      '#markup' => format_date($wrapper->changed->value()),
    );
    $content['state'] = array(
      '#type' => 'item',
      '#title' => t('State'),
      '#markup' => $wrapper->state->label(),
    );
    $content['created'] = array(
      '#type' => 'item',
      '#title' => t('Created'),
      '#markup' => format_date($wrapper->created->value()),
    );
    $content['items'] = array(
      '#type' => 'item',
      '#title' => t('Items'),
      'links' => array(
        '#theme' => 'links',
      ),
    );
    foreach ($this->getItems() as $item) {
      $uri = $item->uri();
      $content['items']['links']['#links'][$item->tjiid] = array(
        'title' => $item->label(),
        'href' => $uri['path'],
      );
    }
    // @todo: Re-add a view for this.
    /*if (module_exists('views')) {
      $view = views_get_view('translation_job_messages');
      $content['messages'] = array(
        '#type' => 'item',
        '#title' => $view->get_title(),
        '#markup' => $view->preview('block', array($this->tjid)),
      );
    }*/
    return entity_get_controller($this->entityType)->buildContent($this, $view_mode, $langcode, $content);
  }

  /**
   * Returns whether the state of this transaction is new.
   *
   * @return bool
   *   TRUE if the state is new, FALSE otherwise.
   */
  public function isNew() {
    return $this->isState(TMGMT_JOB_STATE_NEW);
  }

  /**
   * Returns whether the state of this transaction is prepared.
   *
   * @return bool
   *   TRUE if the state is prepared, FALSE otherwise.
   */
  public function isPrepared() {
    return $this->isState(TMGMT_JOB_STATE_PREPARED);
  }

  /**
   * Returns whether the state of this transaction is submitted.
   *
   * @return bool
   *   TRUE if the state is submitted, FALSE otherwise.
   */
  public function isSubmitted() {
    return $this->isState(TMGMT_JOB_STATE_SUBMITTED);
  }

  /**
   * Returns whether the state of this transaction is rejected.
   *
   * @return bool
   *   TRUE if the state is rejected, FALSE otherwise.
   */
  public function isRejected() {
    return $this->isState(TMGMT_JOB_STATE_REJECTED);
  }

  /**
   * Returns whether the state of this transaction needs to be reviewed.
   *
   * @return bool
   *   TRUE if the state is needs review, FALSE otherwise.
   */
  public function isNeedsReview() {
    return $this->isState(TMGMT_JOB_STATE_REVIEW);
  }

  /**
   * Returns whether the state of this transaction is accepted.
   *
   * @return bool
   *   TRUE if the state is accepted, FALSE otherwise.
   */
  public function isAccepted() {
    return $this->isState(TMGMT_JOB_STATE_ACCEPTED);
  }

  /**
   * Returns whether the state of this transaction is cancelled.
   *
   * @return bool
   *   TRUE if the state is cancelled, FALSE otherwise.
   */
  public function isCancelled() {
    return $this->isState(TMGMT_JOB_STATE_CANCELLED);
  }

  /**
   * Propagates the returned job item translations to the sources.
   *
   * @return bool
   *   TRUE if we were able to propagate the translated data, FALSE otherwise.
   */
  public function propagateTranslatedData() {
    if ($this->isAccepted()) {
      foreach ($this->getItems() as $item) {
        $item->getSourceController()->saveTranslation($item);
      }
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Adds an item to the translation job.
   *
   * @param $source_plugin
   *   The plugin name.
   * @param $item_type
   *   The source item type.
   * @param $item_id
   *   The source item id.
   *
   * @return TMGMTJobItem
   *   The job item that was added to the job or NULL if it couldn't be saved.
   */
  public function addItem($source_plugin, $item_type, $item_id) {
    // Save the job if it hasn't yet been saved.
    if (!empty($this->tjid) || $this->save()) {
      $item = tmgmt_job_item_create($source_plugin, $item_type, $item_id, array('tjid' => $this->tjid));
      if ($item->save()) {
        return $item;
      }
    }
  }

  /**
   * Returns all job items attached to this job.
   *
   * @return array
   *   An array of translation job items.
   */
  public function getItems() {
    return $this->getAttachedEntities('tmgmt_job_item');
  }

  /**
   * Returns all job messages attached to this job.
   *
   * @return array
   *   An array of translation job messages.
   */
  public function getMessages() {
    return $this->getAttachedEntities('tmgmt_job_message');
  }

  /**
   * Returns all job messages attached to this job with timestamp newer than $time.
   *
   * @param $time
   *   Messages need to have a newer timestamp than $time.
   *
   * @return array
   *   An array of translation job messages.
   */
  public function getMessagesSince($time) {
    $messages = $this->getAttachedEntities('tmgmt_job_message');
    $new_messages = array();
    foreach ($messages as $message) {
      if ($message->created >= $time) {
        $new_messages[] = $message;
      }
    }
    return $new_messages;
  }

  /**
   * Returns all entities of a given entity type that are attached to this job.
   *
   * @param $entity_type
   *   The attached entity type to look for.
   *
   * @return array
   *   An array of entities that are attached to this job.
   */
  public function getAttachedEntities($entity_type) {
    $query = new EntityFieldQuery();
    $query->entityCondition('entity_type', $entity_type);
    $query->propertyCondition('tjid', $this->tjid);
    $results = $query->execute();
    if (!empty($results[$entity_type])) {
      return entity_load($entity_type, array_keys($results[$entity_type]));
    }
    return array();
  }

  /**
   * Returns the translator for this job.
   *
   * @return TMGMTTranslator
   *   The translator entity or NULL if it hasn't been defined yet.
   */
  public function getTranslator() {
    if (isset($this->translator)) {
      return tmgmt_translator_load($this->translator);
    }
  }

  /**
   * Returns the state of the job. Can be one of the job state constants.
   *
   * @return integer
   *   The state of the job or NULL if it hasn't been set yet.
   */
  public function getState() {
    if (isset($this->state)) {
      return $this->state;
    }
  }

  /**
   * Updates the state of the job.
   *
   * @param $state
   *   The new state of the job. Has to be one of the state constants defined
   *   in the state
   *
   * @return bool
   *   TRUE if the state could be set, FALSE otherwise.
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
   *   $message and $variables interact.   *
   * @param $arguments
   *   (Optional) An array of variables to replace in the message on display.
   */
  public function addMessage($message, $arguments = array()) {
    // Save the job if it hasn't yet been saved.
    if (empty($this->tjid) && !$this->save()) {
      return;
    }
    $state_before = isset($this->old_state) ? $this->old_state : $this->getState();
    $state_after = $this->getState();
    $message = tmgmt_job_message_create($state_before, $state_after, $message, $arguments, array(
      'tjid' => $this->tjid,
    ));
    // Remove old state again so that future log messages will use the correct
    // state.
    unset($this->old_state);
    if ($message->save()) {
      return $message;
    }
  }

  /**
   * Set the state of the job to needs review.
   *
   * @param $message
   *   The log message to be saved along with the state change.
   * @param $arguments
   *   (Optional) An array of variables to replace in the message on display.
   *
   * @see TMGMTJob::addMessage()
   */
  public function needsReview($message, $arguments = array()) {
    $this->setState(TMGMT_JOB_STATE_REVIEW);
    $this->addMessage($message, $arguments);
    $this->save();
  }

  /**
   * Set the state of the job to prepared.
   *
   * @param $message
   *   The log message to be saved along with the state change.
   * @param $arguments
   *   (Optional) An array of variables to replace in the message on display.
   *
   * @see TMGMTJob::addMessage()
   */
  public function prepared($message = NULL, $arguments = array()) {
    $this->setState(TMGMT_JOB_STATE_PREPARED);
    $this->addMessage($message, $arguments);
    $this->save();
  }

  /**
   * Set the state of the job to submitted.
   *
   * @param $message
   *   The log message to be saved along with the state change.
   * @param $arguments
   *   (Optional) An array of variables to replace in the message on display.
   *
   * @see TMGMTJob::addMessage()
   */
  public function submitted($message = NULL, $arguments = array()) {
    $this->setState(TMGMT_JOB_STATE_SUBMITTED);
    $this->addMessage($message, $arguments);
    $this->save();
  }

  /**
   * Set the state of the job to accepted
   *
   * @param $message
   *   The log message to be saved along with the state change.
   * @param $arguments
   *   (Optional) An array of variables to replace in the message on display.
   *
   * @see TMGMTJob::addMessage()
   */
  public function accepted($message = NULL, $arguments = array()) {
    $this->setState(TMGMT_JOB_STATE_ACCEPTED);
    // @todo Find a solution for what happens if the propagation of the translated data fails.
    if ($this->propagateTranslatedData()) {
      $this->addMessage($message, $arguments);
      $this->save();
    }
  }

  /**
   * Set the state of the job to finished.
   *
   * @param $message
   *   The log message to be saved along with the state change.
   * @param $arguments
   *   (Optional) An array of variables to replace in the message on display.
   *
   * @see TMGMTJob::addMessage()
   */
  public function finished($message = NULL, $arguments = array()) {
    // @todo Maybe we should clean up the job items here.
    $this->setState(TMGMT_JOB_STATE_ACCEPTED);
    $this->addMessage($message, $arguments);
    $this->save();
  }

  /**
   * Set the state of the job to rejected.
   *
   * @param $message
   *   The log message to be saved along with the state change.
   * @param $arguments
   *   (Optional) An array of variables to replace in the message on display.
   *
   * @see TMGMTJob::addMessage()
   */
  public function rejected($message = NULL, $arguments = array()) {
    $this->setState(TMGMT_JOB_STATE_REJECTED);
    $this->addMessage($message, $arguments);
    $this->save();
  }

  /**
   * Set the state of the job to cancelled.
   *
   * @param $message
   *   The log message to be saved along with the state change.
   * @param $arguments
   *   (Optional) An array of variables to replace in the message on display.
   *
   * Use TMGMTJob::cancelTranslation() to cancel a translation.
   *
   * @see TMGMTJob::addMessage()
   */
  public function cancelled($message = NULL, $arguments = array()) {
    $this->setState(TMGMT_JOB_STATE_CANCELLED);
    $this->addMessage($message, $arguments);
    $this->save();
  }

  /**
   * Checks whether a job is translatable by checking if it is prepared and
   * the translator can handle it.
   *
   * @return bool
   *   TRUE if the job is prepared and can be translated, FALSE otherwise.
   */
  public function isTranslatable() {
    if ($this->isPrepared() && $translator = $this->getTranslator()) {
      if ($translator->canTranslate($this)) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Request the translation of a job from the translator.
   *
   * @return integer
   *   The updated job status.
   */
  public function requestTranslation() {
    if ($this->isTranslatable()) {
      // We don't know if the translator plugin already processed our
      // translation request at this point. That means that the plugin has to
      // set the 'submitted', 'needs review', etc. states on its own.
      $this->getTranslatorController()->requestTranslation($this);
    }
  }

  /**
   * Attempts to cancel the translation job. Already accepted jobs can not be
   * cancelled, submitted jobs only if supported by the translator plugin.
   * Always use this method if you want to cancel a translation job.
   *
   * @return bool
   *   TRUE if the translation job was cancelled, FALSE otherwise.
   */
  public function cancelTranslation() {
    // Already accepted translation jobs can't be cancelled.
    if (!$this->isAccepted()) {
      return FALSE;
    }
    // If the job is not currently pending by the translator (submitted), we can
    // cancel without any checks. Otherwise, ask the translator plugin if the
    // job can be cancelled.
    if (!$this->isSubmitted() || $this->getTranslatorController()->cancelTranslation($this)) {
      $this->cancelled('The translation job has been canceled.');
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Returns the translator plugin controller of the translator of this job.
   *
   * @return TMGMTTranslatorPluginControllerInterface
   */
  public function getTranslatorController() {
    if ($translator = $this->getTranslator($this)) {
      return $translator->getPluginController();
    }
  }

  /**
   * Returns the source data of all job items.
   *
   * @return array
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
 * @ingroup tmgmt_job_message
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
   * @var integer
   */
  public $status_before;

  /**
   * The status after the event occurred.
   *
   * @var integer
   */
  public $status_after;

  /**
   * The time when the message object was created as a timestamp.
   *
   * @var integer
   */
  public $created;

  /**
   * Overrides Entity::__construct().
   */
  public function __construct(array $values = array()) {
    parent::__construct($values, 'tmgmt_job_message');
    if (empty($this->created)) {
      $this->created = REQUEST_TIME;
    }
  }

}
