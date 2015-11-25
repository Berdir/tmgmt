<?php
/**
 * @file
 * Contains \Drupal\tmgmt\Entity\ListBuilder\JobListBuilder.
 */

namespace Drupal\tmgmt_local\Entity\ListBuilder;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityListBuilder;
use Drupal\Core\Url;

/**
 * Provides the views data for the message entity type.
 */
class LocalTaskListBuilder extends EntityListBuilder {

  /**
   * {@inheritdoc}
   */
  protected function getDefaultOperations(EntityInterface $entity) {
    $operations = parent::getDefaultOperations($entity);

    // @todo access control handlers
    if ($entity->access('view')) {
      $operations['view'] = array(
        'url' => $entity->urlInfo()->setOption('query', array('destination' => Url::fromRoute('<current>')->getInternalPath())),
        'title' => t('view'),
      );
    }
    // @todo create routing and update urls
    if (user_access('administer translation tasks') && tmgmt_local_translation_access($entity) && empty($entity->tuid)) {
      $operations['assign'] = array(
        'href' => 'manage-translate/assign-tasks/' . $entity->tltid,
        'query' => array('destination' => current_path()),
        'attributes' => array(
          'title' => t('Assign'),
        ),
        'title' => t('assign'),
      );
    }
    elseif (tmgmt_local_translation_access($entity) && empty($entity->tuid)) {
      $operations['assign_to_me'] = array(
        'href' => 'translate/' . $entity->tltid . '/assign-to-me',
        'query' => array('destination' => current_path()),
        'attributes' => array(
          'title' => t('Assign to me'),
        ),
        'title' => t('assign'),
      );
    }
    if (!empty($entity->tuid) && $entity->access('unassign')) {
      $operations['unassign'] = array(
        'href' => 'translate/' . $entity->tltid . '/unassign',
        'query' => array('destination' => current_path()),
        'attributes' => array(
          'title' => t('Unassign'),
        ),
        'title' => t('unassign'),
      );
      return $operations;
    }
  }
}
