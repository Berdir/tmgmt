<?php

/**
 * @file
 * Contains \Drupal\tmgmt_local\Plugin\views\field\TaskOperations.
 */

namespace Drupal\tmgmt_local\Plugin\views\field;

use Drupal\Core\Routing\RedirectDestinationTrait;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Field handler which shows the progress of a job or job item.
 *
 * @ViewsField("tmgmt_local_task_operations")
 */
class TaskOperations extends FieldPluginBase {

  use RedirectDestinationTrait;

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    /** @var \Drupal\tmgmt_local\Entity\LocalTask $task */
    $task = $values->_entity;

    $element = array();
    $element['#theme'] = 'links';
    $element['#attributes'] = array('class' => array('links', 'inline'));
    if ($task->access('view')) {
      $element['#links']['view'] = array(
        'query' => $this->getDestinationArray(),
        'title' => t('View'),
      ) + $task->toUrl()->toRenderArray();
    }
    if (\Drupal::currentUser()->hasPermission('administer translation tasks') && tmgmt_local_translation_access($task) && empty($task->tuid)) {
      $element['#links']['assign'] = array(
        'href' => 'manage-translate/assign-tasks/' . $task->id(),
        'query' => $this->getDestinationArray(),
        'attributes' => array(
          'title' => t('Assign'),
        ),
        'title' => t('assign'),
      );
    }
    elseif (tmgmt_local_translation_access($task) && empty($task->tuid)) {
      $element['#links']['assign_to_me'] = array(
        'href' => 'translate/' . $task->id() . '/assign-to-me',
        'query' => $this->getDestinationArray(),
        'attributes' => array(
          'title' => t('Assign to me'),
        ),
        'title' => t('assign'),
      );
    }
    elseif (tmgmt_local_translation_access($task) && empty($task->tuid)) {
      $element['#links']['assign_to_me'] = array(
        'href' => 'translate/' . $task->id() . '/assign-to-me',
        'query' => $this->getDestinationArray(),
        'attributes' => array(
          'title' => t('Assign to me'),
        ),
        'title' => t('assign'),
      );
    }
    if (!empty($task->tuid) && $task->access('unassign')) {
      $element['#links']['unassign'] = array(
        'href' => 'translate/' . $task->id() . '/unassign',
        'query' => $this->getDestinationArray(),
        'attributes' => array(
          'title' => t('Unassign'),
        ),
        'title' => t('unassign'),
      );
    }
    if ($task->access('delete')) {
      $element['#links']['delete'] = array(
        'route_name' => 'tmgmt_local.local_task_delete',
        'route_parameters' => array('tmgmt_local_task' => $task->id()),
        'query' => $this->getDestinationArray(),
        'title' => t('delete'),
      );
    }
    return $element;
  }

}
