<?php

/**
 * @file
 * Contains TMGMT specific views controller.
 */

/**
 * Views controller class for the job item entity.
 */
class TMGMTJobItemViewsController extends EntityDefaultViewsController {
  public function views_data() {
    $data = parent::views_data();
    $data['tmgmt_job_item']['label'] = array(
      'title' => t('Label'),
      'help' => t('Displays a label of the job item.'),
      'field' => array(
        'handler' => 'tmgmt_handler_field_tmgmt_entity_label',
      ),
    );
    $data['tmgmt_job_item']['operations'] = array(
      'title' => t('Operations'),
      'help' => t('Displays a list of options which are available for a job item.'),
      'real field' => 'tjiid',
      'field' => array(
        'handler' => 'tmgmt_handler_field_tmgmt_job_item_operations',
      ),
    );
    return $data;
  }
}

/**
 * Views controller class for the job entity.
 */
class TMGMTJobViewsController extends EntityDefaultViewsController {
  public function views_data() {
    $data = parent::views_data();
    $data['tmgmt_job']['operations'] = array(
      'title' => t('Operations'),
      'help' => t('Displays a list of options which are available for a job.'),
      'real field' => 'tjid',
      'field' => array(
        'handler' => 'tmgmt_handler_field_tmgmt_job_operations',
      ),
    );
    $data['tmgmt_job']['label']['field']['handler'] = 'tmgmt_handler_field_tmgmt_entity_label';
    $data['tmgmt_job']['job_item'] = array(
      'title' => t('Job items'),
      'help' => t('Get the job items of the job'),
      'relationship' => array(
        'base' => 'tmgmt_job_item',
        'base field' => 'tjid',
        'real field' => 'tjid',
        'label' => t('Job items'),
      ),
    );
    $data['tmgmt_job']['item_count'] = array(
      'title' => t('Job item count'),
      'help' => t('Show the amount of job items per job (per job item status)'),
      'real field' => 'tjid',
      'field' => array(
        'handler' => 'tmgmt_handler_field_tmgmt_job_item_count',
      ),
    );
    return $data;
  }
}
/**
 * Views controller class for the tmgmt job message entity.
 */
class TMGMTMessageViewsController extends EntityDefaultViewsController {
  public function views_data() {
    $data = parent::views_data();
    $data['tmgmt_message']['message']['field']['handler'] = 'tmgmt_handler_field_tmgmt_message_message';
    return $data;
  }
}
