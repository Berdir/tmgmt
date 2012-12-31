<?php

/*
 * @file
 * API documentation for the tmgmt_local module.
 */

/**
 * Return language capabilities.
 *
 * @param $source_language
 *   (optional) Only return capabilities for the given source language.
 * @param $account
 *   (optional) Only return capabilities for the given user.
 *
 * @return array
 *   An array of arrays with the keys source and target.
 */
function hook_tmgmt_local_translation_capabilities($source_language = NULL, $account = NULL) {
  return array(
    array(
      'source' => 'de',
      'target' => 'en',
    ),
    array(
      'source' => 'en',
      'target' => 'de',
    ),
  );
}
