<?php

/**
 * @file
 * Contains Drupal\tmgmt\Entity\Controller\RemoteMappingStorageController.
 */

namespace Drupal\tmgmt\Entity\Controller;

use Drupal\Core\Entity\DatabaseStorageController;
use Drupal\Core\Entity\EntityInterface;

/**
 * Controller class for the job entity.
 *
 * @ingroup tmgmt_job
 */
class RemoteMappingStorageController extends DatabaseStorageController {

  public function attachLoad(&$queried_entities, $load_revision = FALSE) {
    parent::attachLoad($queried_entities, $load_revision);
    foreach ($queried_entities as $entity) {
      if (is_string($entity->remote_data)) {
        $entity->remote_data = unserialize($entity->remote_data);
      }
    }
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
   * @return array
   *   Array of TMGMTRemote entities.
   */
  function loadByLocalData($tjid = NULL, $tjiid = NULL, $data_item_key = NULL) {
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
      return entity_load_multiple('tmgmt_remote', $trids);
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
   * @return array
   *   Array of TMGMTRemote entities.
   */
  function loadByRemoteIdentifier($remote_identifier_1 = NULL, $remote_identifier_2 = NULL, $remote_identifier_3 = NULL) {
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
      return entity_load_multiple('tmgmt_remote', $trids);
    }

    return array();
  }

}
