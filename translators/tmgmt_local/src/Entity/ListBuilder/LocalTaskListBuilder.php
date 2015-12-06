<?php
/**
 * @file
 * Contains \Drupal\tmgmt\Entity\ListBuilder\JobListBuilder.
 */

namespace Drupal\tmgmt_local\Entity\ListBuilder;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Routing\RedirectDestinationTrait;
use Drupal\Core\Url;

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

    if (\Drupal::currentUser()->hasPermission('administer translation tasks') && tmgmt_local_translation_access($entity) && !$entity->getTranslator()) {
      $operations['assign'] = array(
        'href' => 'manage-translate/assign-tasks/' . $entity->id(),
        'query' => $this->getDestinationArray(),
        'attributes' => array(
          'title' => t('Assign'),
        ),
        'title' => t('assign'),
      );
    }
    elseif (tmgmt_local_translation_access($entity) && !$entity->getTranslator()) {
      $operations['assign_to_me'] = array(
        'href' => 'translate/' . $entity->id() . '/assign-to-me',
        'query' => $this->getDestinationArray(),
        'attributes' => array(
          'title' => t('Assign to me'),
        ),
        'title' => t('assign'),
      );
    }
    elseif (tmgmt_local_translation_access($entity) && !$entity->getTranslator()) {
      $operations['assign_to_me'] = array(
        'href' => 'translate/' . $entity->id() . '/assign-to-me',
        'query' => $this->getDestinationArray(),
        'attributes' => array(
          'title' => t('Assign to me'),
        ),
        'title' => t('assign'),
      );
    }
    if ($entity->getTranslator() && $entity->access('unassign')) {
      $operations['unassign'] = array(
        'href' => 'translate/' . $entity->id() . '/unassign',
        'query' => $this->getDestinationArray(),
        'attributes' => array(
          'title' => t('Unassign'),
        ),
        'title' => t('unassign'),
      );
    }
    if ($entity->access('delete')) {
      $operations['delete'] = array(
        'route_name' => 'tmgmt_local.local_task_delete',
        'route_parameters' => array('tmgmt_local_task' => $entity->id()),
        'query' => $this->getDestinationArray(),
        'title' => t('delete'),
      );
    }
    return $operations;
  }

}
