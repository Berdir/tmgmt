<?php

/*
 * @file
 * Contains Drupal\tmgmt\Plugin\Core\Entity\RemoteMapping.
 */

namespace Drupal\tmgmt\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\FieldDefinition;

/**
 * Entity class for the tmgmt_remote entity.
 *
 * @ContentEntityType(
 *   id = "tmgmt_remote",
 *   label = @Translation("Translation Remote Mapping"),
 *   base_table = "tmgmt_remote",
 *   entity_keys = {
 *     "id" = "trid",
 *     "uuid" = "uuid"
 *   }
 * )
 *
 * @ingroup tmgmt_job
 */
class RemoteMapping extends ContentEntityBase {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields['trid'] = FieldDefinition::create('integer')
      ->setLabel(t('Remote mapping ID'))
      ->setReadOnly(TRUE);
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
    $fields['data_item_key'] = FieldDefinition::create('string')
      ->setLabel(t('Data Item Key'));
    $fields['remote_identifier_1'] = FieldDefinition::create('string')
      ->setLabel(t('Remote identifier 1'));
    $fields['remote_identifier_2'] = FieldDefinition::create('string')
      ->setLabel(t('Remote identifier 2'));
    $fields['remote_identifier_3'] = FieldDefinition::create('string')
      ->setLabel(t('Remote identifier 3'));
    $fields['remote_url'] = FieldDefinition::create('uri')
      ->setLabel(t('Remote URL'));
    $fields['word_count'] = FieldDefinition::create('integer')
      ->setLabel(t('Word count'))
      ->setDescription(t('Word count provided by the remote service.'));
    $fields['amount'] = FieldDefinition::create('integer')
      ->setLabel(t('Amount'))
      ->setDescription(t('Amount charged for the remote translation job.'));
    $fields['currency'] = FieldDefinition::create('string')
      ->setLabel(t('Currency'));
    $fields['remote_data'] = FieldDefinition::create('map')
      ->setLabel(t('Remote data'));
    return $fields;
  }

  public function getJobId() {
    return $this->get('tjid')->target_id;
  }

  /**
   * Gets translation job.
   *
   * @return \Drupal\tmgmt\Entity\Job
   */
  function getJob() {
    return $this->get('tjid')->entity;
  }

  /**
   * Gets translation job item.
   *
   * @return \Drupal\tmgmt\Entity\JobItem
   */
  function getJobItem() {
    return $this->get('tjiid')->entity;
  }

  /**
   * Adds data to the remote_data storage.
   *
   * @param string $key
   *   Key through which the data will be accessible.
   * @param $value
   *   Value to store.
   */
  function addRemoteData($key, $value) {
    $this->remote_data->$key = $value;
  }

  /**
   * Gets data from remote_data storage.
   *
   * @param string $key
   *   Access key for the data.
   *
   * @return mixed
   *   Stored data.
   */
  function getRemoteData($key) {
    return $this->remote_data->$key;
  }

  /**
   * Removes data from remote_data storage.
   *
   * @param string $key
   *   Access key for the data that are to be removed.
   */
  function removeRemoteData($key) {
    unset($this->remote_data->$key);
  }

  /**
   * Returns the amount.
   *
   * @return int
   */
  function getAmount() {
    return $this->get('amount')->value;
  }

  /**
   * Returns the currency.
   *
   * @return int
   */
  function getCurrency() {
    return $this->get('currency')->value;
  }

  /**
   * Returns the remote identifier 1.
   *
   * @return string
   */
  function getRemoteIdentifier1() {
    return $this->get('remote_identifier_1')->value;
  }

  /**
   * Returns the remote identifier 2.
   *
   * @return string
   */
  function getRemoteIdentifier2() {
    return $this->get('remote_identifier_1')->value;
  }

  /**
   * Returns the remote identifier 3.
   *
   * @return string
   */
  function getRemoteIdentifier3() {
    return $this->get('remote_identifier_3')->value;
  }

  /**
   * Loads remote mappings based on local data.
   *
   * @param int $tjid
   *   Translation job id.
   * @param int $tjiid
   *   Translation job item id.
   * @param int $data_item_key
   *   Data item key.
   *
   * @return static[]
   *   Array of TMGMTRemote entities.
   */
  static public function loadByLocalData($tjid = NULL, $tjiid = NULL, $data_item_key = NULL) {
    $data_item_key = tmgmt_ensure_keys_string($data_item_key);

    $query = \Drupal::entityQuery('tmgmt_remote');
    if (!empty($tjid)) {
      $query->condition('tjid', $tjid);
    }
    if (!empty($tjiid)) {
      $query->condition('tjiid', $tjiid);
    }
    if (!empty($data_item_key)) {
      $query->condition('data_item_key', $data_item_key);
    }

    $trids = $query->execute();
    if (!empty($trids)) {
      return static::loadMultiple($trids);
    }

    return array();
  }

  /**
   * Loads remote mapping entities based on remote identifier.
   *
   * @param int $remote_identifier_1
   * @param int $remote_identifier_2
   * @param int $remote_identifier_3
   *
   * @return static[]
   *   Array of TMGMTRemote entities.
   */
  static public function loadByRemoteIdentifier($remote_identifier_1 = NULL, $remote_identifier_2 = NULL, $remote_identifier_3 = NULL) {
    $query = \Drupal::entityQuery('tmgmt_remote');
    if ($remote_identifier_1 !== NULL) {
      $query->condition('remote_identifier_1', $remote_identifier_1);
    }
    if ($remote_identifier_2 !== NULL) {
      $query->condition('remote_identifier_2', $remote_identifier_2);
    }
    if ($remote_identifier_3 !== NULL) {
      $query->condition('remote_identifier_3', $remote_identifier_3);
    }
    $trids = $query->execute();
    if (!empty($trids)) {
      return static::loadMultiple($trids);
    }

    return array();
  }

}
