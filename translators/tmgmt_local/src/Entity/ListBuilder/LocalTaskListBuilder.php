<?php
/**
 * @file
 * Contains \Drupal\tmgmt\Entity\ListBuilder\JobListBuilder.
 */

namespace Drupal\tmgmt_local\Entity\ListBuilder;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Routing\RedirectDestinationTrait;

/**
 * Provides the views data for the message entity type.
 */
class LocalTaskListBuilder extends EntityListBuilder {

  use RedirectDestinationTrait;

  /**
   * {@inheritdoc}
   */
  protected function getDefaultOperations(EntityInterface $entity) {
    $operations = parent::getDefaultOperations($entity);
    /** @var \Drupal\tmgmt_local\Entity\LocalTask $entity */
    if ($entity->access('view')) {
      $operations['view'] = array(
        'title' => $this->t('View'),
        'weight' => -10,
        'url' => $entity->toUrl('canonical')->setOption('query', $this->getDestinationArray()),
      );
    }

    if (\Drupal::currentUser()->hasPermission('administer translation tasks') && tmgmt_local_translation_access($entity) && $entity->getStatus() == TMGMT_LOCAL_TASK_STATUS_UNASSIGNED) {
      $operations['assign'] = array(
        'title' => $this->t('Assign'),
        'weight' => 0,
        'url' => $entity->toUrl('assign'),
      );
    }
    elseif (tmgmt_local_translation_access($entity) && $entity->getStatus() == TMGMT_LOCAL_TASK_STATUS_UNASSIGNED) {
      $operations['assign_to_me'] = array(
        'title' => $this->t('Assign to me'),
        'weight' => 0,
        'url' => $entity->toUrl('assign'),
      );
    }
    if ($entity->getStatus() != TMGMT_LOCAL_TASK_STATUS_UNASSIGNED && $entity->access('unassign')) {
      $operations['unassign'] = array(
        'title' => $this->t('Unassign'),
        'weight' => 0,
        'url' => $entity->toUrl('unassign'),
      );
    }
    if ($entity->access('delete')) {
      $operations['delete'] = array(
        'title' => $this->t('Delete'),
        'weight' => 10,
        'url' => $entity->toUrl('delete'),
      );
    }
    return $operations;
  }

}
