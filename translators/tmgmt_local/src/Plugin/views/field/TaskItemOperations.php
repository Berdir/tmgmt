<?php

/**
 * @file
 * Contains \Drupal\tmgmt_local\Plugin\views\field\TaskItemOperations.
 */

namespace Drupal\tmgmt_local\Plugin\views\field;

use Drupal\Core\Link;
use Drupal\Core\Routing\RedirectDestinationTrait;
use Drupal\Core\Url;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;

/**
 * Field handler which shows the link for translating translation task items.
 *
 * @ViewsField("tmgmt_local_task_item_operations")
 */
class TaskItemOperations extends FieldPluginBase {
  use RedirectDestinationTrait;

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    /** @var \Drupal\tmgmt_local\Entity\LocalTaskItem $item */
    $item = $values->_entity;

    $element = [];
    // Only allow to translate if the job is assigned to this user.
    if ($item->access('view') && $item->getTask()->getTranslator()->id() == \Drupal::currentUser()->id()) {
      $element = Link::fromTextAndUrl($item->isPending() ? t('translate') : t('View'),
        Url::fromUserInput('/translate/items/' . $item->id()))->toString();
    }
    return $element;
  }

}
