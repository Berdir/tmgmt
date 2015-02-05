<?php

/**
 * @file
 * Hooks provided by the content entity source module.
 */

use Drupal\Core\Entity\Query\QueryInterface;

/**
 * @addtogroup tmgmt_source
 * @{
 */

/**
 * Allows to alter $query used to list entities on specific entity type overview
 * pages.
 *
 * @see TMGMTEntityDefaultSourceUIController
 */
function hook_tmgmt_content_list_query_alter(QueryInterface $query) {
  $query->condition('type', array('article', 'page'), 'IN');
}

/**
 * @} End of "addtogroup tmgmt_source".
 */
