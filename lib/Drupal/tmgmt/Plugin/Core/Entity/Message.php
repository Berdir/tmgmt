<?php

/*
 * @file
 * Contains Drupal\tmgmt\Plugin\Core\Entity\Message.
 */

namespace Drupal\tmgmt\Plugin\Core\Entity;

use Drupal\Core\Entity\Entity;
use Drupal\Core\Entity\Annotation\EntityType;
use Drupal\Core\Annotation\Translation;

/**
 * Entity class for the tmgmt_message entity.
 *
 * @EntityType(
 *   id = "tmgmt_message",
 *   label = @Translation("Translation Message"),
 *   module = "tmgmt",
 *   controllers = {
 *     "storage" = "Drupal\Core\Entity\DatabaseStorageController",
 *   },
 *   uri_callback = "tmgmt_message_uri",
 *   base_table = "tmgmt_message",
 *   entity_keys = {
 *     "id" = "mid",
 *     "uuid" = "uuid"
 *   }
 * )
 *
 * @ingroup tmgmt_job
 */
class Message extends Entity {

  /**
   * The ID of the message..
   *
   * @var integer
   */
  public $mid;

  /**
   * The UUID of the message.
   *
   * @var string
   */
  public $uuid;

  /**
   * The ID of the job.
   *
   * @var integer
   */
  public $tjid;

  /**
   * The ID of the job item.
   *
   * @var integer
   */
  public $tjiid;

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
   * The time when the message object was created as a timestamp.
   *
   * @var integer
   */
  public $created;

  /**
   * Type of the message (debug, status, warning or error).
   *
   * @var string
   */
  public $type;

  /**
   * Overrides Entity::__construct().
   */
  public function __construct(array $values = array(), $type = 'tmgmt_message') {
    parent::__construct($values, $type);
    if (empty($this->created)) {
      $this->created = REQUEST_TIME;
    }
    if (empty($this->type)) {
      $this->type = 'status';
    }
  }

  /**
   * Overrides Drupal\entity\Entity::id().
   */
  public function id() {
    return $this->mid;
  }

  /**
   * Overrides Entity::label().
   */
  public function defaultLabel() {
    $created = format_date($this->created);
    switch ($this->type) {
      case 'error':
        return t('Error message from @time', array('@time' => $created));
      case 'status':
        return t('Status message from @time', array('@time' => $created));
      case 'warning':
        return t('Warning message from @time', array('@time' => $created));
      case 'debug':
        return t('Debug message from @time', array('@time' => $created));
    }
  }

  /**
   * Returns the translated message.
   *
   * @return
   *   The translated message.
   */
  public function getMessage() {
    $text = $this->message;
    if (is_array($this->variables) && !empty($this->variables)) {
      $text = t($text, $this->variables);
    }
    return $text;
  }

  /**
   * Loads the job entity that this job message is attached to.
   *
   * @return Job
   *   The job entity that this job message is attached to or FALSE if there was
   *   a problem.
   */
  public function getJob() {
    if (!empty($this->tjid)) {
      return tmgmt_job_load($this->tjid);
    }
    return FALSE;
  }

  /**
   * Loads the job entity that this job message is attached to.
   *
   * @return JobItem
   *   The job item entity that this job message is attached to or FALSE if
   *   there was a problem.
   */
  public function getJobItem() {
    if (!empty($this->tjiid)) {
      return tmgmt_job_item_load($this->tjiid);
    }
    return FALSE;
  }

}
