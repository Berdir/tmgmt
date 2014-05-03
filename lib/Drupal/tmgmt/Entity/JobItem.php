<?php

/*
 * @file
 * Contains Drupal\tmgmt\Plugin\Core\Entity\JobItem.
 */

namespace Drupal\tmgmt\Entity;

use Drupal\Component\Plugin\Exception\PluginException;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Entity\Entity;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Language\Language;
use Drupal\tmgmt\TMGMTException;
use Drupal\Core\Entity\Annotation\EntityType;
use Drupal\Core\Annotation\Translation;

/**
 * Entity class for the tmgmt_job_item entity.
 *
 * @EntityType(
 *   id = "tmgmt_job_item",
 *   label = @Translation("Translation Job Item"),
 *   module = "tmgmt",
 *   controllers = {
 *     "storage" = "Drupal\Core\Entity\EntityDatabaseStorage",
 *     "access" = "Drupal\tmgmt\Entity\Controller\JobItemAccessController",
 *     "form" = {
 *       "edit" = "Drupal\tmgmt\Form\JobItemForm"
 *     },
 *   },
 *   uri_callback = "tmgmt_job_item_uri",
 *   base_table = "tmgmt_job_item",
 *   entity_keys = {
 *     "id" = "tjiid",
 *     "uuid" = "uuid"
 *   }
 * )
 *
 * @ingroup tmgmt_job
 */
class JobItem extends Entity {

  /**
   * The source plugin that provides the item.
   *
   * @var string
   */
  public $plugin;

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
   * The time when the job item was changed as a timestamp.
   *
   * @var integer
   */
  public $changed;

  /**
   * Can be used by the source plugin to store the data instead of creating it
   * on demand.
   *
   * If additional information is added in the UI, like adding comments, it will
   * also be saved here.
   *
   * Always use JobItem::getData() to load the data, which will use
   * this property if present and otherwise get it from the source.
   *
   * @var array
   */
  public $data = array();

  /**
   * Counter for all data items waiting for translation.
   *
   * @var integer
   */
  public $count_pending = 0;

  /**
   * Counter for all translated data items.
   *
   * @var integer
   */
  public $count_translated = 0;

  /**
   * Counter for all accepted data items.
   *
   * @var integer
   */
  public $count_accepted = 0;

  /**
   * Counter for all reviewed data items.
   *
   * @var integer
   */
  public $count_reviewed = 0;

  /**
   * Amount of words in this job item.
   *
   * @var integer
   */
  public $word_count = 0;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $values = array(), $type = 'tmgmt_job_item') {
    parent::__construct($values, $type);
    if (!isset($this->state)) {
      $this->state = TMGMT_JOB_ITEM_STATE_ACTIVE;
    }
  }

  /**
   * Clones as active.
   */
  public function cloneAsActive() {
    $clone = clone $this;
    $clone->data = NULL;
    $clone->tjid = NULL;
    $clone->tjiid = NULL;
    $clone->changed = NULL;
    $clone->word_count = NULL;
    $clone->count_accepted = NULL;
    $clone->count_pending = NULL;
    $clone->count_translated = NULL;
    $clone->count_reviewed = NULL;
    $clone->state = TMGMT_JOB_ITEM_STATE_ACTIVE;
    return $clone;
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    $this->changed = REQUEST_TIME;
    if ($this->getJobId()) {
     $this->recalculateStatistics();
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
    // Since we are deleting one or multiple job items here we also need to
    // delete the attached messages.
    /*
    $mids = \Drupal::entityQuery('tmgmt_job_message')
      ->condition('tjiid', array_keys($entities))
      ->execute();
    if (!empty($mids)) {
      entity_delete_multiple('tmgmt_job_message', $mids);
    }
    }*/

    $trids = \Drupal::entityQuery('tmgmt_remote')
      ->condition('tjiid', array_keys($entities))
      ->execute();
    if (!empty($trids)) {
      entity_delete_multiple('tmgmt_remote', $trids);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function id() {
    return $this->tjiid;
  }

  public function getJobId() {
    return $this->tjid;
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
   * {@inheritdoc}
   */
  public function uri() {
    // The path of a job item is not directly below the job that it belongs to.
    // Having to maintain two unknowns / wildcards (job and job item) in the
    // path is more complex than it has to be. Instead we just append the
    // additional breadcrumb pieces manually with _tmgmt_ui_breadcrumb().
    return array('path' => 'admin/tmgmt/items/' . $this->id());
  }

  /**
   * Add a log message for this job item.
   *
   * @param $message
   *   The message to store in the log. Keep $message translatable by not
   *   concatenating dynamic values into it! Variables in the message should be
   *   added by using placeholder strings alongside the variables argument to
   *   declare the value of the placeholders. See t() for documentation on how
   *   $message and $variables interact.
   * @param $variables
   *   (Optional) An array of variables to replace in the message on display.
   * @param $type
   *   (Optional) The type of the message. Can be one of 'status', 'error',
   *   'warning' or 'debug'. Messages of the type 'debug' will not get printed
   *   to the screen.
   */
  public function addMessage($message, $variables = array(), $type = 'status') {
    // Save the job item if it hasn't yet been saved.
    if (!$this->isNew() || $this->save()) {
      $message = tmgmt_message_create($message, $variables, array('tjid' => $this->getJobId(), 'tjiid' => $this->id(), 'type' => $type));
      if ($message->save()) {
        return $message;
      }
    }
    return FALSE;
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
    return ucfirst($this->item_type);
  }

  /**
   * Loads the job entity that this job item is attached to.
   *
   * @return Job
   *   The job entity that this job item is attached to or FALSE if there was
   *   a problem.
   */
  public function getJob() {
    if ($this->getJobId()) {
      return tmgmt_job_load($this->getJobId());
    }
    return FALSE;
  }

  /**
   * Returns the translator for this job item.
   *
   * @return Translator
   *   The translator entity or FALSE if there was a problem.
   */
  public function getTranslator() {
    if ($job = $this->getJob()) {
      return $job->getTranslator();
    }
    return FALSE;
  }

  /**
   * Returns the translator plugin controller of the translator of this job item.
   *
   * @return \Drupal\tmgmt\TranslatorPluginInterface
   *   The controller of the translator plugin or FALSE if there was a problem.
   */
  public function getTranslatorController() {
    if ($job = $this->getJob()) {
      return $job->getTranslatorController();
    }
    return FALSE;
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
    if (empty($this->data) && $this->getJobId()) {
      // Load the data from the source if it has not been set yet.
      $this->data = $this->getSourceData();
      $this->save();
    }
    if (empty($key)) {
      return $this->data;
    }
    if ($index) {
      $key = array_merge($key, array($index));
    }
    return NestedArray::getValue($this->data, $key);
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
    if (!empty($this->plugin)) {
      try {
        return \Drupal::service('plugin.manager.tmgmt.source')->createInstance($this->plugin);
      } catch (PluginException $e) {
        // Ignore exceptions due to missing source plugins.
      }
    }
    return FALSE;
  }

  /**
   * Count of all pending data items
   *
   * @return
   *   Pending counts
   */
  public function getCountPending() {
    return $this->count_pending;
  }

  /**
   * Count of all translated data items.
   *
   * @return
   *   Translated count
   */
  public function getCountTranslated() {
    return $this->count_translated;
  }

  /**
   * Count of all accepted data items.
   *
   * @return
   *   Accepted count
   */
  public function getCountAccepted() {
    return $this->count_accepted;
  }

  /**
   * Count of all accepted data items.
   *
   * @return
   *   Accepted count
   */
  public function getCountReviewed() {
    return $this->count_reviewed;
  }

  /**
   * Word count of all data items.
   *
   * @return
   *   Word count
   */
  public function getWordCount() {
    return (int)$this->word_count;
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
    if (array_key_exists($state, tmgmt_job_item_states()) && $this->state != $state) {
      $this->state = $state;
      $this->save();
      // If a message is attached to this state change add it now.
      if (!empty($message)) {
        $this->addMessage($message, $variables, $type);
      }
    }
    return $this->state;
  }

  /**
   * Returns the state of the job item. Can be one of the job item state
   * constants.
   *
   * @return integer
   *   The state of the job item.
   */
  public function getState() {
    // We don't need to check if the state is actually set because we always set
    // it in the constructor.
    return $this->state;
  }

  /**
   * Checks whether the passed value matches the current state.
   *
   * @param $state
   *   The value to check the current state against.
   *
   * @return boolean
   *   TRUE if the passed state matches the current state, FALSE otherwise.
   */
  public function isState($state) {
    return $this->getState() == $state;
  }

  /**
   * Checks whether the state of this transaction is 'accepted'.
   *
   * @return boolean
   *   TRUE if the state is 'accepted', FALSE otherwise.
   */
  public function isAccepted() {
    return $this->isState(TMGMT_JOB_ITEM_STATE_ACCEPTED);
  }

  /**
   * Checks whether the state of this transaction is 'active'.
   *
   * @return boolean
   *   TRUE if the state is 'active', FALSE otherwise.
   */
  public function isActive() {
    return $this->isState(TMGMT_JOB_ITEM_STATE_ACTIVE);
  }

  /**
   * Checks whether the state of this transaction is 'needs review'.
   *
   * @return boolean
   *   TRUE if the state is 'needs review', FALSE otherwise.
   */
  public function isNeedsReview() {
    return $this->isState(TMGMT_JOB_ITEM_STATE_REVIEW);
  }

  /**
   * Checks whether the state of this transaction is 'aborted'.
   *
   * @return boolean
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
              '#timestamp' => isset($data['#translation']['#timestamp']) ? $data['#translation']['#timestamp'] : $this->changed,
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
   * @param $key
   *   Key pointing to the item the values should be applied.
   *   The key can be either be an array containing the keys of a nested array
   *   hierarchy path or a string with '][' or '|' as delimiter.
   * @param $values
   *   Nested array of values to set.
   */
  public function updateData($key, $values = array()) {
    foreach ($values as $index => $value) {
      // In order to preserve existing values, we can not aplly the values array
      // at once. We need to apply each containing value on its own.
      // If $value is an array we need to advance the hierarchy level.
      if (is_array($value)) {
        $this->updateData(array_merge(tmgmt_ensure_keys_array($key), array($index)), $value);
      }
      // Apply the value.
      else {
        NestedArray::setValue($this->data, array_merge(tmgmt_ensure_keys_array($key), array($index)), $value);
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
            '!source' => l($this->getSourceLabel(), $this->getSystemPath('edit')),
            '@language' => $this->getJob()->getTargetLanguage()->getName(),
            '!review_url' => url($this->getSystemPath('edit'), array('query' => array('destination' => $job_url))),
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
    $query = new EntityFieldQuery();
    $result = $query->entityCondition('entity_type', 'tmgmt_job_item')
      ->propertyCondition('tjiid', $this->id(), '<>')
      ->propertyCondition('tjid', $this->getJobId())
      ->execute();
    if (!empty($result['tmgmt_job_item'])) {
      return entity_load_multiple('tmgmt_job_item', array_keys($result['tmgmt_job_item']));
    }
    return FALSE;
  }

  /**
   * Returns all job messages attached to this job item with timestamp newer
   * than $time.
   *
   * @param $timestamp
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

    return entity_get_controller('tmgmt_remote')->save($remote_mapping);
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
    if (empty($this->data)) {
      $this->data = $this->getSourceData();
    }

    // Consider everything accepted when the job item is accepted.
    if ($this->isAccepted()) {
      $this->count_pending = 0;
      $this->count_translated = 0;
      $this->count_reviewed = 0;
      $this->count_accepted = count(array_filter(tmgmt_flatten_data($this->data), '_tmgmt_filter_data'));
    }
    // Count the data item states.
    else {
      // Reset counter values.
      $this->count_pending = 0;
      $this->count_translated = 0;
      $this->count_reviewed = 0;
      $this->count_accepted = 0;
      $this->word_count = 0;
      $this->count($this->data);
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
        $this->word_count += tmgmt_word_count($item['#text']);

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
            $this->count_reviewed++;
            break;
          case TMGMT_DATA_ITEM_STATE_TRANSLATED:
            $this->count_translated++;
            break;
          default:
            $this->count_pending++;
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
   * @todo: Remove when http://drupal.org/node/2095399 is in.
   */
  public function getRevisionId() {
    return NULL;
  }

  /**
   * @todo: Remove when http://drupal.org/node/2095399 is in.
   */
  public function isDefaultRevision() {
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function get($property_name) {
    return isset($this->{$property_name}) ? $this->{$property_name} : NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function set($property_name, $value) {
    $this->{$property_name} = $value;
  }

  /**
   * {@inheritdoc}
   */
  public function language() {
    return new Language(array('id' => Language::LANGCODE_NOT_SPECIFIED));
  }

  /**
   * {@inheritdoc}
   */
  public static function postLoad(EntityStorageInterface $storage_controller, array &$entities) {
    parent::postLoad($storage_controller, $entities);
    foreach ($entities as $entity) {
      $entity->data = unserialize($entity->data);
    }
  }

}
