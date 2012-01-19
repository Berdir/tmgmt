<?php

/**
 * @file
 * Rules integration.
 */

/**
 * Implements hook_rules_action_info().
 */
function tmgmt_rules_action_info() {
  return array(
    'tmgmt_rules_job_request_translation' => array(
      'label' => t('Request Job translation'),
      'group' => t('Translation Management'),
      'parameter' => array(
        'job' => array(
          'type' => 'tmgmt_job',
          'label' => t('Translation Job'),
          'description' => t('The translation job for which translations should be requested.'),
        ),
      ),
    ),
    'tmgmt_rules_job_accept_translation' => array(
      'label' => t('Accept Job translation'),
      'group' => t('Translation Management'),
      'parameter' => array(
        'job' => array(
          'type' => 'tmgmt_job',
          'label' => t('Translation Job'),
          'description' => t('The translation job for which translations should be accepted.'),
        ),
      ),
    ),
    'tmgmt_rules_job_cancel_translation' => array(
      'label' => t('Cancel translation job'),
      'group' => t('Translation Management'),
      'parameter' => array(
        'job' => array(
          'type' => 'tmgmt_job',
          'label' => t('Translation Job'),
          'description' => t('The translation job that should be canceled.'),
        ),
      ),
    ),
  );
}

/**
 * Rules callback to request a translation of a job.
 */
function tmgmt_rules_job_request_translation(TMGMTJob $job) {
  if ($job->canTranslate()) {
    $job->requestTranslation();
  }
}

/**
 * Rules callback to accept a translation of a job.
 */
function tmgmt_rules_job_accept_translation(TMGMTJob $job) {
  if ($job->isNeedsReview()) {
    $job->setState(TMGMTJob::STATE_ACCEPTED);
    $job->save();
    $job->propagateTranslatedData();
  }
}


/**
 * Rules callback to cancel a translation job.
 */
function tmgmt_rules_job_cancel_translation(TMGMTJob $job) {
  $job->cancelTranslation();
}
