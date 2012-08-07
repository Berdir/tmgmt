<?php

/*
 * @file
 * Contains Drupal\tmgmt\Plugin\Core\Entity\RemoteMapping.
 */

namespace Drupal\tmgmt\Entity;

use Drupal\Core\Entity\Entity;
use Drupal\Core\Entity\Annotation\EntityType;
use Drupal\Core\Annotation\Translation;
use Drupal\Core\Entity\EntityStorageControllerInterface;

/**
 * Entity class for the tmgmt_remote entity.
 *
 * @EntityType(
 *   id = "tmgmt_remote",
 *   label = @Translation("Translation Remote Mapping"),
 *   module = "tmgmt",
 *   controllers = {
 *     "storage" = "Drupal\tmgmt\Entity\Controller\RemoteMappingStorageController",
 *   },
 *   base_table = "tmgmt_remote",
 *   entity_keys = {
 *     "id" = "trid",
 *     "uuid" = "uuid"
 *   }
 * )
 *
 * @ingroup tmgmt_job
 */
class RemoteMapping extends Entity {

  /**
   * Primary key.
   *
   * @var int
   */
  public $trid;

  /**
   * Job identifier.
   *
   * @var int
   */
  public $tjid;

  /**
   * Job item identifier.
   *
   * @var int
   */
  public $tjiid;

  /**
   * Translation job data item key.
   *
   * @var string
   */
  public $data_item_key;

  /**
   * Custom remote identifier 1.
   *
   * @var string
   */
  public $remote_identifier_1;

  /**
   * Custom remote identifier 2.
   *
   * @var string
   */
  public $remote_identifier_2;

  /**
   * Custom remote identifier 3.
   *
   * @var string
   */
  public $remote_identifier_3;

  /**
   * Remote job url.
   *
   * @var string
   */
  public $remote_url;

  /**
   * Word count provided by the remote service.
   *
   * @var int
   */
  public $word_count;

  /**
   * Amount charged for the remote translation job.
   *
   * @var int
   */
  public $amount;

  /**
   * Amount charged currency.
   *
   * @var string
   */
  public $currency;

  /**
   * Custom remote data.
   *
   * @var array
   */
  public $remote_data = array();

  /**
   * Overrides \Drupal\Core\Entity\Entiy::id().
   */
  public function id() {
    return $this->trid;
  }


  /**
   * Gets translation job.
   *
   * @return \Drupal\tmgmt\Entity\Job
   */
  function getJob() {
    return tmgmt_job_load($this->tjid);
  }

  /**
   * Gets translation job item.
   *
   * @return \Drupal\tmgmt\Entity\JobItem
   */
  function getJobItem() {
    if (!empty($this->tjiid)) {
      return tmgmt_job_item_load($this->tjiid);
    }
    return NULL;
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
    $this->remote_data[$key] = $value;
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
    return $this->remote_data[$key];
  }

  /**
   * Removes data from remote_data storage.
   *
   * @param string $key
   *   Access key for the data that are to be removed.
   */
  function removeRemoteData($key) {
    unset($this->remote_data[$key]);
  }

  /**
   * {@inheritdoc}
   */
  public static function postLoad(EntityStorageControllerInterface $storage_controller, array &$entities) {
    parent::postLoad($storage_controller, $entities);
    foreach ($entities as $entity) {
      $entity->remote_data = unserialize($entity->remote_data);
    }
  }

}
