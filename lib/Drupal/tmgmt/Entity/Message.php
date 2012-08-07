<?php

/*
 * @file
 * Contains Drupal\tmgmt\Plugin\Core\Entity\Message.
 */

namespace Drupal\tmgmt\Entity;

use Drupal\Core\Entity\Entity;
use Drupal\Core\Entity\Annotation\EntityType;
use Drupal\Core\Annotation\Translation;
use Drupal\Core\Entity\EntityStorageControllerInterface;

/**
 * Entity class for the tmgmt_message entity.
 *
 * @EntityType(
 *   id = "tmgmt_message",
 *   label = @Translation("Translation Message"),
 *   module = "tmgmt",
 *   controllers = {
 *     "storage" = "Drupal\tmgmt\Entity\Controller\MessageStorageController",
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
   * {@inheritdoc}
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
   * {@inheritdoc}
   */
  public function id() {
    return $this->mid;
  }

  /**
   * {@inheritdoc}
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

  /**
   * {@inheritdoc}
   */
  public static function postLoad(EntityStorageControllerInterface $storage_controller, array &$entities) {
    parent::postLoad($storage_controller, $entities);
    foreach ($entities as $entity) {
      $entity->variables = unserialize($entity->variables);
    }
  }



}
