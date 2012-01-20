<?php

/**
 * @file
 * Contains the controller classes.
 */

/**
 * Controller class for the tmgmt_job entity.
 *
 * @ingroup job
 */
class TMGMTJobController extends EntityAPIController {

  public function save($entity, DatabaseTransaction $transaction = NULL) {

    if (empty($entity->tjid)) {
      $entity->created = REQUEST_TIME;
    }

    $entity->changed = REQUEST_TIME;

    if (!isset($entity->state)) {
      $entity->state = TMGMT_JOB_STATE_NEW;
    }

    return parent::save($entity, $transaction);
  }

  /**
   * Overrides EntityAPIController::buildContent().
   */
  public function buildContent($entity, $view_mode = 'full', $langcode = NULL, $content = array()) {
    $content = parent::buildContent($entity, $view_mode, $langcode, $content);

    $wrapper = entity_metadata_wrapper('tmgmt_job', $entity);

    $content['created'] = array(
      '#type' => 'item',
      '#title' => t('Created'),
      '#markup' => format_date($wrapper->created->value()),
    );

    $content['changed'] = array(
      '#type' => 'item',
      '#title' => t('Last changed'),
      '#markup' => format_date($wrapper->changed->value()),
    );

    $content['state'] = array(
      '#type' => 'item',
      '#title' => t('State'),
      '#markup' => $wrapper->state->label(),
    );

    $content['created'] = array(
      '#type' => 'item',
      '#title' => t('Created'),
      '#markup' => format_date($wrapper->created->value()),
    );

    $content['items'] = array(
      '#type' => 'item',
      '#title' => t('Items'),
      'links' => array(
        '#theme' => 'links',
      )
    );

    foreach ($entity->getItems() as $item) {
      $uri = $item->uri();
      $content['items']['links']['#links'][$item->tjiid] = array(
        'title' => $item->label(),
        'href' => $uri['path'],
      );
    }

    // @todo: Re-add a view for this.
    /*if (module_exists('views')) {
      $view = views_get_view('translation_job_messages');
      $content['messages'] = array(
        '#type' => 'item',
        '#title' => $view->get_title(),
        '#markup' => $view->preview('block', array($entity->tjid)),
      );
    }*/

    return $content;
  }
}
