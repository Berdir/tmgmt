<?php

/**
 * @file
 * Contains TMGMT specific views controller.
 */

/**
 * Views controller class for the tmgmt_job_item entity.
 */
class TMGMTJobItemViewsController extends EntityDefaultViewsController {
  public function views_data() {
    $data = parent::views_data();
    $data['tmgmt_job_item']['label'] = array(
      'title' => t('Label'),
      'help' => t('Displays a label of the job item'),
      'field' => array(
        'handler' => 'tmgmt_handler_field_job_item_label',
      ),
    );
    return $data;
  }
}

/**
 * Views controller class for the tmgmt_job entity.
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
    return $data;
  }

}
