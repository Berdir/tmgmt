<?php

/*
 * @file
 * Contains Drupal\tmgmt\Plugin\Core\Entity\Message.
 */

namespace Drupal\tmgmt\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\FieldDefinition;

/**
 * Entity class for the tmgmt_message entity.
 *
 * @ContentEntityType(
 *   id = "tmgmt_message",
 *   label = @Translation("Translation Message"),
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
class Message extends ContentEntityBase {
  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields['mid'] = FieldDefinition::create('integer')
      ->setLabel('Message ID')
      ->setReadOnly(TRUE);;
    $fields['uuid'] = FieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The node UUID.'))
      ->setReadOnly(TRUE);
    $fields['tjid'] = FieldDefinition::create('entity_reference')
      ->setLabel(t('Job reference'))
      ->setSetting('target_type', 'tmgmt_job');
    $fields['tjiid'] = FieldDefinition::create('entity_reference')
      ->setLabel(t('Job item reference'))
      ->setSetting('target_type', 'tmgmt_job_item');
    $fields['message'] = FieldDefinition::create('string')
      ->setLabel(t('Message'));
    $fields['variables'] = FieldDefinition::create('map')
      ->setLabel(t('Variables'));
    $fields['created'] = FieldDefinition::create('created')
      ->setLabel('Created time');
    $fields['type'] = FieldDefinition::create('string')
      ->setLabel('Message type')
      ->setSetting('default_value', 'status');
    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultLabel() {
    $created = format_date($this->created->value);
    switch ($this->type->value) {
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
    $text = $this->message->value;
    if ($this->variables->first()->toArray()) {
      $text = t($text, $this->variables->first()->toArray());
    }
    return $text;
  }

  /**
   * Loads the job entity that this job message is attached to.
   *
   * @return \Drupal\tmgmt\Entity\Job
   *   The job entity that this job message is attached to or FALSE if there was
   *   a problem.
   */
  public function getJob() {
    return $this->get('tjid')->entity;
  }

  /**
   * Loads the job entity that this job message is attached to.
   *
   * @return \Drupal\tmgmt\Entity\JobItem
   *   The job item entity that this job message is attached to or FALSE if
   *   there was a problem.
   */
  public function getJobItem() {
    return $this->get('tjid')->entity;
  }

}
