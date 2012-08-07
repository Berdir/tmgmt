<?php

/**
 * @file
 * Contains \Drupal\tmgmt_entity_ui\Routing\TmgmtEntityUiRouteSubscriber.
 */

namespace Drupal\tmgmt_entity_ui\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\Core\Routing\RoutingEvents;
use Symfony\Component\Routing\Route;
use Symfony\Component\Routing\RouteCollection;

/**
 * Subscriber to alter entity translation routes.
 */
class TmgmtEntityUiRouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  protected function alterRoutes(RouteCollection $collection, $provider) {
    // Look for routes that use  ContentTranslationController and change it
    // to our subclass.
    foreach ($collection as $route) {
      if ($route->getDefault('_content') == '\Drupal\content_translation\Controller\ContentTranslationController::overview') {
        $route->setDefault('_content', '\Drupal\tmgmt_entity_ui\Controller\TmgmtContentTranslationControllerOverride::overview');
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events = parent::getSubscribedEvents();
    //  ContentTranslationRouteSubscriber is -100, make sure we are later.
    $events[RoutingEvents::ALTER] = array('onAlterRoutes', -101);
    return $events;
  }

}
