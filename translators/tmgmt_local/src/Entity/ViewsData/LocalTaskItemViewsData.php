<?php
/**
 * @file
 * Contains \Drupal\tmgmt_local\Entity\ViewsData\LocalTaskItemViewsData.
 */

namespace Drupal\tmgmt_local\Entity\ViewsData;

use Drupal\views\EntityViewsData;

/**
 * Provides the views data for the local task item entity type.
 */
class LocalTaskItemViewsData extends EntityViewsData {

  /**
   * {@inheritdoc}
   */
  public function getViewsData() {
    $data = parent::getViewsData();
    $data['tmgmt_local_task_item']['operations'] = array(
      'title' => t('Operations'),
      'help' => t('Displays a list of operations which are available for a task item.'),
      'real field' => 'tltiid',
      'field' => array(
        'handler' => 'tmgmt_local_task_handler_field_item_operations',
      ),
    );
    return $data;
  }

}
