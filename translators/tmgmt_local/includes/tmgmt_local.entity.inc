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
   * @var integer
   */
  public $tltid;

  /**
   * The user id of the creator of the task.
   *
   * @var integer
   */
  public $uid;

  /**
   * The time when the task was created as a timestamp.
   *
   * @var integer
   */
  public $created;

  /**
   * The time when the task was changed as a timestamp.
   *
   * @var integer
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
   * @var integer
   */
  public $tuid;

  /**
   * Translation local task job.
   *
   * @var integer
   */
  public $tjid;

  /**
   * Current status of the task.
   *
   * @var int
   */
  public $status;

  /**
   * Couneter for how many times task was returned to translator.
   *
   * @var int
   */
  public $loop_count;

  /**
   * Overrides Entity::__construct().
   */
  public function __construct(array $values = array()) {
    parent::__construct($values, 'tmgmt_local_task');
    if (empty($this->tltid)) {
      $this->created = REQUEST_TIME;
    }
    if (!isset($this->state)) {
      $this->state = TMGMT_LOCAL_TASK_STATUS_UNASSIGNED;
    }
  }

  /**
   * Overrides Entity::defaultUri().
   */
  public function defaultUri() {
    return array('path' => 'admin/config/regional/tmgmt/local-tasks/' . $this->tltid);
  }

  /**
   * Overrides Entity::defaultLabel().
   *
   * Defines the entity label if the 'entity_class_label' callback is used.
   * Specify 'entity_class_label' as 'label callback' in hook_entity_info() to
   * let the entity label point to this method. Override this in order to
   * implement a custom default label.
   */
  protected function defaultLabel() {
    $wrapper = entity_metadata_wrapper($this->entityType, $this);
    return t('Task for @job assigned to @translator', array('@job' => $wrapper->job->label(), '@translator' => $wrapper->translator->name->value()));
  }

  /**
   * Overrides Entity::buildContent().
   */
  public function buildContent($view_mode = 'full', $langcode = NULL) {
    $content = entity_ui_get_form('tmgmt_local_task', $this);
    return entity_get_controller($this->entityType)->buildContent($this, $view_mode, $langcode, $content);
  }

  /**
   * Return local task translation job.
   *
   * @return TMGMTJob
   */
  public function getJob() {
    $wrapper = entity_metadata_wrapper('tmgmt_local_task', $this);
    return $wrapper->job->value();
  }

  /**
   * Adds an item to the translation local task.
   *
   * @param $plugin
   *   The plugin name.
   * @param $item_type
   *   The source item type.
   * @param $item_id
   *   The source item id.
   *
   * @return TMGMTJobItem
   *   The job item that was added to the job or FALSE if it couldn't be saved.
   * @throws TMGMTException
   *   On zero item word count.
   */
  public function addItem($plugin, $item_type, $item_id) {

    $transaction = db_transaction();
    $is_new = FALSE;

    if (empty($this->tltid)) {
      $this->save();
      $is_new = TRUE;
    }

    // @todo - not done for now, do it when we figure UI for adding individual
    // items to task.

    throw new Exception('addItem not yet implemented');

    $item = tmgmt_job_item_create($plugin, $item_type, $item_id, array('tjid' => $this->tjid));
    // Initialize job item data variable needed to determine word count
    // in job item getWordCount().
    $item->getData();
    $item->save();

    if ($item->getWordCount() == 0) {
      $transaction->rollback();

      // In case we got word count 0 for the first job item, NULL tjid so that
      // if there is another addItem() call the rolled back job object will get
      // persisted.
      if ($is_new) {
        $this->tjid = NULL;
      }

      throw new TMGMTException('Created job item with word count 0. Plugin: @plugin | Item type: @item_type | Item id: @item_id',
        array('@plugin' => $plugin, '@item_type' => $item_type, '@item_id' => $item_id));
    }

    return $item;
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
   * Returns all job items attached to this job.
   *
   * @return array
   *   An array of translation job items.
   */
  public function getItems($conditions = array()) {
    // @todo - use connecting table for this tmgmt_local_task_job_item.
    throw new Exception('not yet done.');

    $query = new EntityFieldQuery();
    $query->entityCondition('entity_type', 'tmgmt_job_item');
    $query->propertyCondition('tltid', $this->tltid);
    foreach ($conditions as $key => $condition) {
      if (is_array($condition)) {
        $operator = isset($condition['operator']) ? $condition['operator'] : '=';
        $query->propertyCondition($key, $condition['value'], $operator);
      }
      else {
        $query->propertyCondition($key, $condition);
      }
    }
    $results = $query->execute();
    if (!empty($results['tmgmt_job_item'])) {
      return entity_load('tmgmt_job_item', array_keys($results['tmgmt_job_item']));
    }
    return array();
  }

  /**
   * Returns the status of the task. Can be one of the task status constants.
   *
   * @return integer
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
   * Returns the source data of all job items.
   *
   * @param $key
   *   If present, only the subarray identified by key is returned.
   * @param $index
   *   Optional index of an attribute below $key.
   * @return array
   *   A nested array with the source data where the most upper key is the job
   *   item id.
   */
  public function getData(array $key = array(), $index = null) {
    // @todo - query here our new table that we still didn't implement
    // so pull job items from that table and not from job
    throw new Exception('not yet implemented');
    $data = array();
    if (!empty($key)) {
      $tjiid = array_shift($key);
      $job_item = entity_load_single('tmgmt_job_item', $tjiid);
      if ($job_item) {
        $data[$tjiid] = $job_item->getData($key, $index);
      }
    }
    else {
      foreach ($this->getItems() as $key => $item) {
        $data[$key] = $item->getData();
      }
    }
    return $data;
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
   * Sums up all approved counts of this task job items.
   *
   * @return
   *   The sum of all approved counts
   */
  public function getCountApproved() {
    return tmgmt_local_task_statistic($this, 'count_approved');
  }

  /**
   * Sums up all word counts of this task job items.
   *
   * @return
   *   The sum of all approved counts
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
