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
      'help' => t('Displays a label of the job item'),
      'field' => array(
        'handler' => 'tmgmt_handler_field_tmgmt_job_item_label',
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
    $data['tmgmt_job']['actions'] = array(
      'title' => t('Actions'),
      'help' => t('Displays a list of options which are available for a job.'),
      'real field' => 'tjid',
      'field' => array(
        'handler' => 'tmgmt_handler_field_tmgmt_job_actions',
      ),
    );
    $data['tmgmt_job']['label']['field']['handler'] = 'tmgmt_handler_field_tmgmt_job_label';
    return $data;
  }
}
/**
 * Views controller class for the tmgmt job message entity.
 */
class TMGMTJobMessageViewsController extends EntityDefaultViewsController {
  public function views_data() {
    $data = parent::views_data();
    $data['tmgmt_job_message']['message']['field']['handler'] = 'tmgmt_handler_field_tmgmt_job_message_message';
    return $data;
  }
}
