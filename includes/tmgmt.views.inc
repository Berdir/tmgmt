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
