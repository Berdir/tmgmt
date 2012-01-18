<?php

class TMGMTJobMetadataController extends EntityDefaultMetadataController {

  public function entityPropertyInfo() {
    $info = parent::entityPropertyInfo();
    $properties = &$info[$this->type]['properties'];

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
