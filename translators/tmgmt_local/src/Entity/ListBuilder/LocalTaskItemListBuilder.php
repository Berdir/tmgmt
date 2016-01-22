<?php
/**
 * @file
 * Contains \Drupal\tmgmt\Entity\ListBuilder\JobListBuilder.
 */

namespace Drupal\tmgmt_local\Entity\ListBuilder;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;

/**
 * Provides the views data for the message entity type.
 */
class LocalTaskItemListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  protected function getDefaultOperations(EntityInterface $entity) {
    /** @var \Drupal\tmgmt_local\Entity\LocalTaskItem $entity */
    $operations = parent::getDefaultOperations($entity);
    // @todo access control handlers and routing
    if ($entity->access('view', \Drupal::currentUser()) && $entity->getTask()->getAssignee()->id() == \Drupal::currentUser()->id()) {
      if ($entity->isPending()) {
        $element['#links']['translate'] = [
          'title' => $this->t('Translate'),
          'weight' => 0,
          'url' => $entity->toUrl('translate'),
        ];
      }
      else {
        $element['#links']['view'] = [
          'title' => $this->t('View'),
          'weight' => 0,
          'url' => $entity->toUrl('view'),
        ];
      }
    }
    return $operations;
  }

}
