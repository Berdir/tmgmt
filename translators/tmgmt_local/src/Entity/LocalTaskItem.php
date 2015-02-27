<?php

/*
 * @file
 * Contains \Drupal\tmgmt_local\Entity\LocalTaskItem.
 */

namespace Drupal\tmgmt_local\Entity;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Entity\Annotation\ContentEntityType;
use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Render\Element;


/**
 * Entity class for the local task item entity.
 *
 * @ContentEntityType(
 *   id = "tmgmt_local_task_item",
 *   label = @Translation("Translation Task Item"),
 *   controllers = {
 *     "access" = "Drupal\tmgmt_local\Entity\Controller\LocalTaskItemAccessController",
 *     "form" = {
 *       "edit" = "Drupal\tmgmt_local\Entity\Form\LocalTaskItemFormController"
 *     },
 *   },
 *   base_table = "tmgmt_local_task_item",
 *   entity_keys = {
 *     "id" = "tjiid",
 *     "uuid" = "uuid"
 *   }
 * )
 *
 * @ingroup tmgmt_local_task
 */
class LocalTaskItem extends ContentEntityBase implements EntityChangedInterface {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields['tltiid'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Local Task ID'))
      ->setDescription(t('The Local Task Item ID.'))
      ->setReadOnly(TRUE)
      ->setSetting('unsigned', TRUE);

    $fields['tltid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Local task'))
      ->setDescription(t('The local task.'))
      ->setReadOnly(TRUE)
      ->setSetting('target_type', 'tmgmt_local_task');

    $fields['tjiid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Job Item'))
      ->setDescription(t('The Job Item.'))
      ->setReadOnly(TRUE)
      ->setSetting('target_type', 'tmgmt_job_item');

    $fields['uuid'] = BaseFieldDefinition::create('uuid')
      ->setLabel(t('UUID'))
      ->setDescription(t('The job item UUID.'))
      ->setReadOnly(TRUE);

    $fields['item_type'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Item Type'))
      ->setDescription(t('The item type of this job item.'))
      ->setSettings(array(
        'max_length' => 255,
      ));

    $fields['item_id'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Item ID'))
      ->setDescription(t('The item ID of this job item.'))
      ->setSettings(array(
        'max_length' => 255,
      ));

    $fields['data'] = BaseFieldDefinition::create('string_long')
      ->setLabel(t('Data'))
      ->setDescription(t('The source data'));

    $fields['status'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Local task item status'))
      ->setDescription(t('The local task item status'))
      ->setDefaultValue(TMGMT_LOCAL_TASK_ITEM_STATUS_PENDING);

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the job was last edited.'));

    $fields['count_pending'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Pending count'))
      ->setSetting('unsigned', TRUE);

    $fields['count_translated'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Translated count'))
      ->setSetting('unsigned', TRUE);

    $fields['count_reviewed'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Reviewed count'))
      ->setSetting('unsigned', TRUE);

    $fields['count_accepted'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Accepted count'))
      ->setSetting('unsigned', TRUE);

    $fields['word_count'] = BaseFieldDefinition::create('integer')
      ->setLabel(t('Word count'))
      ->setSetting('unsigned', TRUE);

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getChangedTime() {
    $this->get('changed')->value;
  }

  /**
   * {@inheritdoc}
   */
  protected function defaultLabel() {
    if ($job_item = $this->getJobItem()) {
      return $job_item->label();
    }
    return t('Missing job item');
  }

  /**
   * Returns the translation task.
   *
   * @return LocalTask
   */
  public function getTask() {
    $this->get('tltid')->entity;
  }

  /**
   * Returns the translation job item.
   *
   * @return \Drupal\tmgmt\JobItemInterface
   */
  public function getJobItem() {
    return $this->get('tjiid')->entity;
  }

  /**
   * Returns TRUE if the local task is pending.
   *
   * @return bool
   *   TRUE if the local task item is untranslated.
   */
  public function isPending() {
    return $this->status->value == TMGMT_LOCAL_TASK_ITEM_STATUS_PENDING;
  }

  /**
   * Returns TRUE if the local task is translated (fully translated).
   *
   * @return bool
   *   TRUE if the local task item is translated.
   */
  public function isCompleted() {
    return $this->status->value == TMGMT_LOCAL_TASK_ITEM_STATUS_COMPLETED;
  }

  /**
   * Rreturns TRUE if the local task is closed (translated and accepted).
   *
   * @return bool
   *   TRUE if the local task item is translated and accepted.
   */
  public function isClosed() {
    return $this->status->value == TMGMT_LOCAL_TASK_ITEM_STATUS_CLOSED;
  }

  /**
   * Sets the task item status to completed.
   */
  public function completed() {
    $this->status = TMGMT_LOCAL_TASK_ITEM_STATUS_COMPLETED;
  }

  /**
   * Sets the task item status to closed.
   */
  public function closed() {
    $this->status = TMGMT_LOCAL_TASK_ITEM_STATUS_CLOSED;
  }

  /**
   * Updates the values for a specific substructure in the data array.
   *
   * The values are either set or updated but never deleted.
   *
   * @param $key
   *   Key pointing to the item the values should be applied.
   *   The key can be either be an array containing the keys of a nested array
   *   hierarchy path or a string with '][' or '|' as delimiter.
   * @param $values
   *   Nested array of values to set.
   */
  public function updateData($key, $values = array()) {
    foreach ($values as $index => $value) {
      // In order to preserve existing values, we can not aplly the values array
      // at once. We need to apply each containing value on its own.
      // If $value is an array we need to advance the hierarchy level.
      if (is_array($value)) {
        $this->updateData(array_merge(\Drupal::service('tmgmt.data')->ensureArrayKey($key), array($index)), $value);
      }
      // Apply the value.
      else {
        NestedArray::setValue($this->data, array_merge(\Drupal::service('tmgmt.data')->tmgmt_ensure_keys_array($key), array($index)), $value);
      }
    }
  }

  /**
   * Array of translations.
   *
   * The structure is similar to the form API in the way that it is a possibly
   * nested array with the following properties whose presence indicate that the
   * current element is a text that might need to be translated.
   *
   * - #text: The translated text of the corresponding entry in the job item.
   * - #status: The status of the translation.
   *
   * The key can be an alphanumeric string.
   *
   * @param array $key
   *   If present, only the subarray identified by key is returned.
   * @param string $index
   *   Optional index of an attribute below $key.
   *
   * @return array
   *   A structured data array.
   */
  public function getData(array $key = array(), $index = NULL) {
    if (empty($key)) {
      return $this->data;
    }
    if ($index) {
      $key = array_merge($key, array($index));
    }
    return NestedArray::getValue($this->data, $key);
  }

  /**
   * Count of all translated data items.
   *
   * @return
   *   Translated count
   */
  public function getCountTranslated() {
    return $this->count_translated;
  }

  /**
   * Count of all untranslated data items.
   *
   * @return
   *   Translated count
   */
  public function getCountUntranslated() {
    return $this->count_untranslated;
  }

  /**
   * Count of all completed data items.
   *
   * @return
   *   Translated count
   */
  public function getCountCompleted() {
    return $this->count_completed;
  }


  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage_controller) {
    parent::preSave($storage_controller);
    // @todo Eliminate the need to flatten and unflatten the TaskItem data.
    // Consider everything translated when the job item is translated.
    $data_service = \Drupal::service('tmgmt.data');
    if ($this->isCompleted()) {
      $this->count_untranslated = 0;
      $this->count_translated = count($data_service->flattenData($this->data));
      $this->count_completed = 0;
    }
    // Consider everything completed if the job is completed.
    elseif ($this->isClosed()) {
      $this->count_untranslated = 0;
      $this->count_translated = 0;
      $this->count_completed = count($data_service->flattenData($this->data));
    }
    // Count the data item states.
    else {
      // Start with assuming that all data is untranslated, then go through it
      // and count translated data.
      $this->count_untranslated = count(\Drupal::service('tmgmt.data')->filterTranslatable($this->getData()));
      $this->count_translated = 0;
      $this->count_completed = 0;
      $this->count($this->data);
    }
  }

  /**
   * Parse all data items recursively and sums up the counters for
   * accepted, translated and pending items.
   *
   * @param $item
   *   The current data item.
   * @param $this
   *   The job item the count should be calculated.
   */
  protected function count(&$item) {
    if (!empty($item['#text'])) {
      if (\Drupal::service('tmgmt.data')->filterData($item)) {

        // Set default states if no state is set.
        if (!isset($item['#status'])) {
          $item['#status'] = TMGMT_DATA_ITEM_STATE_UNTRANSLATED;
        }
        switch ($item['#status']) {
          case TMGMT_DATA_ITEM_STATE_TRANSLATED:
            $this->count_untranslated--;
            $this->count_translated++;
            break;
        }
      }
    }
    else {
      foreach (Element::children($item) as $key) {
        $this->count($item[$key]);
      }
    }
  }

}
