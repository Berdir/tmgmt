<?php

/*
 * @file
 * Contains \Drupal\tmgmt_local\Entity\LocalTask.
 */

namespace Drupal\tmgmt_local\Entity;

use Drupal\Core\Entity\Entity;
use Drupal\Core\Entity\EntityStorageControllerInterface;
use Drupal\tmgmt\Entity\JobItem;

/**
 * Entity class for the local task entity.
 *
 * @EntityType(
 *   id = "tmgmt_local_task",
 *   label = @Translation("Translation Task"),
 *   module = "tmgmt_local",
 *   controllers = {
 *     "storage" = "Drupal\Core\Entity\DatabaseStorageController",
 *     "access" = "Drupal\tmgmt_local\Entity\Controller\LocalTaskAccessController",
 *     "form" = {
 *       "edit" = "Drupal\tmgmt_local\Entity\Form\LocalTaskFormController"
 *     }
 *   },
 *   uri_callback = "tmgmt_local_task_uri",
 *   base_table = "tmgmt_local_task",
 *   entity_keys = {
 *     "id" = "tltid",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   }
 * )
 *
 *
 * @ingroup tmgmt_local_task
 */
class LocalTask extends Entity {

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
   * {@inheritdoc}
   */
  public function __construct(array $values = array(), $entity_type = 'tmgmt_local_task') {
    parent::__construct($values, $entity_type);
  }

  /*
   * {@inheritdoc}
   */
  public function defaultUri() {
    return array('path' => 'translate/' . $this->tltid);
  }

  /**
   * {@inheritdoc}
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
        return t('Task for @job assigned to @translator', array('@job' => $this->getJob()->label(), '@translator' => user_load($this->tuid->getUsername())));
      }
      else {
        return t('@title assigned to @translator', array('@title' => $this->title, '@translator' => user_load($this->tuid->getUsername())));
      }
    }


  }


  /**
   * Return the corresponding translation job.
   *
   * @return Job
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
    $this->incrementLoopCount(TMGMT_LOCAL_TASK_STATUS_PENDING, $user->id());
    $this->tuid = $user->id();
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
    $query = \Drupal::entityQuery('tmgmt_loal_task_item');
    $query->condition('tltid', $this->tltid);
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
      return entity_load_multiple('tmgmt_local_task_item', $results);
    }
    return array();
  }

  /**
   * Create a task item for this task and the given job item.
   *
   * @param \Drupal\tmgmt\Entity\JobItem $job_item
   *   The job item.
   */
  public function addTaskItem(JobItem $job_item) {
    // Save the task to get an id.
    if (empty($this->tltid)) {
      $this->save();
    }

    $local_task = entity_create('tmgmt_local_task_item', array(
      'tltid' => $this->id(),
      'tjiid' => $job_item->id(),
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
   * @see Job::addMessage()
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
    return $this->uid == $account->id();
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
   * Count of all translated data items.
   *
   * @return
   *   Translated count
   */
  public function getCountTranslated() {
    return tmgmt_local_task_statistic($this, 'count_translated');
  }

  /**
   * Count of all untranslated data items.
   *
   * @return
   *   Translated count
   */
  public function getCountUntranslated() {
    return tmgmt_local_task_statistic($this, 'count_untranslated');
  }

  /**
   * Count of all completed data items.
   *
   * @return
   *   Translated count
   */
  public function getCountCompleted() {
    return tmgmt_local_task_statistic($this, 'count_completed');
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

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageControllerInterface $storage_controller) {
    parent::preSave($storage_controller);
    $this->changed = REQUEST_TIME;
  }

  /**
   * {@inheritdoc}
   */
  public static function postDelete(EntityStorageControllerInterface $storage_controller, array $entities) {
    parent::postDelete($storage_controller, $entities);
    $ids = \Drupal::entityQuery('tmgmt_local_task_item')
      ->condition('tltid', array_keys($entities))
      ->execute();
    if (!empty($ids)) {
      entity_delete_multiple('tmgmt_local_task_item', $ids);
    }
  }


}
