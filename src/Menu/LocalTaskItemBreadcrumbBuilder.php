<?php

/**
 * @file
 * Contains of \Drupal\tmgmt\Menu\LocalTaskItemBreadcrumbBuilder.
 */

namespace Drupal\tmgmt\Menu;

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Link;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\tmgmt_local\LocalTaskItemInterface;

/**
 * A custom Local task item breadcrumb builder.
 */
class LocalTaskItemBreadcrumbBuilder implements BreadcrumbBuilderInterface {
  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function applies(RouteMatchInterface $route_match) {
    if ($route_match->getRouteName() == 'entity.tmgmt_local_task_item.canonical' && $route_match->getParameter('tmgmt_local_task_item') instanceof LocalTaskItemInterface) {
      return TRUE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {
    $breadcrumb = new Breadcrumb();
    $breadcrumb->addLink(Link::createFromRoute($this->t('Home'), '<front>'));
    $breadcrumb->addCacheContexts(['route']);

    /** @var LocalTaskItemInterface $local_task_item */
    $local_task_item = $route_match->getParameter('tmgmt_local_task_item');
    $breadcrumb->addCacheableDependency($local_task_item);

    $breadcrumb->addLink(Link::createFromRoute($this->t('Local Tasks'), 'view.tmgmt_local_task_overview.unassigned'));
    $breadcrumb->addLink(Link::createFromRoute($local_task_item->getTask()->label(), 'entity.tmgmt_local_task.canonical', array('tmgmt_local_task' => $local_task_item->getTask()->id())));

    return $breadcrumb;
  }

}
