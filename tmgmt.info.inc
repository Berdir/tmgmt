<?php

class TMGMTJobMetadataController extends EntityDefaultMetadataController {

  public function entityPropertyInfo() {
    $info = parent::entityPropertyInfo();
    $properties = &$info[$this->type]['properties'];

    // Make the created and changed property appear as date.
    $properties['changed']['type'] = $properties['created']['type'] = 'date';

    // Add the options list for the available languages.
    $properties['target_language']['options list'] = $properties['source_language']['options list'] = 'entity_metadata_language_list';

    // Add the options list for the defined state constants.
    $properties['state']['options list'] = array('TMGMTJob', 'getStatesList');

    // Add the options list for all available translator plugins.
    $properties['translator']['options list'] = 'tmgmt_translator_plugin_labels';

    return _tmgmt_override_property_description($info, $this->type);
  }

}

class TMGMTJobItemMetadataController extends EntityDefaultMetadataController {

  public function entityPropertyInfo() {
    $info = parent::entityPropertyInfo();
    $properties = &$info[$this->type]['properties'];

    $properties['tjid'] = array(
      'description' => t('Corresponding job entity.'),
      'type' => 'tmgmt_job',
    ) + $properties['tjid'];

    // Add the options list for all available source plugins.
    $properties['source_plugin']['options list'] = 'tmgmt_source_plugin_labels';

    return _tmgmt_override_property_description($info, $this->type);
  }

}

class TMGMTJobMessageMetadataController extends EntityDefaultMetadataController {

  public function entityPropertyInfo() {
    $info = parent::entityPropertyInfo();
    $properties = &$info[$this->type]['properties'];

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

class TMGMTTranslatorMetadataController extends EntityDefaultMetadataController {

  public function entityPropertyInfo() {
    $info = parent::entityPropertyInfo();
    return _tmgmt_override_property_description($info, $this->type);
  }

}

/**
 * Populates all entity property descriptions with the values from the
 * description of the corresponding entity base table.
 *
 * @param array $info
 *
 * @return array
 *   The altered properties array.
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
