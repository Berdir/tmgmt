<?php

/**
 * @file
 * Contains Drupal\tmgmt_local\LocalTaskInterface.
 */

namespace Drupal\tmgmt_local;

use Drupal\Core\Entity\ContentEntityInterface;

/**
 * Interface for tmgmt_local_task entity.
 *
 * @ingroup tmgmt_local_task
 */
interface LocalTaskInterface extends ContentEntityInterface {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type);

  /**
   * {@inheritdoc}
   */
  public function getOwner();

  /**
   * {@inheritdoc}
   */
  public function getOwnerId();

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid);

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account);

  /**
   * {@inheritdoc}
   */
  public function getChangedTime();

  /**
   * {@inheritdoc}
   */
  function defaultLabel();

  /**
   * Return the corresponding translation job.
   *
   * @return \Drupal\tmgmt\JobInterface
   */
  public function getJob();

  /**
   * Assign translation task to passed user.
   *
   * @param object $user
   *   User object.
   */
  public function assign($user);

  /**
   * Unassign translation task.
   */
  public function unassign();

  /**
   * Returns all local task items attached to this task.
   *
   * @param array $conditions
   *   Additional conditions.
   *
   * @return \Drupal\tmgmt_local\Entity\LocalTaskItem[]
   *   An array of local task items.
   */
  public function getItems($conditions = array());

  /**
   * Create a task item for this task and the given job item.
   *
   * @param \Drupal\tmgmt\JobItemInterface $job_item
   *   The job item.
   */
  public function addTaskItem(JobItemInterface $job_item);

  /**
   * Returns the status of the task. Can be one of the task status constants.
   *
   * @return int
   *   The status of the task or NULL if it hasn't been set yet.
   */
  public function getStatus();

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
  public function setStatus($status);

  /**
   * Checks whether the passed value matches the current status.
   *
   * @param $status
   *   The value to check the current status against.
   *
   * @return bool
   *   TRUE if the passed status matches the current status, FALSE otherwise.
   */
  public function isStatus($status);

  /**
   * Checks whether the user described by $account is the author of this task.
   *
   * @param $account
   *   (Optional) A user object. Defaults to the currently logged in user.
   */
  public function isAuthor($account = NULL);

  /**
   * Returns whether the status of this task is 'unassigned'.
   *
   * @return bool
   *   TRUE if the status is 'unassigned', FALSE otherwise.
   */
  public function isUnassigned();

  /**
   * Returns whether the status of this task is 'pending'.
   *
   * @return bool
   *   TRUE if the status is 'pending', FALSE otherwise.
   */
  public function isPending();

  /**
   * Returns whether the status of this task is 'completed'.
   *
   * @return bool
   *   TRUE if the status is 'completed', FALSE otherwise.
   */
  public function isCompleted();

  /**
   * Returns whether the status of this task is 'rejected'.
   *
   * @return bool
   *   TRUE if the status is 'rejected', FALSE otherwise.
   */
  public function isRejected();

  /**
   * Returns whether the status of this task is 'closed'.
   *
   * @return bool
   *   TRUE if the status is 'closed', FALSE otherwise.
   */
  public function isClosed();

  /**
   * Count of all translated data items.
   *
   * @return
   *   Translated count
   */
  public function getCountTranslated();

  /**
   * Count of all untranslated data items.
   *
   * @return
   *   Translated count
   */
  public function getCountUntranslated();

  /**
   * Count of all completed data items.
   *
   * @return
   *   Translated count
   */
  public function getCountCompleted();

  /**
   * Sums up all word counts of this task job items.
   *
   * @return
   *   The sum of all accepted counts
   */
  public function getWordCount();


  /**
   * Returns loop count of a task.
   *
   * @return int
   *   Task loop count.
   */
  public function getLoopCount();

  /**
   * Increment loop_count property depending on current status, new status and
   * new translator.
   *
   * @param int $newStatus
   *   New status of task.
   * @param int $new_tuid
   *   New translator uid.
   */
  public function incrementLoopCount($newStatus, $new_tuid);

  /**
   * {@inheritdoc}
   */
  public static function postDelete(EntityStorageInterface $storage_controller, array $entities);

  /**
   * Gets the timestamp of the last entity change across all translations.
   *
   * @return int
   *   The timestamp of the last entity save operation across all
   *   translations.
   */
  public function getChangedTimeAcrossTranslations();
}
