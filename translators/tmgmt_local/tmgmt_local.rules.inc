<?php

/**
 * @file
 * Rules integration.
 */

/**
 * Implements hook_rules_action_info().
 */
function tmgmt_local_rules_action_info() {
  $info['tmgmt_local_rules_task_assign_to_me'] = array(
    'label' => t('Assign Translation Local Task To Me'),
    'group' => t('Translation Management'),
    'parameter' => array(
      'task' => array(
        'type' => 'tmgmt_local_task',
        'label' => t('Translation Local Task'),
        'description' => t('The translation local task which can be assigned to current user.'),
      ),
    ),
  );
  $info['tmgmt_local_rules_task_unassign'] = array(
    'label' => t('Unassign Translation Local Task'),
    'group' => t('Translation Management'),
    'parameter' => array(
      'task' => array(
        'type' => 'tmgmt_local_task',
        'label' => t('Translation Local Task'),
        'description' => t('The translation local task which will be unassigned.'),
      ),
    ),
  );

  return $info;
}


/**
 * Rules callback to assign a translation task to translator.
 */
function tmgmt_local_rules_task_assign_to_me(TMGMTLocalTask $task) {
  global $user;
  $task->assign($user);
  $task->save();
}

/**
 * Rules callback to assign a translation task to translator.
 */
function tmgmt_local_rules_task_assign(TMGMTLocalTask $task, stdClass $translator) {
  $task->assign($translator);
  $task->save();
}

/**
 * Rules callback to unassign a translation task.
 */
function tmgmt_local_rules_task_unassign(TMGMTLocalTask $task) {
  $task->unassign();
  $task->save();
}
