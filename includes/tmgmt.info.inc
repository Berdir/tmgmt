<?php

/**
 * @file
 * Contains
 */

/**
 * Metadata controller for the job entity.
 */
class TMGMTJobMetadataController extends EntityDefaultMetadataController {

  public function entityPropertyInfo() {
    $info = parent::entityPropertyInfo();
    $properties = &$info[$this->type]['properties'];

    // Make the created and changed property appear as date.
    $properties['changed']['type'] = $properties['created']['type'] = 'date';

    // Use the defined entity label callback instead of the custom label directly.
    $properties['label']['getter callback'] = 'entity_class_label';

    // Add the options list for the available languages.
    $properties['target_language']['options list'] = $properties['source_language']['options list'] = 'entity_metadata_language_list';

    // Add the options list for the defined state constants.
    $properties['state']['options list'] = 'tmgmt_job_states';

    // Add the options list for all available translator plugins.
    $properties['translator']['type'] = 'tmgmt_translator';
    $properties['translator']['options list'] = 'tmgmt_translator_labels';

    $properties['author'] = array(
      'label' => t('Author'),
      'type' => 'user',
      'description' => t('The author of the translation job.'),
      'setter callback' => 'entity_property_verbatim_set',
      'setter permission' => 'administer tmgmt',
      'required' => TRUE,
      'schema field' => 'uid',
    );

    return _tmgmt_override_property_description($info, $this->type);
  }

}

/**
 * Metadata controller for the job item entity.
 */
class TMGMTJobItemMetadataController extends EntityDefaultMetadataController {

  public function entityPropertyInfo() {
    $info = parent::entityPropertyInfo();
    $properties = &$info[$this->type]['properties'];

    // Make the created and changed property appear as date.
    $properties['changed']['type'] = 'date';

    // Add the options list for the defined state constants.
    $properties['state']['options list'] = 'tmgmt_job_item_states';

    $properties['job'] = array(
      'label' => t('Job'),
      'type' => 'tmgmt_job',
      'description' => t('The job that the job item belongs to.'),
      'setter callback' => 'entity_property_verbatim_set',
      'required' => TRUE,
      'schema field' => 'tjid',
    );
    $properties['tjid'] = array(
      'description' => t('Corresponding job entity.'),
      'type' => 'tmgmt_job',
    ) + $properties['tjid'];
    // Add the options list for all available source plugins.
    $properties['plugin']['options list'] = 'tmgmt_source_plugin_labels';

    return _tmgmt_override_property_description($info, $this->type);
  }

}

/**
 * Metadata controller for the job message entity.
 */
class TMGMTJobMessageMetadataController extends EntityDefaultMetadataController {

  public function entityPropertyInfo() {
    $info = parent::entityPropertyInfo();
    $properties = &$info[$this->type]['properties'];

    $properties['job'] = array(
      'label' => t('Job'),
      'type' => 'tmgmt_job',
      'description' => t('The job that the job message belongs to.'),
      'setter callback' => 'entity_property_verbatim_set',
      'required' => TRUE,
      'schema field' => 'tjid',
    );
    $properties['tjid'] = array(
      'description' => t('Corresponding job entity.'),
      'type' => 'tmgmt_job',
    ) + $properties['tjid'];
    // Make the created property appear as date.
    $properties['created']['type'] = 'date';

    // Add the options list for the defined state constants.
    $properties['state_before']['options list'] = $properties['state_after']['options list'] = 'tmgmt_job_states';

    return _tmgmt_override_property_description($info, $this->type);
  }

}

/**
 * Metadata controller for the translator entity.
 */
class TMGMTTranslatorMetadataController extends EntityDefaultMetadataController {

  public function entityPropertyInfo() {
    $info = parent::entityPropertyInfo();
    $properties = &$info[$this->type]['properties'];

    $properties['plugin']['options list'] = 'tmgmt_translator_plugin_labels';

    return _tmgmt_override_property_description($info, $this->type);
  }

}

/**
 * Populates all entity property descriptions based on the schema definition.
 *
 * @param $info
 *  Entity propety info array.
 *
 * @return
 *   The altered entity properties array.
 */
function _tmgmt_override_property_description($info, $entity_type) {
  // Load tmgmt.install so we can access the schema.
  module_load_install('tmgmt');
  $entity_info = entity_get_info($entity_type);
  $schema = tmgmt_schema();
  $fields = $schema[$entity_info['base table']]['fields'];
  $properties = &$info[$entity_type]['properties'];
  foreach ($properties as $name => $property_info) {
    if (isset($fields[$name]['description'])) {
      $properties[$name]['description'] = $fields[$name]['description'];
    }
  }
  return $info;
}
