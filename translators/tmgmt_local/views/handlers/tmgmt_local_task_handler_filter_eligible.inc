<?php

/**
 * Field handler which shows the link for assign translation task to selected
 * user.
 *
 * @ingroup views_field_handlers
 */
class tmgmt_local_task_handler_filter_eligible extends views_handler_filter {

  /**
   * {@inheritdoc}
   */
  public function query() {
    $this->ensure_my_table();
    $source = $this->table_alias . '.source_language';
    $target = $this->table_alias . '.target_language';
    // Add a new group for the language abilities, which are a set of source
    // and target language combinations.
    $this->query->set_where_group('OR', 'eligible');
    // Return all language abilities for the current user.
    foreach (tmgmt_local_supported_language_pairs(NULL, array(\Drupal::currentUser()->id())) as $key => $ability) {
      $key = str_replace('-', '_', $key);
      $arguments = array(':source_' . $key => $ability['source_language'], ':target_' . $key => $ability['target_language']);
      $this->query->add_where_expression('eligible', "$source = :source_$key AND $target = :target_$key", $arguments);
    }
  }
}
