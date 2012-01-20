<?php

/**
 * @file
 * Rules integration.
 */

/**
 * Implements hook_rules_action_info().
 */
function tmgmt_rules_action_info() {
  $info['tmgmt_rules_job_request_translation'] = array(
    'label' => t('Request Job translation'),
    'group' => t('Translation Management'),
    'parameter' => array(
      'job' => array(
        'type' => 'tmgmt_job',
        'label' => t('Translation Job'),
        'description' => t('The translation job for which translations should be requested.'),
      ),
    ),
  );
  $info['tmgmt_rules_job_accept_translation'] = array(
      'label' => t('Accept Job translation'),
      'group' => t('Translation Management'),
      'parameter' => array(
        'job' => array(
          'type' => 'tmgmt_job',
          'label' => t('Translation Job'),
          'description' => t('The translation job for which translations should be accepted.'),
        ),
      ),
    );
  $info['tmgmt_rules_job_cancel_translation'] = array(
    'label' => t('Cancel translation job'),
    'group' => t('Translation Management'),
    'parameter' => array(
      'job' => array(
        'type' => 'tmgmt_job',
        'label' => t('Translation Job'),
        'description' => t('The translation job that should be canceled.'),
      ),
    ),
  );
  return $info;
}

/**
 * Rules callback to request a translation of a job.
 */
function tmgmt_rules_job_request_translation(TMGMTJob $job) {
  if ($job->isTranslatable()) {
    $job->requestTranslation();
  }
}

/**
 * Rules callback to accept a translation of a job.
 */
function tmgmt_rules_job_accept_translation(TMGMTJob $job) {
  if ($job->isNeedsReview()) {
    $job->accepted();
  }
}


/**
 * Rules callback to cancel a translation job.
 */
function tmgmt_rules_job_cancel_translation(TMGMTJob $job) {
  $job->cancelTranslation();
}
