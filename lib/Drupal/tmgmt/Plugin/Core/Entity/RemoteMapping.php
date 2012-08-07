<?php

/*
 * @file
 * Contains Drupal\tmgmt\Plugin\Core\Entity\RemoteMapping.
 */

namespace Drupal\tmgmt\Plugin\Core\Entity;

use Drupal\Core\Entity\Entity;
use Drupal\Core\Entity\Annotation\EntityType;
use Drupal\Core\Annotation\Translation;

/**
 * Entity class for the tmgmt_remote entity.
 *
 * @EntityType(
 *   id = "tmgmt_remote",
 *   label = @Translation("Translation Remote Mapping"),
 *   module = "tmgmt",
 *   controllers = {
 *     "storage" = "Drupal\tmgmt\EntityController\RemoteMappingStorageController",
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
   * TMGMTJob identifier.
   *
   * @var int
   */
  public $tjid;

  /**
   * TMGMTJobItem identifier.
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
   * Custom remote data.
   *
   * @var array
   */
  public $remote_data;

  /**
   * Overrides \Drupal\Core\Entity\Entiy::id().
   */
  public function id() {
    return $this->trid;
  }


  /**
   * Gets translation job.
   *
   * @return TMGMTJob
   */
  function getJob() {
    return tmgmt_job_load($this->tjid);
  }

  /**
   * Gets translation job item.
   *
   * @return TMGMTJobItem
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

}
