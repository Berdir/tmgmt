<?php
/**
 * @file
 * Contains \Drupal\tmgmt\Entity\ListBuilder\JobListBuilder.
 */

namespace Drupal\tmgmt\Entity\ListBuilder;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Url;

/**
 * Provides the views data for the message entity type.
 */
class LocalTaskItemListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  protected function getDefaultOperations(EntityInterface $entity) {
    $operations = parent::getDefaultOperations($entity);
    // @todo access control handlers and routing
    if (entity_access('view', 'tmgmt_local_task_item', $entity) && $entity->getTask()->tuid == $user->id()) {
    $element['#links']['translate'] = array(
      'href' => 'translate/' . $entity->tltid . '/item/' . $entity->tltiid,
      'attributes' => array(
        'title' => $entity->isPending() ? t('Translate') : t('View'),
      ),
      'title' => $entity->isPending() ? t('translate') : t('view'),
    );
  }
    return $operations;
  }
}
