<?php

/**
 * @file
 * Please supply a file description.
 */

class TMGMTTranslatorUIController extends EntityDefaultUIController {

  /**
   * Implements hook_menu();
   */
  public function hook_menu() {
    $items = parent::hook_menu();
    // We don't need the entire entity label here.
    $items[$this->path]['title'] = 'Translators';
    $items[$this->path]['type'] = MENU_LOCAL_TASK;
    $items[$this->path . '/add']['title'] = 'Add Translator';
    unset($items[$this->path . '/add']['title callback']);
    unset($items[$this->path . '/add']['title arguments']);
    if (!empty($this->entityInfo['exportable'])) {
      $items[$this->path . '/import']['title'] = 'Import Translator';
      unset($items[$this->path . '/import']['title callback']);
      unset($items[$this->path . '/import']['title arguments']);
    }
    return $items;
  }

  /**
   * Overrides EntityDefaultUIController::overviewTable().
   */
  public function overviewTable($conditions = array()) {
    $table = parent::overviewTable($conditions);
    // Add the plugin column.
    array_splice($table['#header'], 1, 0, array(t('Plugin')));
    return $table;
  }

  /**
   * Overrides EntityDefaultUIController::overviewTableRow().
   */
  public function overviewTableRow($conditions, $id, $entity, $additional_cols = array()) {
    $wrapper = entity_metadata_wrapper('tmgmt_translator', $entity);
    // Add a column to show the translator plugin via the metadata wrapper.
    $additional_cols['plugin'] = $wrapper->translator_plugin->label();
    $row = parent::overviewTableRow($conditions, $id, $entity, $additional_cols);
    // Add the description to the standard entity ui overview item.
    $row[0]['data']['#theme'] = 'tmgmt_ui_translator_overview_item';
    $row[0]['data']['#description'] = $entity->description;
    return $row;
  }

  /**
   * Overrides EntityDefaultUIController::overviewForm().
   */
  public function overviewForm($form, &$form_state) {
    // Just show translator entities if the plugin is available.
    $plugins = tmgmt_translator_plugin_info();
    $conditions = array('translator_plugin' => array_keys($plugins));
    $form['table'] = $this->overviewTable($conditions);
    $form['pager'] = array('#theme' => 'pager');
    return $form;
  }

}

/**
 * Adds a description to the translator entity on the entity overview form.
 *
 * @see theme_entity_ui_overview_item()
 */
function theme_tmgmt_ui_translator_overview_item($variables) {
  $output = theme('entity_ui_overview_item', $variables);
  if (!empty($variables['description'])) {
    $output .= '<div class="description">' . $variables['description'] . '</div>';
  }
  return $output;
}
