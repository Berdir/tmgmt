<?php

/*
 * @file
 * Contains \Drupal\tmgmt_local\Entity\LocalTask.
 */

namespace Drupal\tmgmt_local\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\tmgmt\JobItemInterface;
use Drupal\user\EntityOwnerInterface;
use Drupal\user\UserInterface;
use Drupal\user\Entity\User;

/**
 * Entity class for the local task entity.
 *
 * @ContentEntityType(
 *   id = "tmgmt_local_task",
 *   label = @Translation("Translation Task"),
 *   controllers = {
 *     "access" = "Drupal\tmgmt_local\Entity\Controller\LocalTaskAccessController",
 *     "form" = {
 *       "edit" = "Drupal\tmgmt_local\Entity\Form\LocalTaskFormController"
 *     }
 *   },
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
class LocalTask extends ContentEntityBase implements EntityChangedInterface, EntityOwnerInterface {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields['tltid'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Local task ID'))
      ->setDescription(t('The local task ID.'))
      ->setReadOnly(TRUE)
      ->setSetting('unsigned', TRUE);

    $fields['tjid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Job'))
      ->setDescription(t('The Job for this task.'))
      ->setReadOnly(TRUE)
      ->setSetting('target_type', 'tmgmt_job')
      ->setDefaultValue(0);

    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The node UUID.'))
      ->setReadOnly(TRUE);

    $fields['title'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Title'))
      ->setDescription(t('The title of this local task.'))
      ->setDefaultValue('')
      ->setSettings(array(
        'max_length' => 255,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'string',
        'weight' => -5,
      ))
      ->setDisplayConfigurable('form', TRUE);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Owner'))
      ->setDescription(t('The user that created the local task.'))
      ->setSettings(array(
        'target_type' => 'user',
      ))
      ->setDefaultValue(0);

    $fields['tuid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Assigned translator'))
      ->setDescription(t('The translator assigned to this task.'))
      ->setSettings(array(
        'target_type' => 'user',
      ))
      ->setDefaultValue(0);

    $fields['status'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Local task status'))
      ->setDescription(t('The local task status.'))
      ->setDefaultValue(TMGMT_LOCAL_TASK_STATUS_UNASSIGNED);

    $fields['loop_count'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Loop count'))
      ->setDescription(t('Counter for how many times task was returned to translator.'))
      ->setDefaultValue(TMGMT_LOCAL_TASK_STATUS_UNASSIGNED);

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the job was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the job was last edited.'));
    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('uid')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('uid')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('uid', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('uid', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getChangedTime() {
    return $this->get('changed')->value;
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
        return t('Task for @job assigned to @translator', array('@job' => $this->getJob()->label(), '@translator' => User::load($this->tuid->getUsername())));
      }
      else {
        return t('@title assigned to @translator', array('@title' => $this->title, '@translator' => User::load($this->tuid->getUsername())));
      }
    }
  }


  /**
   * Return the corresponding translation job.
   *
   * @return \Drupal\tmgmt\JobInterface
   */
  public function getJob() {
    return $this->get('tjid')->entity;
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
   * Returns all local task items attached to this task.
   *
   * @param array $conditions
   *   Additional conditions.
   *
   * @return \Drupal\tmgmt_local\Entity\LocalTaskItem[]
   *   An array of local task items.
   */
  public function getItems($conditions = array()) {
    $query = \Drupal::entityQuery('tmgmt_loal_task_item');
    $query->condition('tltid', $this->id());
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
   * @param \Drupal\tmgmt\JobItemInterface $job_item
   *   The job item.
   */
  public function addTaskItem(JobItemInterface $job_item) {
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
    return $this->status->value;
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
      $this->incrementLoopCount($status, $this->tuid->target_id);
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
   * @return bool
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
    $account = isset($account) ? $account : \Drupal::currentUser();
    return $this->getOwnerId() == $account->id();
  }

  /**
   * Returns whether the status of this task is 'unassigned'.
   *
   * @return bool
   *   TRUE if the status is 'unassigned', FALSE otherwise.
   */
  public function isUnassigned() {
    return $this->isStatus(TMGMT_LOCAL_TASK_STATUS_UNASSIGNED);
  }

  /**
   * Returns whether the status of this task is 'pending'.
   *
   * @return bool
   *   TRUE if the status is 'pending', FALSE otherwise.
   */
  public function isPending() {
    return $this->isStatus(TMGMT_LOCAL_TASK_STATUS_PENDING);
  }

  /**
   * Returns whether the status of this task is 'completed'.
   *
   * @return bool
   *   TRUE if the status is 'completed', FALSE otherwise.
   */
  public function isCompleted() {
    return $this->isStatus(TMGMT_LOCAL_TASK_STATUS_COMPLETED);
  }

  /**
   * Returns whether the status of this task is 'rejected'.
   *
   * @return bool
   *   TRUE if the status is 'rejected', FALSE otherwise.
   */
  public function isRejected() {
    return $this->isStatus(TMGMT_LOCAL_TASK_STATUS_REJECTED);
  }

  /**
   * Returns whether the status of this task is 'closed'.
   *
   * @return bool
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
    return $this->loop_count->value;
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
     if ($this->getStatus() == TMGMT_LOCAL_TASK_STATUS_PENDING
         && $newStatus == TMGMT_LOCAL_TASK_STATUS_PENDING
         && $this->tuid->target_id != $new_tuid) {
      ++$this->loop_count->value;
    }
    else if ($this->getStatus() != TMGMT_LOCAL_TASK_STATUS_UNASSIGNED
             && $newStatus == TMGMT_LOCAL_TASK_STATUS_UNASSIGNED) {
      ++$this->loop_count->value;
    }
    else if ($this->getStatus() != TMGMT_LOCAL_TASK_STATUS_UNASSIGNED
             && $this->getStatus() != TMGMT_LOCAL_TASK_STATUS_PENDING
             && $newStatus == TMGMT_LOCAL_TASK_STATUS_PENDING) {
      ++$this->loop_count->value;
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function postDelete(EntityStorageInterface $storage_controller, array $entities) {
    parent::postDelete($storage_controller, $entities);
    $ids = \Drupal::entityQuery('tmgmt_local_task_item')
      ->condition('tltid', array_keys($entities), 'IN')
      ->execute();
    if (!empty($ids)) {
      entity_delete_multiple('tmgmt_local_task_item', $ids);
    }
  }

}
