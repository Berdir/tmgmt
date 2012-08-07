<?php

/**
 * @file
 * Contains Views controllers for the translation management module.
 */

/**
 * Implements hook_views_data().
 */
function tmgmt_views_data() {
  $data = array();

  $data['tmgmt_job'] = array(
    'table' => array(
      'group' => 'Translation Management Job Item',
      'entity type' => 'tmgmt_job_item',
      'base' => array(
        'field' => 'tjiid',
        'title' => 'Translation Management Job Item',
        'help' => '',
      ),
    ),
    'tjiid' => array(
      'title' => 'Translation management job item ID',
      'help' => 'The identifier of the translation job item.',
      'field' => array(
        'real field' => 'tjiid',
        'handler' => 'numeric',
        'click sortable' => true,
        'float' => false,
      ),
      'sort' => array(
        'real field' => 'tjiid',
        'handler' => 'standard',
      ),
      'filter' => array(
        'real field' => 'tjiid',
        'handler' => 'numeric',
      ),
      'argument' => array(
        'real field' => 'tjiid',
        'handler' => 'numeric',
      ),
    ),
    'tjid' => array(
      'relationship' => array(
        'label' => 'Translation Management Job',
        'handler' => 'relationship',
        'base' => 'tmgmt_job',
        'base field' => 'tjid',
        'relationship field' => 'tjid',
      ),
      'title' => 'Tjid',
      'help' => 'Corresponding job entity.',
      'field' => array(
        'real field' => 'tjid',
        'handler' => 'numeric',
        'click sortable' => true,
        'float' => false,
      ),
      'sort' => array(
        'real field' => 'tjid',
        'handler' => 'standard',
      ),
      'filter' => array(
        'real field' => 'tjid',
        'handler' => 'numeric',
      ),
      'argument' => array(
        'real field' => 'tjid',
        'handler' => 'numeric',
      ),
    ),
    'plugin' => array(
      'title' => 'Plugin',
      'help' => 'Indicates the plugin which provides this item.',
      'field' => array(
        'real field' => 'plugin',
        'handler' => 'machine_name',
        'click sortable' => true,
        'options callback' => array(
          0 => 'EntityDefaultViewsController',
          1 => 'optionsListCallback',
        ),
        'options arguments' => array(
          0 => 'tmgmt_job_item',
          1 => 'plugin',
          2 => 'view',
        ),
      ),
      'sort' => array(
        'real field' => 'plugin',
        'handler' => 'standard',
      ),
      'filter' => array(
        'real field' => 'plugin',
        'handler' => 'in_operator',
        'options callback' => array(
          0 => 'EntityDefaultViewsController',
          1 => 'optionsListCallback',
        ),
        'options arguments' => array(
          0 => 'tmgmt_job_item',
          1 => 'plugin',
          2 => 'view',
        ),
      ),
      'argument' => array(
        'real field' => 'plugin',
        'handler' => 'string',
      ),
    ),
    'item_type' => array(
      'title' => 'Item_type',
      'help' => 'The type of the item, e.g. the entity type.',
      'field' => array(
        'real field' => 'item_type',
        'handler' => 'standard',
        'click sortable' => true,
      ),
      'sort' => array(
        'real field' => 'item_type',
        'handler' => 'standard',
      ),
      'filter' => array(
        'real field' => 'item_type',
        'handler' => 'string',
      ),
      'argument' => array(
        'real field' => 'item_type',
        'handler' => 'string',
      ),
    ),
    'item_id' => array(
      'title' => 'Item_id',
      'help' => 'The unique id (within the given item type) of the item.',
      'field' => array(
        'real field' => 'item_id',
        'handler' => 'standard',
        'click sortable' => true,
      ),
      'sort' => array(
        'real field' => 'item_id',
        'handler' => 'standard',
      ),
      'filter' => array(
        'real field' => 'item_id',
        'handler' => 'string',
      ),
      'argument' => array(
        'real field' => 'item_id',
        'handler' => 'string',
      ),
    ),
    'state' => array(
      'title' => 'State',
      'help' => 'The state of the translation job item.',
      'field' => array(
        'real field' => 'state',
        'handler' => 'machine_name',
        'click sortable' => true,
        'float' => false,
        'options callback' => array(
          0 => 'EntityDefaultViewsController',
          1 => 'optionsListCallback',
        ),
        'options arguments' => array(
          0 => 'tmgmt_job_item',
          1 => 'state',
          2 => 'view',
        ),
      ),
      'sort' => array(
        'real field' => 'state',
        'handler' => 'standard',
      ),
      'filter' => array(
        'real field' => 'state',
        'handler' => 'in_operator',
        'options callback' => array(
          0 => 'EntityDefaultViewsController',
          1 => 'optionsListCallback',
        ),
        'options arguments' => array(
          0 => 'tmgmt_job_item',
          1 => 'state',
          2 => 'view',
        ),
      ),
      'argument' => array(
        'real field' => 'state',
        'handler' => 'numeric',
      ),
    ),
    'changed' => array(
      'title' => 'Changed',
      'help' => 'Changed timestamp.',
      'field' => array(
        'real field' => 'changed',
        'handler' => 'date',
        'click sortable' => true,
      ),
      'sort' => array(
        'real field' => 'changed',
        'handler' => 'date',
      ),
      'filter' => array(
        'real field' => 'changed',
        'handler' => 'date',
      ),
      'argument' => array(
        'real field' => 'changed',
        'handler' => 'date',
      ),
    ),
    'count_pending' => array(
      'title' => 'Count_pending',
      'help' => 'Counter for all pending data items.',
      'field' => array(
        'real field' => 'count_pending',
        'handler' => 'numeric',
        'click sortable' => true,
        'float' => false,
      ),
      'sort' => array(
        'real field' => 'count_pending',
        'handler' => 'standard',
      ),
      'filter' => array(
        'real field' => 'count_pending',
        'handler' => 'numeric',
      ),
      'argument' => array(
        'real field' => 'count_pending',
        'handler' => 'numeric',
      ),
    ),
    'count_translated' => array(
      'title' => 'Count_translated',
      'help' => 'Counter for all translated data items.',
      'field' => array(
        'real field' => 'count_translated',
        'handler' => 'numeric',
        'click sortable' => true,
        'float' => false,
      ),
      'sort' => array(
        'real field' => 'count_translated',
        'handler' => 'standard',
      ),
      'filter' => array(
        'real field' => 'count_translated',
        'handler' => 'numeric',
      ),
      'argument' => array(
        'real field' => 'count_translated',
        'handler' => 'numeric',
      ),
    ),
    'count_accepted' => array(
      'title' => 'Count_accepted',
      'help' => 'Counter for all accepted data items.',
      'field' => array(
        'real field' => 'count_accepted',
        'handler' => 'numeric',
        'click sortable' => true,
        'float' => false,
      ),
      'sort' => array(
        'real field' => 'count_accepted',
        'handler' => 'standard',
      ),
      'filter' => array(
        'real field' => 'count_accepted',
        'handler' => 'numeric',
      ),
      'argument' => array(
        'real field' => 'count_accepted',
        'handler' => 'numeric',
      ),
    ),
    'count_reviewed' => array(
      'title' => 'Count_reviewed',
      'help' => 'Counter for all reviewed data items.',
      'field' => array(
        'real field' => 'count_reviewed',
        'handler' => 'numeric',
        'click sortable' => true,
        'float' => false,
      ),
      'sort' => array(
        'real field' => 'count_reviewed',
        'handler' => 'standard',
      ),
      'filter' => array(
        'real field' => 'count_reviewed',
        'handler' => 'numeric',
      ),
      'argument' => array(
        'real field' => 'count_reviewed',
        'handler' => 'numeric',
      ),
    ),
    'word_count' => array(
      'title' => 'Word count',
      'help' => 'Word count of all texts contained in this job item.',
      'field' => array(
        'real field' => 'word_count',
        'handler' => 'numeric',
        'click sortable' => true,
        'float' => false,
      ),
      'sort' => array(
        'real field' => 'word_count',
        'handler' => 'standard',
      ),
      'filter' => array(
        'real field' => 'word_count',
        'handler' => 'numeric',
      ),
      'argument' => array(
        'real field' => 'word_count',
        'handler' => 'numeric',
      ),
    ),
    'label' => array(
      'title' => 'Label',
      'help' => 'Displays a label of the job item.',
      'field' => array(
        'handler' => 'tmgmt_handler_field_tmgmt_entity_label',
      ),
    ),
    'progress' => array(
      'title' => 'Progress',
      'help' => 'Displays the progress of a job item.',
      'real field' => 'tjiid',
      'field' => array(
        'handler' => 'tmgmt_handler_field_tmgmt_progress',
      ),
    ),
    'operations' => array(
      'title' => 'Operations',
      'help' => 'Displays a list of options which are available for a job item.',
      'real field' => 'tjiid',
      'field' => array(
        'handler' => 'tmgmt_handler_field_tmgmt_job_item_operations',
      ),
    ),
    'tmgmt_message' => array(
      'title' => 'Translation Management Message',
      'help' => 'Associated translation Management Message via the translation Management Message\'s tjid.',
      'relationship' => array(
        'label' => 'Translation Management Message',
        'handler' => 'relationship',
        'base' => 'tmgmt_message',
        'base field' => 'tjid',
        'relationship field' => 'tjid',
      ),
    ),
  );

  $data['tmgmt_job'] = array(
    'table' => array(
      'group' => 'Translation Management Job',
      'entity type' => 'tmgmt_job',
      'base' => array(
        'field' => 'tjid',
        'title' => 'Translation Management Job',
        'help' => '',
      ),
    ),
    'tjid' => array(
      'title' => 'Translation management job ID',
      'help' => 'The identifier of the translation job.',
      'field' => array(
        'real field' => 'tjid',
        'handler' => 'numeric',
        'click sortable' => TRUE,
        'float' => FALSE,
      ),
      'sort' => array(
        'real field' => 'tjid',
        'handler' => 'standard',
      ),
      'filter' => array(
        'real field' => 'tjid',
        'handler' => 'numeric',
      ),
      'argument' => array(
        'real field' => 'tjid',
        'handler' => 'numeric',
      ),
    ),
    'source_language' => array(
      'title' => 'Source_language',
      'help' => 'The source language of the data.',
      'field' => array(
        'real field' => 'source_language',
        'handler' => 'machine_name',
        'click sortable' => TRUE,
        'options callback' => array(
          0 => 'EntityDefaultViewsController',
          1 => 'optionsListCallback',
        ),
        'options arguments' => array(
          0 => 'tmgmt_job',
          1 => 'source_language',
          2 => 'view',
        ),
      ),
      'sort' => array(
        'real field' => 'source_language',
        'handler' => 'standard',
      ),
      'filter' => array(
        'real field' => 'source_language',
        'handler' => 'in_operator',
        'options callback' => array(
          0 => 'EntityDefaultViewsController',
          1 => 'optionsListCallback',
        ),
        'options arguments' => array(
          0 => 'tmgmt_job',
          1 => 'source_language',
          2 => 'view',
        ),
      ),
      'argument' => array(
        'real field' => 'source_language',
        'handler' => 'string',
      ),
    ),
    'target_language' => array(
      'title' => 'Target_language',
      'help' => 'The language the data should be translated to.',
      'field' => array(
        'real field' => 'target_language',
        'handler' => 'machine_name',
        'click sortable' => TRUE,
        'options callback' => array(
          0 => 'EntityDefaultViewsController',
          1 => 'optionsListCallback',
        ),
        'options arguments' => array(
          0 => 'tmgmt_job',
          1 => 'target_language',
          2 => 'view',
        ),
      ),
      'sort' => array(
        'real field' => 'target_language',
        'handler' => 'standard',
      ),
      'filter' => array(
        'real field' => 'target_language',
        'handler' => 'in_operator',
        'options callback' => array(
          0 => 'EntityDefaultViewsController',
          1 => 'optionsListCallback',
        ),
        'options arguments' => array(
          0 => 'tmgmt_job',
          1 => 'target_language',
          2 => 'view',
        ),
      ),
      'argument' => array(
        'real field' => 'target_language',
        'handler' => 'string',
      ),
    ),
    'state' => array(
      'title' => 'State',
      'help' => 'The state of the translation job.',
      'field' => array(
        'real field' => 'state',
        'handler' => 'machine_name',
        'click sortable' => TRUE,
        'float' => FALSE,
        'options callback' => array(
          0 => 'EntityDefaultViewsController',
          1 => 'optionsListCallback',
        ),
        'options arguments' => array(
          0 => 'tmgmt_job',
          1 => 'state',
          2 => 'view',
        ),
      ),
      'sort' => array(
        'real field' => 'state',
        'handler' => 'standard',
      ),
      'filter' => array(
        'real field' => 'state',
        'handler' => 'in_operator',
        'options callback' => array(
          0 => 'EntityDefaultViewsController',
          1 => 'optionsListCallback',
        ),
        'options arguments' => array(
          0 => 'tmgmt_job',
          1 => 'state',
          2 => 'view',
        ),
      ),
      'argument' => array(
        'real field' => 'state',
        'handler' => 'numeric',
      ),
    ),
    'created' => array(
      'title' => 'Created',
      'help' => 'Created timestamp.',
      'field' => array(
        'real field' => 'created',
        'handler' => 'date',
        'click sortable' => TRUE,
      ),
      'sort' => array(
        'real field' => 'created',
        'handler' => 'date',
      ),
      'filter' => array(
        'real field' => 'created',
        'handler' => 'date',
      ),
      'argument' => array(
        'real field' => 'created',
        'handler' => 'date',
      ),
    ),
    'changed' => array(
      'title' => 'Changed',
      'help' => 'Changed timestamp.',
      'field' => array(
        'real field' => 'changed',
        'handler' => 'date',
        'click sortable' => TRUE,
      ),
      'sort' => array(
        'real field' => 'changed',
        'handler' => 'date',
      ),
      'filter' => array(
        'real field' => 'changed',
        'handler' => 'date',
      ),
      'argument' => array(
        'real field' => 'changed',
        'handler' => 'date',
      ),
    ),
    'translator' => array(
      'relationship' => array(
        'label' => 'Translation Management Translator',
        'handler' => 'relationship',
        'base' => 'tmgmt_translator',
        'base field' => 'name',
        'relationship field' => 'translator',
      ),
      'title' => 'Translator',
      'help' => 'Machine name of the translator.',
      'field' => array(
        'real field' => 'translator',
        'handler' => 'tmgmt_handler_field_tmgmt_translator',
        'click sortable' => TRUE,
        'options callback' => array(
          0 => 'EntityDefaultViewsController',
          1 => 'optionsListCallback',
        ),
        'options arguments' => array(
          0 => 'tmgmt_job',
          1 => 'translator',
          2 => 'view',
        ),
      ),
      'sort' => array(
        'real field' => 'translator',
        'handler' => 'standard',
      ),
      'filter' => array(
        'real field' => 'translator',
        'handler' => 'in_operator',
        'options callback' => array(
          0 => 'EntityDefaultViewsController',
          1 => 'optionsListCallback',
        ),
        'options arguments' => array(
          0 => 'tmgmt_job',
          1 => 'translator',
          2 => 'view',
        ),
      ),
      'argument' => array(
        'real field' => 'translator',
        'handler' => 'string',
      ),
    ),
    'reference' => array(
      'title' => 'Reference',
      'help' => 'Remote identifier of this translation job.',
      'field' => array(
        'real field' => 'reference',
        'handler' => 'standard',
        'click sortable' => TRUE,
      ),
      'sort' => array(
        'real field' => 'reference',
        'handler' => 'standard',
      ),
      'filter' => array(
        'real field' => 'reference',
        'handler' => 'string',
      ),
      'argument' => array(
        'real field' => 'reference',
        'handler' => 'string',
      ),
    ),
    'uid' => array(
      'title' => 'Uid',
      'help' => 'uid of the job creator',
      'field' => array(
        'real field' => 'uid',
        'handler' => 'numeric',
        'click sortable' => TRUE,
        'float' => FALSE,
      ),
      'sort' => array(
        'real field' => 'uid',
        'handler' => 'standard',
      ),
      'filter' => array(
        'real field' => 'uid',
        'handler' => 'numeric',
      ),
      'argument' => array(
        'real field' => 'uid',
        'handler' => 'numeric',
      ),
    ),
    'label' => array(
      'title' => 'Label',
      'help' => 'Optional user provided label of the job.',
      'field' => array(
        'real field' => 'label',
        'handler' => 'tmgmt_handler_field_tmgmt_entity_label',
        'click sortable' => TRUE,
      ),
      'sort' => array(
        'real field' => 'label',
        'handler' => 'standard',
      ),
      'filter' => array(
        'real field' => 'label',
        'handler' => 'string',
      ),
      'argument' => array(
        'real field' => 'label',
        'handler' => 'string',
      ),
    ),
    'author' => array(
      'relationship' => array(
        'label' => 'User',
        'handler' => 'relationship',
        'base' => 'users',
        'base field' => 'uid',
        'relationship field' => 'uid',
      ),
      'title' => 'Author uid',
      'help' => 'The author of the translation job.',
      'field' => array(
        'real field' => 'uid',
        'handler' => 'numeric',
        'click sortable' => TRUE,
        'float' => FALSE,
      ),
      'sort' => array(
        'real field' => 'uid',
        'handler' => 'standard',
      ),
      'filter' => array(
        'real field' => 'uid',
        'handler' => 'numeric',
      ),
      'argument' => array(
        'real field' => 'uid',
        'handler' => 'numeric',
      ),
    ),
    'operations' => array(
      'title' => 'Operations',
      'help' => 'Displays a list of options which are available for a job.',
      'real field' => 'tjid',
      'field' => array(
        'handler' => 'tmgmt_handler_field_tmgmt_job_operations',
      ),
    ),
    'progress' => array(
      'title' => 'Progress',
      'help' => 'Displays the progress of a job.',
      'real field' => 'tjid',
      'field' => array(
        'handler' => 'tmgmt_handler_field_tmgmt_progress',
      ),
    ),
    'word_count' => array(
      'title' => 'Word count',
      'help' => 'Displays the word count of a job.',
      'real field' => 'tjid',
      'field' => array(
        'handler' => 'tmgmt_handler_field_tmgmt_wordcount',
      ),
    ),
    'job_item' => array(
      'title' => 'Job items',
      'help' => 'Get the job items of the job',
      'relationship' => array(
        'base' => 'tmgmt_job_item',
        'base field' => 'tjid',
        'real field' => 'tjid',
        'label' => 'Job items',
      ),
    ),
    'item_count' => array(
      'title' => 'Job item count',
      'help' => 'Show the amount of job items per job (per job item status)',
      'real field' => 'tjid',
      'field' => array(
        'handler' => 'tmgmt_handler_field_tmgmt_job_item_count',
      ),
    ),
    array (
      'tmgmt_job_item' => array(
        'title' => 'Translation Management Job Item',
        'help' => 'Associated translation Management Job Item via the translation Management Job Item\'s tjid.',
        'relationship' => array(
          'label' => 'Translation Management Job Item',
          'handler' => 'relationship',
          'base' => 'tmgmt_job_item',
          'base field' => 'tjid',
          'relationship field' => 'tjid',
        ),
      ),
    ),
    'tmgmt_message' => array(
      'title' => 'Translation Management Message',
      'help' => 'Associated translation Management Message via the translation Management Message\'s tjiid.',
      'relationship' => array(
        'label' => 'Translation Management Message',
        'handler' => 'relationship',
        'base' => 'tmgmt_message',
        'base field' => 'tjiid',
        'relationship field' => 'tjiid',
      ),
    ),
  );

  $data['users'] = array(
    'tmgmt_job' => array(
      'title' => 'Translation Management Job',
      'help' => 'Associated translation Management Job via the translation Management Job\'s author.',
      'relationship' => array(
        'label' => 'Translation Management Job',
        'handler' => 'relationship',
        'base' => 'tmgmt_job',
        'base field' => 'uid',
        'relationship field' => 'uid',
      ),
    ),
  );

  $data['tmgmt_message'] = array(
    'table' => array(
      'group' => 'Translation Management Message',
      'entity type' => 'tmgmt_message',
      'base' => array(
        'field' => 'mid',
        'title' => 'Translation Management Message',
        'help' => '',
      ),
    ),
    'mid' => array(
      'title' => 'Translation management message ID',
      'help' => 'The identifier of the message.',
      'field' => array(
        'real field' => 'mid',
        'handler' => 'numeric',
        'click sortable' => true,
        'float' => false,
      ),
      'sort' => array(
        'real field' => 'mid',
        'handler' => 'standard',
      ),
      'filter' => array(
        'real field' => 'mid',
        'handler' => 'numeric',
      ),
      'argument' => array(
        'real field' => 'mid',
        'handler' => 'numeric',
      ),
    ),
    'tjid' => array(
      'relationship' => array(
        'label' => 'Translation Management Job',
        'handler' => 'relationship',
        'base' => 'tmgmt_job',
        'base field' => 'tjid',
        'relationship field' => 'tjid',
      ),
      'title' => 'Tjid',
      'help' => 'Corresponding job entity.',
      'field' => array(
        'real field' => 'tjid',
        'handler' => 'numeric',
        'click sortable' => true,
        'float' => false,
      ),
      'sort' => array(
        'real field' => 'tjid',
        'handler' => 'standard',
      ),
      'filter' => array(
        'real field' => 'tjid',
        'handler' => 'numeric',
      ),
      'argument' => array(
        'real field' => 'tjid',
        'handler' => 'numeric',
      ),
    ),
    'tjiid' => array(
      'relationship' => array(
        'label' => 'Translation Management Job Item',
        'handler' => 'relationship',
        'base' => 'tmgmt_job_item',
        'base field' => 'tjiid',
        'relationship field' => 'tjiid',
      ),
      'title' => 'Tjiid',
      'help' => 'Corresponding job item entity.',
      'field' => array(
        'real field' => 'tjiid',
        'handler' => 'numeric',
        'click sortable' => true,
        'float' => false,
      ),
      'sort' => array(
        'real field' => 'tjiid',
        'handler' => 'standard',
      ),
      'filter' => array(
        'real field' => 'tjiid',
        'handler' => 'numeric',
      ),
      'argument' => array(
        'real field' => 'tjiid',
        'handler' => 'numeric',
      ),
    ),
    'message' => array(
      'title' => 'Message',
      'help' => 'The language into the data should be translated.',
      'field' => array(
        'real field' => 'message',
        'handler' => 'tmgmt_handler_field_tmgmt_message_message',
        'click sortable' => true,
      ),
      'sort' => array(
        'real field' => 'message',
        'handler' => 'standard',
      ),
      'filter' => array(
        'real field' => 'message',
        'handler' => 'string',
      ),
      'argument' => array(
        'real field' => 'message',
        'handler' => 'string',
      ),
    ),
    'created' => array(
      'title' => 'Created',
      'help' => 'Created timestamp.',
      'field' => array(
        'real field' => 'created',
        'handler' => 'date',
        'click sortable' => true,
      ),
      'sort' => array(
        'real field' => 'created',
        'handler' => 'date',
      ),
      'filter' => array(
        'real field' => 'created',
        'handler' => 'date',
      ),
      'argument' => array(
        'real field' => 'created',
        'handler' => 'date',
      ),
    ),
    'type' => array(
      'title' => 'Type',
      'help' => 'Type of the message (debug, notice, warning or error)',
      'field' => array(
        'real field' => 'type',
        'handler' => 'standard',
        'click sortable' => true,
      ),
      'sort' => array(
        'real field' => 'type',
        'handler' => 'standard',
      ),
      'filter' => array(
        'real field' => 'type',
        'handler' => 'string',
      ),
      'argument' => array(
        'real field' => 'type',
        'handler' => 'string',
      ),
    ),
  );

  return $data;
  /*
  foreach (tmgmt_source_views_controller() as $controller) {
    $data += $controller->views_data();
  }
  return $data;*/
}

/**
 * Views controller class for the job item entity.
 */
class JobItemViewsController extends EntityDefaultViewsController {

  /**
   * {@inheritdoc}
   */
  public function views_data() {
    $data = parent::views_data();
    $data['tmgmt_job_item']['label'] = array(
      'title' => t('Label'),
      'help' => t('Displays a label of the job item.'),
      'field' => array(
        'handler' => 'tmgmt_handler_field_tmgmt_entity_label',
      ),
    );
    $data['tmgmt_job_item']['type'] = array(
      'title' => t('Type'),
      'help' => t('Displays a type of the job item.'),
      'field' => array(
        'handler' => 'tmgmt_handler_field_tmgmt_job_item_type',
      ),
    );
    $data['tmgmt_job_item']['progress'] = array(
      'title' => t('Progress'),
      'help' => t('Displays the progress of a job item.'),
      'real field' => 'tjiid',
      'field' => array(
        'handler' => 'tmgmt_handler_field_tmgmt_progress',
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
class JobViewsController extends EntityDefaultViewsController {

  /**
   * {@inheritdoc}
   */
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
    $data['tmgmt_job']['progress'] = array(
      'title' => t('Progress'),
      'help' => t('Displays the progress of a job.'),
      'real field' => 'tjid',
      'field' => array(
        'handler' => 'tmgmt_handler_field_tmgmt_progress',
      ),
    );
    $data['tmgmt_job']['word_count'] = array(
      'title' => t('Word count'),
      'help' => t('Displays the word count of a job.'),
      'real field' => 'tjid',
      'field' => array(
        'handler' => 'tmgmt_handler_field_tmgmt_wordcount',
      ),
    );
    $data['tmgmt_job']['label']['field']['handler'] = 'tmgmt_handler_field_tmgmt_entity_label';
    $data['tmgmt_job']['translator']['field']['handler'] = 'tmgmt_handler_field_tmgmt_translator';
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
 * Views controller class for the job message entity.
 */
class TMGMTMessageViewsController extends EntityDefaultViewsController {

  /**
   * {@inheritdoc}
   */
  public function views_data() {
    $data = parent::views_data();
    $data['tmgmt_message']['message']['field']['handler'] = 'tmgmt_handler_field_tmgmt_message_message';
    return $data;
  }

}

interface TMGMTSourceViewsControllerInterface extends TMGMTPluginBaseInterface {

  /**
   * Defines the result for hook_views_data().
   */
  public function views_data();

}

/**
 * Vies controller class for source plugins.
 */
class TMGMTDefaultSourceViewsController extends TMGMTPluginBase implements TMGMTSourceViewsControllerInterface {

  /**
   * {@inheritdoc}
   */
  public function views_data() {
    // @todo Implement this in a generic fashion.
    /* $key = $this->pluginInfo['something'];
    $data[$key]['tmgmt_translatable_types'] = array(
      'title' => t('Translatable types'),
      'help' => t('Filter translatable elements based on their types.'),
      'filter' => array(
        'handler' => 'in_operator',
        'real field' => 'type',
        'options callback' => 'tmgmt_source_translatable_item_types',
        'options arguments' => array($this->pluginType),
      ),
    ); */
    return array();
  }

}
