<?php

/*
 * @file
 * Entity class.
 */

/**
 * Entity class for the local task entity.
 *
 * @ingroup tmgmt_local_task
 */
class TMGMTLocalTask extends Entity {

  /**
   * Translation local task identifier.
   *
   * @var int
   */
  public $tltid;

  /**
   * The user id of the creator of the task.
   *
   * @var int
   */
  public $uid;

  /**
   * The time when the task was created as a timestamp.
   *
   * @var int
   */
  public $created = REQUEST_TIME;

  /**
   * The time when the task was changed as a timestamp.
   *
   * @var int
   */
  public $changed;

  /**
   * A title of this task.
   *
   * @var string
   */
  public $title;

  /**
   * The user id of the assigned translator.
   *
   * @var int
   */
  public $tuid;

  /**
   * Translation job.
   *
   * @var int
   */
  public $tjid;

  /**
   * Current status of the task.
   *
   * @var int
   */
  public $status = TMGMT_LOCAL_TASK_STATUS_UNASSIGNED;

  /**
   * Counter for how many times task was returned to translator.
   *
   * @var int
   */
  public $loop_count;

  /**
   * Overrides Entity::__construct().
   */
  public function __construct(array $values = array(), $entity_type = 'tmgmt_local_task') {
    parent::__construct($values, $entity_type);
  }

  /*
   * Overrides Entity::defaultUri().
   */
  public function defaultUri() {
    return array('path' => 'translate/' . $this->tltid);
  }

  /**
   * Overrides Entity::defaultLabel().
   */
  protected function defaultLabel() {
    if (empty($this->tuid)) {
      if (empty($this->title)) {
        return t('Task for @job', array('@job' => $this->getJob()->label()));
      }
      else {
        return $this->title;
      }
    }
    else {
      if (empty($this->title)) {
        return t('Task for @job assigned to @translator', array('@job' => $this->getJob()->label(), '@translator' =>  entity_label('user', user_load($this->tuid))));
      }
      else {
        return t('@title assigned to @translator', array('@title' => $this->title, '@translator' =>  entity_label('user', user_load($this->tuid))));
      }
    }


  }

  /**
   * Overrides Entity::buildContent().
   */
  public function buildContent($view_mode = 'full', $langcode = NULL) {
    $content = entity_ui_get_form('tmgmt_local_task', $this);
    return entity_get_controller($this->entityType)->buildContent($this, $view_mode, $langcode, $content);
  }

  /**
   * Return the corresponding translation job.
   *
   * @return TMGMTJob
   */
  public function getJob() {
    return tmgmt_job_load($this->tjid);
  }

  /**
   * Assign translation task to passed user.
   *
   * @param object $user
   *   User object.
   */
  public function assign($user) {
    $this->incrementLoopCount(TMGMT_LOCAL_TASK_STATUS_PENDING, $user->uid);
    $this->tuid = $user->uid;
    $this->status = TMGMT_LOCAL_TASK_STATUS_PENDING;
  }

   /**
    * Unassign translation task.
    */
  public function unassign() {
    // We also need to increment loop count when unassigning.
    $this->incrementLoopCount(TMGMT_LOCAL_TASK_STATUS_UNASSIGNED, 0);
    $this->tuid = 0;
    $this->status = TMGMT_LOCAL_TASK_STATUS_UNASSIGNED;
  }

  /**
   * Returns all job items attached to this task.
   *
   * @return array
   *   An array of translation job items.
   */
  public function getItems($conditions = array()) {
    return $this->getJob()->getItems($conditions);
  }

  /**
   * Create a task item for this task and the given job item.
   *
   * @param TMGMTJobItem $job_item
   *   The job item.
   */
  public function addTaskItem(TMGMTJobItem $job_item) {
    // Save the task to get an id.
    if (empty($this->tltid)) {
      $this->save();
    }

    $local_task = entity_create('tmgmt_local_task_item', array(
      'tltid' => $this->identifier(),
      'tjiid' => $job_item->identifier(),
    ));
    $local_task->save();
    return $local_task;
  }

  /**
   * Returns the status of the task. Can be one of the task status constants.
   *
   * @return int
   *   The status of the task or NULL if it hasn't been set yet.
   */
  public function getStatus() {
    return $this->status;
  }

  /**
   * Updates the status of the task.
   *
   * @param $status
   *   The new status of the task. Has to be one of the task status constants.
   * @param $message
   *   (Optional) The log message to be saved along with the status change.
   * @param $variables
   *   (Optional) An array of variables to replace in the message on display.
   *
   * @return int
   *   The updated status of the task if it could be set.
   *
   * @see TMGMTJob::addMessage()
   */
  public function setStatus($status) {
    // Return TRUE if the status could be set. Return FALSE otherwise.
    if (array_key_exists($status, tmgmt_local_task_statuses())) {
      $this->incrementLoopCount($status, $this->tuid);
      $this->status = $status;
      $this->save();
    }
    return $this->status;
  }

  /**
   * Checks whether the passed value matches the current status.
   *
   * @param $status
   *   The value to check the current status against.
   *
   * @return boolean
   *   TRUE if the passed status matches the current status, FALSE otherwise.
   */
  public function isStatus($status) {
    return $this->getStatus() == $status;
  }

  /**
   * Checks whether the user described by $account is the author of this task.
   *
   * @param $account
   *   (Optional) A user object. Defaults to the currently logged in user.
   */
  public function isAuthor($account = NULL) {
    $account = isset($account) ? $account : $GLOBALS['user'];
    return $this->uid == $account->uid;
  }

  /**
   * Returns whether the status of this task is 'unassigned'.
   *
   * @return boolean
   *   TRUE if the status is 'unassigned', FALSE otherwise.
   */
  public function isUnassigned() {
    return $this->isStatus(TMGMT_LOCAL_TASK_STATUS_UNASSIGNED);
  }

  /**
   * Returns whether the status of this task is 'pending'.
   *
   * @return boolean
   *   TRUE if the status is 'pending', FALSE otherwise.
   */
  public function isPending() {
    return $this->isStatus(TMGMT_LOCAL_TASK_STATUS_PENDING);
  }

  /**
   * Returns whether the status of this task is 'completed'.
   *
   * @return boolean
   *   TRUE if the status is 'completed', FALSE otherwise.
   */
  public function isCompleted() {
    return $this->isStatus(TMGMT_LOCAL_TASK_STATUS_COMPLETED);
  }

  /**
   * Returns whether the status of this task is 'rejected'.
   *
   * @return boolean
   *   TRUE if the status is 'rejected', FALSE otherwise.
   */
  public function isRejected() {
    return $this->isStatus(TMGMT_LOCAL_TASK_STATUS_REJECTED);
  }

  /**
   * Returns whether the status of this task is 'closed'.
   *
   * @return boolean
   *   TRUE if the status is 'closed', FALSE otherwise.
   */
  public function isClosed() {
    return $this->isStatus(TMGMT_LOCAL_TASK_STATUS_CLOSED);
  }

  /**
   * Sums up all pending counts of this task job items.
   *
   * @return
   *   The sum of all pending counts
   */
  public function getCountPending() {
    return tmgmt_local_task_statistic($this, 'count_pending');
  }

  /**
   * Sums up all translated counts of this task job items.
   *
   * @return
   *   The sum of all translated counts
   */
  public function getCountTranslated() {
    return tmgmt_local_task_statistic($this, 'count_translated');
  }

  /**
   * Sums up all accepted counts of this task job items.
   *
   * @return
   *   The sum of all accepted counts
   */
  public function getCountAccepted() {
    return tmgmt_local_task_statistic($this, 'count_accepted');
  }

  /**
   * Sums up all word counts of this task job items.
   *
   * @return
   *   The sum of all accepted counts
   */
  public function getWordCount() {
    return tmgmt_local_task_statistic($this, 'word_count');
  }

  /**
   * Returns loop count of a task.
   *
   * @return int
   *   Task loop count.
   */
  public function getLoopCount() {
    return $this->loop_count;
  }

  /**
   * Increment loop_count property depending on current status, new status and
   * new translator.
   *
   * @param int $newStatus
   *   New status of task.
   * @param int $new_tuid
   *   New translator uid.
   */
  public function incrementLoopCount($newStatus, $new_tuid) {
     if ($this->status == TMGMT_LOCAL_TASK_STATUS_PENDING
         && $newStatus == TMGMT_LOCAL_TASK_STATUS_PENDING
         && $this->tuid != $new_tuid) {
      ++$this->loop_count;
    }
    else if ($this->status != TMGMT_LOCAL_TASK_STATUS_UNASSIGNED
             && $newStatus == TMGMT_LOCAL_TASK_STATUS_UNASSIGNED) {
      ++$this->loop_count;
    }
    else if ($this->status != TMGMT_LOCAL_TASK_STATUS_UNASSIGNED
             && $this->status != TMGMT_LOCAL_TASK_STATUS_PENDING
             && $newStatus == TMGMT_LOCAL_TASK_STATUS_PENDING) {
      ++$this->loop_count;
    }
  }

}

/**
 * Entity class for the local task item entity.
 *
 * @ingroup tmgmt_local_task
 */
class TMGMTLocalTaskItem extends Entity {

  /**
   * Translation local task item identifier.
   *
   * @var int
   */
  public $tltiid;

  /**
   * The task identifier.
   *
   * @var int
   */
  public $tltid;

  /**
   * Translation job item.
   *
   * @var int
   */
  public $tjiid;

  /**
   * Current status of the task.
   *
   * @var int
   */
  public $status;

  /**
   * Translated data and data item status.
   *
   * @var array
   */
  public $data = array();

  /**
   * Overrides Entity::__construct().
   */
  public function __construct(array $values = array(), $entity_type = 'tmgmt_local_task_item') {
    parent::__construct($values, $entity_type);
  }

  /*
   * Overrides Entity::defaultUri().
   */
  public function defaultUri() {
    return array('path' => 'translate/' . $this->tltid . '/item/' . $this->tltiid);
  }

  /**
   * Overrides Entity::defaultLabel().
   */
  protected function defaultLabel() {
    if ($job_item = $this->getJobItem()) {
      return $job_item->label();
    }
    return t('Missing job item');
  }

  /**
   * Returns the translation task.
   *
   * @return TMGMTLocalTask
   */
  public function getTask() {
    return entity_load_single('tmgmt_local_task', $this->tltid);
  }

  /**
   * Returns the translation job item.
   *
   * @return TMGMTJobItem
   */
  public function getJobItem() {
    return entity_load_single('tmgmt_job_item', $this->tjiid);
  }

  /**
   * Overrides Entity::buildContent().
   */
  public function buildContent($view_mode = 'full', $langcode = NULL) {
    $content = drupal_get_form('tmgmt_local_translation_form', $this);
    return entity_get_controller($this->entityType)->buildContent($this, $view_mode, $langcode, $content);
  }

  public function isUntranslated() {
    return $this->status == TMGMT_LOCAL_TASK_ITEM_STATUS_UNTRANSLATED;
  }

  /**
   * Sets the task item status to translated.
   */
  public function translated() {
    $this->status = TMGMT_LOCAL_TASK_ITEM_STATUS_TRANSLATED;
  }

  /**
   * Sets the task item status to completed.
   */
  public function completed() {
    $this->status = TMGMT_LOCAL_TASK_ITEM_STATUS_COMPLETED;
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
        drupal_array_set_nested_value($this->data, array_merge(tmgmt_ensure_keys_array($key), array($index)), $value);
      }
    }
  }

  /**
   * Array of translations.
   *
   * The structure is similar to the form API in the way that it is a possibly
   * nested array with the following properties whose presence indicate that the
   * current element is a text that might need to be translated.
   *
   * - #text: The translated text of the corresponding entry in the job item.
   * - #status: The status of the translation.
   *
   * The key can be an alphanumeric string.
   *
   * @param array $key
   *   If present, only the subarray identified by key is returned.
   * @param string $index
   *   Optional index of an attribute below $key.
   *
   * @return array
   *   A structured data array.
   */
  public function getData(array $key = array(), $index = null) {
    if (empty($key)) {
      return $this->data;
    }
    if ($index) {
      $key = array_merge($key, array($index));
    }
    return drupal_array_get_nested_value($this->data, $key);
  }

}
