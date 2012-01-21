<?php

/**
 * @file
 * Please supply a file description.
 */

function tmgmt_translator_form($form, &$form_state) {
  drupal_set_title($form_state['op'] == 'add' ? t('Add Translator') : t('Import Translator'), PASS_THROUGH);
  return $form;
}
