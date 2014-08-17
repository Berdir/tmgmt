<?php

/*
 * @file
 * Contains Drupal\tmgmt\Plugin\Core\Entity\JobItem.
 */

namespace Drupal\tmgmt\Entity;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Language\Language;
use Drupal\tmgmt\TMGMTException;

/**
 * Entity class for the tmgmt_job_item entity.
 *
 * @ContentEntityType(
 *   id = "tmgmt_job_item",
 *   label = @Translation("Translation Job Item"),
 *   module = "tmgmt",
 *   controllers = {
 *     "access" = "Drupal\tmgmt\Entity\Controller\JobItemAccessControlHandler",
 *     "form" = {
 *       "edit" = "Drupal\tmgmt\Form\JobItemForm"
 *     },
 *   },
 *   base_table = "tmgmt_job_item",
 *   entity_keys = {
 *     "id" = "tjiid",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "tmgmt.job_item_entity",
 *   }
 * )
 *
 * @ingroup tmgmt_job
 */
class JobItem extends ContentEntityBase {

  /**
   * Holds the unserialized source data.
   *
   * @var array
   */
  protected $unserializedData;

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields['tjiid'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Job Item ID'))
      ->setDescription(t('The Job Item ID.'))
      ->setReadOnly(TRUE)
      ->setSetting('unsigned', TRUE);

    $fields['tjid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Job'))
      ->setDescription(t('The Job.'))
      ->setReadOnly(TRUE)
      ->setSetting('target_type', 'tmgmt_job')
      ->setDefaultValue(0);

    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The job item UUID.'))
      ->setReadOnly(TRUE);

    $fields['plugin'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Plugin'))
      ->setDescription(t('The plugin of this job item.'))
      ->setSettings(array(
        'max_length' => 255,
      ));

    $fields['item_type'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Item Type'))
      ->setDescription(t('The item type of this job item.'))
      ->setSettings(array(
        'max_length' => 255,
      ));

    $fields['item_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Item ID'))
      ->setDescription(t('The item ID of this job item.'))
      ->setSettings(array(
        'max_length' => 255,
      ));

    $fields['data'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Data'))
      ->setDescription(t('The source data'));

    $fields['state'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Job item state'))
      ->setDescription(t('The job item state'))
      ->setDefaultValue(TMGMT_JOB_ITEM_STATE_ACTIVE);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the job was last edited.'));

    $fields['count_pending'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Pending count'))
      ->setSetting('unsigned', TRUE);

    $fields['count_translated'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Translated count'))
      ->setSetting('unsigned', TRUE);

    $fields['count_reviewed'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Reviewed count'))
      ->setSetting('unsigned', TRUE);

    $fields['count_accepted'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Accepted count'))
      ->setSetting('unsigned', TRUE);

    $fields['word_count'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Word count'))
      ->setSetting('unsigned', TRUE);

    return $fields;
  }

  /**
   * Clones as active.
   */
  public function cloneAsActive() {
    $clone = $this->createDuplicate();
    $clone->data->value = NULL;
    $clone->unserializedData = NULL;
    $clone->tjid->target_id = 0;
    $clone->tjiid->value = 0;
    $clone->word_count->value = NULL;
    $clone->count_accepted->value = NULL;
    $clone->count_pending->value = NULL;
    $clone->count_translated->value = NULL;
    $clone->count_reviewed->value = NULL;
    $clone->state->value = TMGMT_JOB_ITEM_STATE_ACTIVE;
    return $clone;
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);
    if ($this->getJobId()) {
      $this->recalculateStatistics();
    }
    if ($this->unserializedData) {
      $this->data = serialize($this->unserializedData);
    }
    elseif (empty($this->get('data')->value)) {
      $this->data = serialize(array());
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function preDelete(EntityStorageInterface $storage, array $entities) {
    // We need to check whether the state of the job is affected by this
    // deletion.
    foreach ($entities as $entity) {
      if ($job = $entity->getJob()) {
        // We only care for active jobs.
        if ($job->isActive() && tmgmt_job_check_finished($job->id())) {
          // Mark the job as finished.
          $job->finished();
        }
      }
    }
    parent::preDelete($storage, $entities);
  }

  /**
   * {@inheritdoc}
   */
  public static function postDelete(EntityStorageInterface $storage, array $entities) {
    parent::postDelete($storage, $entities);
    // Since we are deleting one or multiple job items here we also need to
    // delete the attached messages.
    $mids = \Drupal::entityQuery('tmgmt_message')
      ->condition('tjiid', array_keys($entities))
      ->execute();
    if (!empty($mids)) {
      entity_delete_multiple('tmgmt_message', $mids);
    }

    $trids = \Drupal::entityQuery('tmgmt_remote')
      ->condition('tjiid', array_keys($entities))
      ->execute();
    if (!empty($trids)) {
      entity_delete_multiple('tmgmt_remote', $trids);
    }
  }

  /**
   * Returns the Job ID.
   *
   * @return int
   *   The job ID.
   */
  public function getJobId() {
    return $this->get('tjid')->target_id;
  }

  /**
   * Returns the plugin.
   *
   * @return string
   *   The plugin ID.
   */
  public function getPlugin() {
    return $this->get('plugin')->value;
  }

  /**
   * Returns the item type.
   *
   * @return string
   *   The item type.
   */
  public function getItemType() {
    return $this->get('item_type')->value;
  }

  /**
   * Returns the item ID.
   *
   * @return string
   *   The item ID.
   */
  public function getItemId() {
    return $this->get('item_id')->value;
  }

  /**
   * Returns the created time.
   *
   * @return int
   *   The time when the job was last changed.
   */
  function getChangedTime() {
    return $this->get('changed')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function label($langcode = NULL) {
    if ($controller = $this->getSourceController()) {
      $label = $controller->getLabel($this);
    }
    else {
      $label = parent::Label();
    }

    if (strlen($label) > TMGMT_JOB_LABEL_MAX_LENGTH) {
      $label = truncate_utf8($label, TMGMT_JOB_LABEL_MAX_LENGTH, TRUE);
    }

    return $label;
  }

  /**
   * Add a log message for this job item.
   *
   * @param string $message
   *   The message to store in the log. Keep $message translatable by not
   *   concatenating dynamic values into it! Variables in the message should be
   *   added by using placeholder strings alongside the variables argument to
   *   declare the value of the placeholders. See t() for documentation on how
   *   $message and $variables interact.
   * @param array $variables
   *   (Optional) An array of variables to replace in the message on display.
   * @param string $type
   *   (Optional) The type of the message. Can be one of 'status', 'error',
   *   'warning' or 'debug'. Messages of the type 'debug' will not get printed
   *   to the screen.
   *
   * @return \Drupal\tmgmt\Entity\Message
   */
  public function addMessage($message, $variables = array(), $type = 'status') {
    // Save the job item if it hasn't yet been saved.
    if (!$this->isNew() || $this->save()) {
      $message = tmgmt_message_create($message, $variables, array('tjid' => $this->getJobId(), 'tjiid' => $this->id(), 'type' => $type));
      if ($message->save()) {
        return $message;
      }
    }
  }

  /**
   * Retrieves the label of the source object via the source controller.
   *
   * @return
   *   The label of the source object.
   */
  public function getSourceLabel() {
    if ($controller = $this->getSourceController()) {
      return $controller->getLabel($this);
    }
    return FALSE;
  }

  /**
   * Retrieves the path to the source object via the source controller.
   *
   * @return
   *   The path to the source object.
   */
  public function getSourceUri() {
    if ($controller = $this->getSourceController()) {
      return $controller->getUri($this);
    }
    return FALSE;
  }

  /**
   * Returns the user readable type of job item.
   *
   * @param string
   *   A type that describes the job item.
   */
  public function getSourceType() {
    if ($controller = $this->getSourceController()) {
      return $controller->getType($this);
    }
    return ucfirst($this->get('item_type')->value);
  }

  /**
   * Loads the job entity that this job item is attached to.
   *
   * @return \Drupal\tmgmt\Entity\Job
   *   The job entity that this job item is attached to or NULL if there is
   *   no job.
   */
  public function getJob() {
    return $this->get('tjid')->entity;
  }

  /**
   * Returns the translator for this job item.
   *
   * @return \Drupal\tmgmt\Entity\Translator
   *   The translator entity or NULL if there is none.
   */
  public function getTranslator() {
    if ($job = $this->getJob()) {
      return $job->getTranslator();
    }
    return NULL;
  }

  /**
   * Returns the translator plugin controller of the translator of this job item.
   *
   * @return \Drupal\tmgmt\TranslatorPluginInterface
   *   The controller of the translator plugin or NULL if there is none.
   */
  public function getTranslatorController() {
    if ($job = $this->getJob()) {
      return $job->getTranslatorController();
    }
    return NULL;
  }

  /**
   * Array of the data to be translated.
   *
   * The structure is similar to the form API in the way that it is a possibly
   * nested array with the following properties whose presence indicate that the
   * current element is a text that might need to be translated.
   *
   * - #text: The text to be translated.
   * - #label: (Optional) The label that might be shown to the translator.
   * - #comment: (Optional) A comment with additional information.
   * - #translate: (Optional) If set to FALSE the text will not be translated.
   * - #translation: The translated data. Set by the translator plugin.
   * - #escape: (Optional) List of arrays with a required string key, keyed by
   *   the position key. Translators must use this list to prevent translation
   *   of these strings if possible.
   *
   *
   * @todo: Move data item documentation to a new, separate api group.
   *
   * The key can be an alphanumeric string.
   * @param $key
   *   If present, only the subarray identified by key is returned.
   * @param $index
   *   Optional index of an attribute below $key.
   *
   * @return array
   *   A structured data array.
   */
  public function getData(array $key = array(), $index = NULL) {
    if (empty($this->unserializedData) && $this->get('data')->value) {
      $this->unserializedData = unserialize($this->get('data')->value);
    }
    if (empty($this->unserializedData) && $this->getJobId()) {
      // Load the data from the source if it has not been set yet.
      $this->unserializedData = $this->getSourceData();
      $this->save();
    }
    if (empty($key)) {
      return $this->unserializedData;
    }
    if ($index) {
      $key = array_merge($key, array($index));
    }
    return NestedArray::getValue($this->unserializedData, $key);
  }

  /**
   * Loads the structured source data array from the source.
   */
  public function getSourceData() {
    if ($controller = $this->getSourceController()) {
      return $controller->getData($this);
    }
    return array();
  }

  /**
   * Returns the plugin controller of the configured plugin.
   *
   * @return \Drupal\tmgmt\SourcePluginInterface
   */
  public function getSourceController() {
    if ($this->get('plugin')->value) {
      try {
        return \Drupal::service('plugin.manager.tmgmt.source')->createInstance($this->get('plugin')->value);
      } catch (PluginException $e) {
        // Ignore exceptions due to missing source plugins.
      }
    }
    return FALSE;
  }

  /**
   * Count of all pending data items.
   *
   * @return int
   *   Pending counts.
   */
  public function getCountPending() {
    return $this->get('count_pending')->value;
  }

  /**
   * Count of all translated data items.
   *
   * @return int
   *   Translated count.
   */
  public function getCountTranslated() {
    return $this->get('count_translated')->value;
  }

  /**
   * Count of all accepted data items.
   *
   * @return int
   *   Accepted count.
   */
  public function getCountAccepted() {
    return $this->get('count_accepted')->value;
  }

  /**
   * Count of all accepted data items.
   *
   * @return int
   *   Accepted count
   */
  public function getCountReviewed() {
    return $this->get('count_reviewed')->value;
  }

  /**
   * Word count of all data items.
   *
   * @return int
   *   Word count
   */
  public function getWordCount() {
    return (int) $this->get('word_count')->value;
  }

  /**
   * Sets the state of the job item to 'needs review'.
   */
  public function needsReview($message = NULL, $variables = array(), $type = 'status') {
    if (!isset($message)) {
      $uri = $this->getSourceUri();
      $message = 'The translation for !source needs to be reviewed.';
      $variables = array('!source' => l($this->getSourceLabel(), $uri['path']));
    }
    $return = $this->setState(TMGMT_JOB_ITEM_STATE_REVIEW, $message, $variables, $type);
    // Auto accept the trganslation if the translator is configured for it.
    if ($this->getTranslator()->getSetting('auto_accept')) {
      $this->acceptTranslation();
    }
    return $return;
  }

  /**
   * Sets the state of the job item to 'accepted'.
   */
  public function accepted($message = NULL, $variables = array(), $type = 'status') {
    if (!isset($message)) {
      $uri = $this->getSourceUri();
      $message = 'The translation for !source has been accepted.';
      $variables = array('!source' => l($this->getSourceLabel(), $uri['path']));
    }
    $return = $this->setState(TMGMT_JOB_ITEM_STATE_ACCEPTED, $message, $variables, $type);
    // Check if this was the last unfinished job item in this job.
    if (tmgmt_job_check_finished($this->getJobId()) && $job = $this->getJob()) {
      // Mark the job as finished.
      $job->finished();
    }
    return $return;
  }

  /**
   * Sets the state of the job item to 'active'.
   */
  public function active($message = NULL, $variables = array(), $type = 'status') {
    if (!isset($message)) {
      $uri = $this->getSourceUri();
      $message = 'The translation for !source is now being processed.';
      $variables = array('!source' => l($this->getSourceLabel(), $uri['path']));
    }
    return $this->setState(TMGMT_JOB_ITEM_STATE_ACTIVE, $message, $variables, $type);
  }

  /**
   * Updates the state of the job item.
   *
   * @param $state
   *   The new state of the job item. Has to be one of the job state constants.
   * @param $message
   *   (Optional) The log message to be saved along with the state change.
   * @param $variables
   *   (Optional) An array of variables to replace in the message on display.
   *
   * @return int
   *   The updated state of the job if it could be set.
   *
   * @see Job::addMessage()
   */
  public function setState($state, $message = NULL, $variables = array(), $type = 'debug') {
    // Return TRUE if the state could be set. Return FALSE otherwise.
    if (array_key_exists($state, tmgmt_job_item_states()) && $this->get('state')->value != $state) {
      $this->state = $state;
      $this->save();
      // If a message is attached to this state change add it now.
      if (!empty($message)) {
        $this->addMessage($message, $variables, $type);
      }
    }
    return $this->get('state')->value;
  }

  /**
   * Returns the state of the job item. Can be one of the job item state
   * constants.
   *
   * @return int
   *   The state of the job item.
   */
  public function getState() {
    // We don't need to check if the state is actually set because we always set
    // it in the constructor.
    return $this->get('state')->value;
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
   * Checks whether the state of this transaction is 'accepted'.
   *
   * @return bool
   *   TRUE if the state is 'accepted', FALSE otherwise.
   */
  public function isAccepted() {
    return $this->isState(TMGMT_JOB_ITEM_STATE_ACCEPTED);
  }

  /**
   * Checks whether the state of this transaction is 'active'.
   *
   * @return bool
   *   TRUE if the state is 'active', FALSE otherwise.
   */
  public function isActive() {
    return $this->isState(TMGMT_JOB_ITEM_STATE_ACTIVE);
  }

  /**
   * Checks whether the state of this transaction is 'needs review'.
   *
   * @return bool
   *   TRUE if the state is 'needs review', FALSE otherwise.
   */
  public function isNeedsReview() {
    return $this->isState(TMGMT_JOB_ITEM_STATE_REVIEW);
  }

  /**
   * Checks whether the state of this transaction is 'aborted'.
   *
   * @return bool
   *   TRUE if the state is 'aborted', FALSE otherwise.
   */
  public function isAborted() {
    return $this->isState(TMGMT_JOB_ITEM_STATE_ABORTED);
  }

  /**
   * Recursively writes translated data to the data array of a job item.
   *
   * While doing this the #status of each data item is set to
   * TMGMT_DATA_ITEM_STATE_TRANSLATED.
   *
   * @param $translation
   *   Nested array of translated data. Can either be a single text entry, the
   *   whole data structure or parts of it.
   * @param $key
   *   (Optional) Either a flattened key (a 'key1][key2][key3' string) or a nested
   *   one, e.g. array('key1', 'key2', 'key2'). Defaults to an empty array which
   *   means that it will replace the whole translated data array.
   */
  protected function addTranslatedDataRecursive($translation, $key = array()) {
    if (isset($translation['#text'])) {
      $data = $this->getData(tmgmt_ensure_keys_array($key));
      if (empty($data['#status']) || $data['#status'] != TMGMT_DATA_ITEM_STATE_ACCEPTED) {

        // In case the origin is not set consider it to be remote.
        if (!isset($translation['#origin'])) {
          $translation['#origin'] = 'remote';
        }

        // If we already have a translation text and it hasn't changed, don't
        // update anything unless the origin is remote.
        if (!empty($data['#translation']['#text']) && $data['#translation']['#text'] == $translation['#text'] && $translation['#origin'] != 'remote') {
          return;
        }

        // In case the timestamp is not set consider it to be now.
        if (!isset($translation['#timestamp'])) {
          $translation['#timestamp'] = REQUEST_TIME;
        }
        // If we have a translation text and is different from new one create
        // revision.
        if (!empty($data['#translation']['#text']) && $data['#translation']['#text'] != $translation['#text']) {

          // Copy into $translation existing revisions.
          if (!empty($data['#translation']['#text_revisions'])) {
            $translation['#text_revisions'] = $data['#translation']['#text_revisions'];
          }

          // If current translation was created locally and the incoming one is
          // remote, do not override the local, just create a new revision.
          if (isset($data['#translation']['#origin']) && $data['#translation']['#origin'] == 'local' && $translation['#origin'] == 'remote') {
            $translation['#text_revisions'][] = array(
              '#text' => $translation['#text'],
              '#origin' => $translation['#origin'],
              '#timestamp' => $translation['#timestamp'],
            );
            $this->addMessage('Translation for customized @key received. Revert your changes if you wish to use it.', array('@key' => tmgmt_ensure_keys_string($key)));
            // Unset text and origin so that the current translation does not
            // get overridden.
            unset($translation['#text'], $translation['#origin'], $translation['#timestamp']);
          }
          // Do the same if the translation was already reviewed and origin is
          // remote.
          elseif ($translation['#origin'] == 'remote' && !empty($data['#status']) && $data['#status'] == TMGMT_DATA_ITEM_STATE_REVIEWED) {
            $translation['#text_revisions'][] = array(
              '#text' => $translation['#text'],
              '#origin' => $translation['#origin'],
              '#timestamp' => $translation['#timestamp'],
            );
            $this->addMessage('Translation for already reviewed @key received and stored as a new revision. Revert to it if you wish to use it.', array('@key' => tmgmt_ensure_keys_string($key)));
            // Unset text and origin so that the current translation does not
            // get overridden.
            unset($translation['#text'], $translation['#origin'], $translation['#timestamp']);
          }
          else {
            $translation['#text_revisions'][] = array(
              '#text' => $data['#translation']['#text'],
              '#origin' => isset($data['#translation']['#origin']) ? $data['#translation']['#origin'] : 'remote',
              '#timestamp' => isset($data['#translation']['#timestamp']) ? $data['#translation']['#timestamp'] : $this->getChangedTime(),
            );
            // Add a message if the translation update is from remote.
            if ($translation['#origin'] == 'remote') {
              $diff = drupal_strlen($translation['#text']) - drupal_strlen($data['#translation']['#text']);
              $this->addMessage('Updated translation for key @key, size difference: @diff characters.', array('@key' => tmgmt_ensure_keys_string($key), '@diff' => $diff));
            }
          }
        }

        $values = array(
          '#translation' => $translation,
          '#status' => TMGMT_DATA_ITEM_STATE_TRANSLATED,
        );
        $this->updateData($key, $values);
      }
      return;
    }

    foreach (element_children($translation) as $item) {
      $this->addTranslatedDataRecursive($translation[$item], array_merge($key, array($item)));
    }
  }

  /**
   * Reverts data item translation to the latest existing revision.
   *
   * @param array $key
   *   Data item key that should be reverted.
   *
   * @return bool
   *   Result of the revert action.
   */
  public function dataItemRevert(array $key) {
    $data = $this->getData($key);
    if (!empty($data['#translation']['#text_revisions'])) {

      $prev_revision = end($data['#translation']['#text_revisions']);
      $data['#translation']['#text_revisions'][] = array(
        '#text' => $data['#translation']['#text'],
        '#timestamp' => $data['#translation']['#timestamp'],
        '#origin' => $data['#translation']['#origin'],
      );
      $data['#translation']['#text'] = $prev_revision['#text'];
      $data['#translation']['#origin'] = $prev_revision['#origin'];
      $data['#translation']['#timestamp'] = $prev_revision['#timestamp'];

      $this->updateData($key, $data);
      $this->addMessage('Translation for @key reverted to the latest version.', array('@key' => tmgmt_ensure_keys_string($key)));
      return TRUE;
    }

    return FALSE;
  }

  /**
   * Updates the values for a specific substructure in the data array.
   *
   * The values are either set or updated but never deleted.
   *
   * @param string|array $key
   *   Key pointing to the item the values should be applied.
   *   The key can be either be an array containing the keys of a nested array
   *   hierarchy path or a string with '][' or '|' as delimiter.
   * @param array $values
   *   Nested array of values to set.
   * @param bool $replace
   *   (optional) When TRUE, replaces the structure at the provided key instead
   *   of writing into it.
   */
  public function updateData($key, $values = array(), $replace = FALSE) {
    if ($replace) {
      if (!is_array($this->unserializedData)) {
        $this->unserializedData = unserialize($this->get('data')->value);
        if (!is_array($this->unserializedData)) {
          $this->unserializedData = array();
        }
      }
      NestedArray::setValue($this->unserializedData, tmgmt_ensure_keys_array($key), $values);
    }
    foreach ($values as $index => $value) {
      // In order to preserve existing values, we can not aplly the values array
      // at once. We need to apply each containing value on its own.
      // If $value is an array we need to advance the hierarchy level.
      if (is_array($value)) {
        $this->updateData(array_merge(tmgmt_ensure_keys_array($key), array($index)), $value);
      }
      // Apply the value.
      else {
        if (!is_array($this->unserializedData)) {
          $this->unserializedData = unserialize($this->get('data')->value);
        }
        NestedArray::setValue($this->unserializedData, array_merge(tmgmt_ensure_keys_array($key), array($index)), $value);
      }
    }
  }

  /**
   * Adds translated data to a job item.
   *
   * This function calls for JobItem::addTranslatedDataRecursive() which
   * sets the status of each added data item to TMGMT_DATA_ITEM_STATE_TRANSLATED.
   *
   * Following rules apply while adding translated data:
   *
   * 1) Updated are only items that are changed. In case there is local
   * modification the translation is added as a revision with a message stating
   * this fact.
   *
   * 2) Merging happens at the data items level, so updating only those that are
   * changed. If a data item is in review/reject status and is being updated
   * with translation originating from remote the status is updated to
   * 'translated' no matter if it is changed or not.
   *
   * 3) Each time a data item is updated the previous translation becomes a
   * revision.
   *
   * If all data items are translated, the status of the job item is updated to
   * needs review.
   *
   * @todo
   * To update the job item status to needs review we could take advantage of
   * the JobItem::getCountPending() and JobItem::getCountTranslated().
   * The catch is, that this counter gets updated while saveing which not yet
   * hapened.
   *
   * @param $translation
   *   Nested array of translated data. Can either be a single text entry, the
   *   whole data structure or parts of it.
   * @param $key
   *   (Optional) Either a flattened key (a 'key1][key2][key3' string) or a nested
   *   one, e.g. array('key1', 'key2', 'key2'). Defaults to an empty array which
   *   means that it will replace the whole translated data array.
   */
  public function addTranslatedData($translation, $key = array()) {
    $this->addTranslatedDataRecursive($translation, $key);
    // Check if the job item has all the translated data that it needs now.
    // Only attempt to change the status to needs review if it is currently
    // active.
    if ($this->isActive()) {
      $data = tmgmt_flatten_data($this->getData());
      $data = array_filter($data, '_tmgmt_filter_data');
      $finished = TRUE;
      foreach ($data as $item) {
        if (empty($item['#status']) || $item['#status'] == TMGMT_DATA_ITEM_STATE_PENDING) {
          $finished = FALSE;
          break;
        }
      }
      if ($finished) {
        // There are no unfinished elements left.
        if ($this->getJob()->getTranslator()->getSetting('auto_accept')) {
          // If the job item is going to be auto-accepted, set to review without
          // a message.
          $this->needsReview(FALSE);
        }
        else {
          // Otherwise, create a message that contains source label, target
          // language and links to the review form.
          $job_url = $this->getJob()->url();
          $variables = array(
            '!source' => l($this->getSourceLabel(), $this->getSystemPath()),
            '@language' => $this->getJob()->getTargetLanguage()->getName(),
            '!review_url' => url($this->getSystemPath(), array('query' => array('destination' => $job_url))),
          );
          $this->needsReview('The translation of !source to @language is finished and can now be <a href="!review_url">reviewed</a>.', $variables);
        }
      }
    }
    $this->save();
  }

  /**
   * Propagates the returned job item translations to the sources.
   *
   * @return boolean
   *   TRUE if we were able to propagate the translated data and the item could
   *   be saved, FALSE otherwise.
   */
  public function acceptTranslation() {
    if (!$this->isNeedsReview() || !$controller = $this->getSourceController()) {
      return FALSE;
    }
    // We don't know if the source plugin was able to save the translation after
    // this point. That means that the plugin has to set the 'accepted' states
    // on its own.
    $controller->saveTranslation($this);
  }

  /**
   * Returns all job messages attached to this job item.
   *
   * @return array
   *   An array of translation job messages.
   */
  public function getMessages($conditions = array()) {
    $query = \Drupal::entityQuery('tmgmt_message')
      ->condition('tjiid', $this->id());
    foreach ($conditions as $key => $condition) {
      if (is_array($condition)) {
        $operator = isset($condition['operator']) ? $condition['operator'] : '=';
        $query->condition($key, $condition['value'], $operator);
      }
      else {
        $query->condition($key, $condition);
      }
    }
    $results = $query->execute();
    if (!empty($results)) {
      return entity_load_multiple('tmgmt_message', $results);
    }
    return array();
  }

  /**
   * Retrieves all siblings of this job item.
   *
   * @return array
   *   An array of job items that are the siblings of this job item.
   */
  public function getSiblings() {
    $ids = \Drupal::entityQuery('tmgmt_job_item')
      ->condition('tjiid', $this->id(), '<>')
      ->condition('tjid', $this->getJobId())
      ->execute();
    if ($ids) {
      return entity_load_multiple('tmgmt_job_item', $ids);
    }
    return FALSE;
  }

  /**
   * Returns all job messages attached to this job item with timestamp newer
   * than $time.
   *
   * @param int $time
   *   (Optional) Messages need to have a newer timestamp than $time. Defaults
   *   to REQUEST_TIME.
   *
   * @return array
   *   An array of translation job messages.
   */
  public function getMessagesSince($time = NULL) {
    $time = isset($time) ? $time : REQUEST_TIME;
    $conditions = array('created' => array('value' => $time, 'operator' => '>='));
    return $this->getMessages($conditions);
  }


  /**
   * Adds remote mapping entity to this job item.
   *
   * @param string $data_item_key
   *   Job data item key.
   * @param int $remote_identifier_1
   *   Array of remote identifiers. In case you need to save
   *   remote_identifier_2/3 set it into $mapping_data argument.
   * @param array $mapping_data
   *   Additional data to be added.
   *
   * @return int|bool
   * @throws TMGMTException
   */
  public function addRemoteMapping($data_item_key = NULL, $remote_identifier_1 = NULL, $mapping_data = array()) {

    if (empty($remote_identifier_1) && !isset($mapping_data['remote_identifier_2']) && !isset($remote_mapping['remote_identifier_3'])) {
      throw new TMGMTException('Cannot create remote mapping without remote identifier.');
    }

    $data = array(
      'tjid' => $this->getJobId(),
      'tjiid' => $this->id(),
      'data_item_key' => $data_item_key,
      'remote_identifier_1' => $remote_identifier_1,
    );

    if (!empty($mapping_data)) {
      $data += $mapping_data;
    }

    $remote_mapping = entity_create('tmgmt_remote', $data);

    return $remote_mapping->save();
  }

  /**
   * Gets remote mappings for current job item.
   *
   * @return array
   *   List of TMGMTRemote entities.
   */
  public function getRemoteMappings() {
    $trids = \Drupal::entityQuery('tmgmt_remote')
      ->condition('tjiid', $this->id())
      ->execute();

    if (!empty($trids)) {
      return entity_load_multiple('tmgmt_remote', $trids);
    }

    return array();
  }

  /**
   * Gets language code of the job item source.
   *
   * @return string
   *   Language code.
   */
  public function getSourceLangCode() {
    return $this->getSourceController()->getSourceLangCode($this);
  }

  /**
   * Gets existing translation language codes of the job item source.
   *
   * @return array
   *   Array of language codes.
   */
  public function getExistingLangCodes() {
    return $this->getSourceController()->getExistingLangCodes($this);
  }

  /**
   * Recalculate statistical word-data: pending, translated, reviewed, accepted.
   */
  public function recalculateStatistics() {
    // Set translatable data from the current entity to calculate words.

    if (empty($this->unserializedData) && $this->get('data')->value) {
      $this->unserializedData = unserialize($this->get('data')->value);
    }

    if (empty($this->unserializedData)) {
      $this->unserializedData = $this->getSourceData();
    }

    // Consider everything accepted when the job item is accepted.
    if ($this->isAccepted()) {
      $this->count_pending = 0;
      $this->count_translated = 0;
      $this->count_reviewed = 0;
      $this->count_accepted = count(array_filter(tmgmt_flatten_data($this->unserializedData), '_tmgmt_filter_data'));
    }
    // Count the data item states.
    else {
      // Reset counter values.
      $this->count_pending = 0;
      $this->count_translated = 0;
      $this->count_reviewed = 0;
      $this->count_accepted = 0;
      $this->word_count = 0;
      $this->count($this->unserializedData);
    }
  }

  /**
   * Parse all data items recursively and sums up the counters for
   * accepted, translated and pending items.
   *
   * @param $item
   *   The current data item.
   */
  protected function count(&$item) {
    if (!empty($item['#text'])) {
      if (_tmgmt_filter_data($item)) {

        // Count words of the data item.
        $this->word_count->value += tmgmt_word_count($item['#text']);

        // Set default states if no state is set.
        if (!isset($item['#status'])) {
          // Translation is present.
          if (!empty($item['#translation'])) {
            $item['#status'] = TMGMT_DATA_ITEM_STATE_TRANSLATED;
          }
          // No translation present.
          else {
            $item['#status'] = TMGMT_DATA_ITEM_STATE_PENDING;
          }
        }
        switch ($item['#status']) {
          case TMGMT_DATA_ITEM_STATE_REVIEWED:
            $this->count_reviewed->value++;
            break;
          case TMGMT_DATA_ITEM_STATE_TRANSLATED:
            $this->count_translated->value++;
            break;
          default:
            $this->count_pending->value++;
            break;
        }
      }
    }
    elseif (is_array($item)) {
      foreach (element_children($item) as $key) {
        $this->count($item[$key]);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function language() {
    return new Language(array('id' => Language::LANGCODE_NOT_SPECIFIED));
  }

}
