<?php

/**
 * @file
 * Contains Views controllers for the translation management module.
 */

/**
 * Implements hook_views_data().
 */
function tmgmt_views_data() {
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
      'title' => 'Job ID',
      'help' => 'The identifier of the translation job.',
      'field' => array(
        'id' => 'numeric',
        'click sortable' => TRUE,
        'float' => FALSE,
      ),
      'sort' => array(
        'id' => 'standard',
      ),
      'filter' => array(
        'id' => 'numeric',
      ),
      'argument' => array(
        'id' => 'numeric',
      ),
    ),
    'source_language' => array(
      'title' => 'Source language',
      'help' => 'The source language of the data.',
      'field' => array(
        'id' => 'machine_name',
        'click sortable' => TRUE,
        'options callback' => 'tmgmt_available_languages',
      ),
      'sort' => array(
        'id' => 'standard',
      ),
      'filter' => array(
        'id' => 'in_operator',
        'options callback' => 'tmgmt_available_languages',
      ),
      'argument' => array(
        'id' => 'standard',
      ),
    ),
    'target_language' => array(
      'title' => 'Target language',
      'help' => 'The language the data should be translated to.',
      'field' => array(
        'id' => 'machine_name',
        'click sortable' => TRUE,
        'options callback' => 'tmgmt_available_languages',
      ),
      'sort' => array(
        'id' => 'standard',
      ),
      'filter' => array(
        'id' => 'in_operator',
        'options callback' => 'tmgmt_available_languages',
      ),
      'argument' => array(
        'id' => 'standard',
      ),
    ),
    'state' => array(
      'title' => 'State',
      'help' => 'The state of the translation job.',
      'field' => array(
        'id' => 'machine_name',
        'click sortable' => TRUE,
        'float' => FALSE,
        'options callback' => 'tmgmt_job_states',
      ),
      'sort' => array(
        'id' => 'standard',
      ),
      'filter' => array(
        'id' => 'in_operator',
        'options callback' => 'tmgmt_job_states',
      ),
      'argument' => array(
        'id' => 'numeric',
      ),
    ),
    'created' => array(
      'title' => 'Created',
      'help' => 'Created timestamp.',
      'field' => array(
        'id' => 'date',
        'click sortable' => TRUE,
      ),
      'sort' => array(
        'id' => 'date',
      ),
      'filter' => array(
        'id' => 'date',
      ),
      'argument' => array(
        'id' => 'date',
      ),
    ),
    'changed' => array(
      'title' => 'Changed',
      'help' => 'Changed timestamp.',
      'field' => array(
        'id' => 'date',
        'click sortable' => TRUE,
      ),
      'sort' => array(
        'id' => 'date',
      ),
      'filter' => array(
        'id' => 'date',
      ),
      'argument' => array(
        'id' => 'date',
      ),
    ),
    'translator' => array(
      'title' => 'Translator',
      'help' => 'Machine name of the translator.',
      'field' => array(
        'id' => 'tmgmt_translator',
        'click sortable' => TRUE,
        'options callback' => 'tmgmt_translator_labels',
      ),
      'sort' => array(
        'id' => 'standard',
      ),
      'filter' => array(
        'id' => 'in_operator',
        'options callback' => 'tmgmt_translator_labels',
      ),
      'argument' => array(
        'id' => 'standard',
      ),
    ),
    'reference' => array(
      'title' => 'Reference',
      'help' => 'Remote identifier of this translation job.',
      'field' => array(
        'id' => 'standard',
        'click sortable' => TRUE,
      ),
      'sort' => array(
        'id' => 'standard',
      ),
      'filter' => array(
        'id' => 'standard',
      ),
      'argument' => array(
        'id' => 'standard',
      ),
    ),
    'uid' => array(
      'title' => 'Uid',
      'help' => 'uid of the job creator',
      'field' => array(
        'id' => 'numeric',
        'click sortable' => TRUE,
        'float' => FALSE,
      ),
      'sort' => array(
        'id' => 'standard',
      ),
      'filter' => array(
        'id' => 'numeric',
      ),
      'argument' => array(
        'id' => 'numeric',
      ),
    ),
    'label' => array(
      'title' => 'Label',
      'help' => 'Optional user provided label of the job.',
      'field' => array(
        'id' => 'tmgmt_entity_label',
      ),
      'sort' => array(
        'id' => 'standard',
      ),
      'filter' => array(
        'id' => 'standard',
      ),
      'argument' => array(
        'id' => 'standard',
      ),
    ),
    'author' => array(
      'relationship' => array(
        'label' => 'User',
        'id' => 'standard',
        'base' => 'users',
        'base field' => 'uid',
        'relationship field' => 'uid',
      ),
      'title' => 'Author uid',
      'help' => 'The author of the translation job.',
      'field' => array(
        'id' => 'numeric',
        'click sortable' => TRUE,
        'float' => FALSE,
      ),
      'sort' => array(
        'id' => 'standard',
      ),
      'filter' => array(
        'id' => 'numeric',
      ),
      'argument' => array(
        'id' => 'numeric',
      ),
    ),
    'operations' => array(
      'title' => 'Operations',
      'help' => 'Displays a list of options which are available for a job.',
      'real field' => 'tjid',
      'field' => array(
        'id' => 'tmgmt_job_operations',
      ),
    ),
    'progress' => array(
      'title' => 'Progress',
      'help' => 'Displays the progress of a job.',
      'real field' => 'tjid',
      'field' => array(
        'id' => 'tmgmt_progress',
      ),
    ),
    'word_count' => array(
      'title' => 'Word count',
      'help' => 'Displays the word count of a job.',
      'real field' => 'tjid',
      'field' => array(
        'id' => 'tmgmt_wordcount',
      ),
    ),
    'job_item' => array(
      'title' => 'Job items',
      'help' => 'Get the job items of the job',
      'relationship' => array(
        'id' => 'standard',
        'base' => 'tmgmt_job_item',
        'base field' => 'tjid',
        'real field' => 'tjid',
        'label' => 'Job items',
      ),
    ),
  );

  $data['tmgmt_job_item'] = array(
    'table' => array(
      'group' => 'Translation Management Job item',
      'entity type' => 'tmgmt_job_item',
      'base' => array(
        'field' => 'tjiid',
        'title' => 'Translation Management Job item',
        'help' => '',
      ),
    ),
    'tjiid' => array(
      'title' => 'Translation management job item ID',
      'help' => 'The identifier of the translation job item.',
      'field' => array(
        'real field' => 'tjiid',
        'id' => 'numeric',
        'click sortable' => TRUE,
        'float' => FALSE,
      ),
      'sort' => array(
        'real field' => 'tjiid',
        'id' => 'standard',
      ),
      'filter' => array(
        'real field' => 'tjiid',
        'id' => 'numeric',
      ),
      'argument' => array(
        'real field' => 'tjiid',
        'id' => 'numeric',
      ),
    ),
    'label' => array(
      'title' => 'Label',
      'help' => 'Displays a label of the job item.',
      'real field' => 'tjiid',
      'field' => array(
        'id' => 'tmgmt_entity_label',
      ),
    ),
    'tjid' => array(
      'relationship' => array(
        'label' => 'Translation Management Job',
        'id' => 'standard',
        'base' => 'tmgmt_job',
        'base field' => 'tjid',
      ),
      'title' => 'Tjid',
      'help' => 'Corresponding job entity.',
      'field' => array(
        'real field' => 'tjid',
        'id' => 'numeric',
        'click sortable' => TRUE,
        'float' => FALSE,
      ),
      'sort' => array(
        'real field' => 'tjid',
        'id' => 'standard',
      ),
      'filter' => array(
        'real field' => 'tjid',
        'id' => 'numeric',
      ),
      'argument' => array(
        'real field' => 'tjid',
        'id' => 'numeric',
      ),
    ),
    'plugin' => array(
      'title' => 'Plugin',
      'help' => 'Indicates the plugin which provides this item.',
      'field' => array(
        'real field' => 'plugin',
        'id' => 'machine_name',
        'click sortable' => TRUE,
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
        'id' => 'standard',
      ),
      'filter' => array(
        'real field' => 'plugin',
        'id' => 'in_operator',
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
        'id' => 'string',
      ),
    ),
    'item_type' => array(
      'title' => 'Item_type',
      'help' => 'The type of the item, e.g. the entity type.',
      'field' => array(
        'real field' => 'item_type',
        'id' => 'standard',
        'click sortable' => TRUE,
      ),
      'sort' => array(
        'real field' => 'item_type',
        'id' => 'standard',
      ),
      'filter' => array(
        'real field' => 'item_type',
        'id' => 'string',
      ),
      'argument' => array(
        'real field' => 'item_type',
        'id' => 'string',
      ),
    ),
    'item_id' => array(
      'title' => 'Item_id',
      'help' => 'The unique id (within the given item type) of the item.',
      'field' => array(
        'real field' => 'item_id',
        'id' => 'standard',
        'click sortable' => TRUE,
      ),
      'sort' => array(
        'real field' => 'item_id',
        'id' => 'standard',
      ),
      'filter' => array(
        'real field' => 'item_id',
        'id' => 'string',
      ),
      'argument' => array(
        'real field' => 'item_id',
        'id' => 'string',
      ),
    ),
    'state' => array(
      'title' => 'State',
      'help' => 'The state of the translation job item.',
      'field' => array(
        'id' => 'machine_name',
        'click sortable' => TRUE,
        'float' => FALSE,
        'options callback' => 'tmgmt_job_item_states',
      ),
      'sort' => array(
        'id' => 'standard',
      ),
      'filter' => array(
        'id' => 'in_operator',
        'options callback' => 'tmgmt_job_item_states',
      ),
      'argument' => array(
        'id' => 'numeric',
      ),
    ),
    'changed' => array(
      'title' => 'Changed',
      'help' => 'Changed timestamp.',
      'field' => array(
        'real field' => 'changed',
        'id' => 'date',
        'click sortable' => TRUE,
      ),
      'sort' => array(
        'real field' => 'changed',
        'id' => 'date',
      ),
      'filter' => array(
        'real field' => 'changed',
        'id' => 'date',
      ),
      'argument' => array(
        'real field' => 'changed',
        'id' => 'date',
      ),
    ),
    'count_pending' => array(
      'title' => 'Count_pending',
      'help' => 'Counter for all pending data items.',
      'field' => array(
        'real field' => 'count_pending',
        'id' => 'numeric',
        'click sortable' => TRUE,
        'float' => FALSE,
      ),
      'sort' => array(
        'real field' => 'count_pending',
        'id' => 'standard',
      ),
      'filter' => array(
        'real field' => 'count_pending',
        'id' => 'numeric',
      ),
      'argument' => array(
        'real field' => 'count_pending',
        'id' => 'numeric',
      ),
    ),
    'count_translated' => array(
      'title' => 'Count_translated',
      'help' => 'Counter for all translated data items.',
      'field' => array(
        'real field' => 'count_translated',
        'id' => 'numeric',
        'click sortable' => TRUE,
        'float' => FALSE,
      ),
      'sort' => array(
        'real field' => 'count_translated',
        'id' => 'standard',
      ),
      'filter' => array(
        'real field' => 'count_translated',
        'id' => 'numeric',
      ),
      'argument' => array(
        'real field' => 'count_translated',
        'id' => 'numeric',
      ),
    ),
    'count_accepted' => array(
      'title' => 'Count_accepted',
      'help' => 'Counter for all accepted data items.',
      'field' => array(
        'real field' => 'count_accepted',
        'id' => 'numeric',
        'click sortable' => TRUE,
        'float' => FALSE,
      ),
      'sort' => array(
        'real field' => 'count_accepted',
        'id' => 'standard',
      ),
      'filter' => array(
        'real field' => 'count_accepted',
        'id' => 'numeric',
      ),
      'argument' => array(
        'real field' => 'count_accepted',
        'id' => 'numeric',
      ),
    ),
    'count_reviewed' => array(
      'title' => 'Count_reviewed',
      'help' => 'Counter for all reviewed data items.',
      'field' => array(
        'real field' => 'count_reviewed',
        'id' => 'numeric',
        'click sortable' => TRUE,
        'float' => FALSE,
      ),
      'sort' => array(
        'real field' => 'count_reviewed',
        'id' => 'standard',
      ),
      'filter' => array(
        'real field' => 'count_reviewed',
        'id' => 'numeric',
      ),
      'argument' => array(
        'real field' => 'count_reviewed',
        'id' => 'numeric',
      ),
    ),
    'word_count' => array(
      'title' => 'Word count',
      'help' => 'Word count of all texts contained in this job item.',
      'field' => array(
        'real field' => 'word_count',
        'id' => 'numeric',
        'click sortable' => TRUE,
        'float' => FALSE,
      ),
      'sort' => array(
        'real field' => 'word_count',
        'id' => 'standard',
      ),
      'filter' => array(
        'real field' => 'word_count',
        'id' => 'numeric',
      ),
      'argument' => array(
        'real field' => 'word_count',
        'id' => 'numeric',
      ),
    ),
    'progress' => array(
      'title' => 'Progress',
      'help' => 'Displays the progress of a job item.',
      'real field' => 'tjiid',
      'field' => array(
        'id' => 'tmgmt_progress',
      ),
    ),
    'operations' => array(
      'title' => 'Operations',
      'help' => 'Displays a list of options which are available for a job item.',
      'real field' => 'tjiid',
      'field' => array(
        'id' => 'tmgmt_job_item_operations',
      ),
    ),
    'tmgmt_message' => array(
      'title' => 'Translation Management Message',
      'help' => 'Associated translation Management Message via the translation Management Message\'s tjid.',
      'relationship' => array(
        'label' => 'Translation Management Message',
        'id' => 'standard',
        'base' => 'tmgmt_message',
        'base field' => 'tjid',
      ),
    ),
    'type' => array(
      'title' => t('Type'),
      'help' => t('Displays a type of the job item.'),
      'real field' => 'tjiid',
      'field' => array(
        'id' => 'tmgmt_job_item_type',
      ),
    ),
  );

  $data['tmgmt_message'] = array(
    'table' => array(
      'group' => 'Translation Message',
      'entity type' => 'tmgmt_message',
      'base' => array(
        'field' => 'mid',
        'title' => 'Translation Message',
        'help' => '',
      ),
    ),
    'mid' => array(
      'title' => 'Translation message ID',
      'help' => 'The identifier of the message.',
      'field' => array(
        'id' => 'numeric',
        'click sortable' => TRUE,
        'float' => FALSE,
      ),
      'sort' => array(
        'id' => 'standard',
      ),
      'filter' => array(
        'id' => 'numeric',
      ),
      'argument' => array(
        'id' => 'numeric',
      ),
    ),
    'tjid' => array(
      'relationship' => array(
        'label' => 'Translation Job',
        'id' => 'standard',
        'base' => 'tmgmt_job',
        'base field' => 'tjid',
      ),
      'title' => 'Job',
      'help' => 'Corresponding translation job.',
      'field' => array(
        'id' => 'numeric',
        'click sortable' => TRUE,
        'float' => FALSE,
      ),
      'sort' => array(
        'id' => 'standard',
      ),
      'filter' => array(
        'id' => 'numeric',
      ),
      'argument' => array(
        'id' => 'numeric',
      ),
    ),
    'tjiid' => array(
      'relationship' => array(
        'label' => 'Translation Job Item',
        'id' => 'standard',
        'base' => 'tmgmt_job_item',
        'base field' => 'tjiid',
      ),
      'title' => 'Job item',
      'help' => 'Corresponding job item.',
      'field' => array(
        'id' => 'numeric',
        'click sortable' => TRUE,
        'float' => FALSE,
      ),
      'sort' => array(
        'id' => 'standard',
      ),
      'filter' => array(
        'id' => 'numeric',
      ),
      'argument' => array(
        'id' => 'numeric',
      ),
    ),
    'message' => array(
      'title' => 'Message',
      'help' => 'The language into the data should be translated.',
      'field' => array(
        'id' => 'tmgmt_message',
        'click sortable' => TRUE,
      ),
      'sort' => array(
        'id' => 'standard',
      ),
      'filter' => array(
        'id' => 'string',
      ),
      'argument' => array(
        'id' => 'string',
      ),
    ),
    'created' => array(
      'title' => 'Created',
      'help' => 'Created timestamp.',
      'field' => array(
        'real field' => 'created',
        'id' => 'date',
        'click sortable' => TRUE,
      ),
      'sort' => array(
        'real field' => 'created',
        'id' => 'date',
      ),
      'filter' => array(
        'real field' => 'created',
        'id' => 'date',
      ),
      'argument' => array(
        'real field' => 'created',
        'id' => 'date',
      ),
    ),
    'type' => array(
      'title' => 'Type',
      'help' => 'Type of the message (debug, notice, warning or error)',
      'field' => array(
        'real field' => 'type',
        'id' => 'standard',
        'click sortable' => TRUE,
      ),
      'sort' => array(
        'real field' => 'type',
        'id' => 'standard',
      ),
      'filter' => array(
        'real field' => 'type',
        'id' => 'string',
      ),
      'argument' => array(
        'real field' => 'type',
        'id' => 'string',
      ),
    ),
    'author' => array(
      'relationship' => array(
        'label' => 'User',
        'id' => 'standard',
        'base' => 'users',
        'base field' => 'uid',
        'relationship field' => 'uid',
      ),
      'title' => 'Author uid',
      'help' => 'The author of the message.',
      'field' => array(
        'id' => 'numeric',
        'click sortable' => TRUE,
        'float' => FALSE,
      ),
      'sort' => array(
        'id' => 'standard',
      ),
      'filter' => array(
        'id' => 'numeric',
      ),
      'argument' => array(
        'id' => 'numeric',
      ),
    ),
  );

  return $data;
}
