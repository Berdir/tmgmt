<?php

/**
 * @file
 * Contains Drupal\tmgmt\SourcePluginControllerInterface.
 */

namespace Drupal\tmgmt;

use Drupal\tmgmt\Plugin\Core\Entity\JobItem;
use Drupal\Component\Plugin\PluginInspectionInterface;

/**
 * Interface for source plugin controllers.
 *
 * @ingroup tmgmt_source
 */
interface SourcePluginInterface extends PluginInspectionInterface {

  /**
   * Returns an array with the data structured for translation.
   *
   * @param JobItem $job_item
   *   The job item entity.
   *
   * @see JobItem::getData()
   */
  public function getData(JobItem $job_item);

  /**
   * Saves a translation.
   *
   * @param JobItem $job_item
   *   The job item entity.
   *
   * @return boolean
   *   TRUE if the translation was saved successfully, FALSE otherwise.
   */
  public function saveTranslation(JobItem $job_item);

  /**
   * Return a title for this job item.
   *
   * @param JobItem $job_item
   *   The job item entity.
   */
  public function getLabel(JobItem $job_item);

  /**
   * Returns the Uri for this job item.
   *
   * @param JobItem $job_item
   *   The job item entity.
   *
   * @see entity_uri()
   */
  public function getUri(JobItem $job_item);

  /**
   * Returns an array of translatable source item types.
   */
  public function getItemTypes();

  /**
   * Returns the label of a source item type.
   *
   * @param $type
   *   The identifier of a source item type.
   */
  public function getItemTypeLabel($type);

  /**
   * Returns the type of a job item.
   *
   * @param \Drupal\tmgmt\Plugin\Core\Entity\JobItem $job_item
   *   The job item.
   *
   * @return string
   *   A type that describes the job item.
   */
  public function getType(JobItem $job_item);

}
