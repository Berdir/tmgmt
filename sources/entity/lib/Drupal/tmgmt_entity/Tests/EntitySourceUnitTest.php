<?php

/**
 * @file
 * Contains Drupal\tmgmt_entity\Tests\EntitySourceUnitTest.php
 */

namespace Drupal\tmgmt_entity\Tests;

use Drupal\system\Tests\Entity\EntityUnitTestBase;

/**
 * Entity Source unit tests.
 */
class EntitySourceUnitTest extends EntityUnitTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('tmgmt', 'tmgmt_entity', 'tmgmt_test', 'node', 'entity', 'filter');

  /**
   * Implements getInfo().
   */
  static function getInfo() {
    return array(
      'name' => 'Entity Source Unit tests',
      'description' => 'Unit tests for exporting translatable data from entities and saving it back.',
      'group' => 'Translation Management',
    );
  }

  public function setUp() {
    parent::setUp();

    entity_test_install();
    $this->installSchema('tmgmt', array('tmgmt_job', 'tmgmt_job_item'));
    $this->installSchema('entity_test', array('entity_test_rev', 'entity_test_mul', 'entity_test_mulrev'));
    $this->installSchema('node', array('node', 'node_revision', 'node_type'));

    // Make the test field translatable.
    $field = field_info_field('field_test_text');
    $field['translatable'] = TRUE;
    $field['cardinality'] = 2;
    field_update_field($field);
  }

  public function testEntityTest() {
    // Create an english test entity.
    $values = array(
      'langcode' => 'en',
      'user_id' => 1,
    );
    $entity_test = entity_create('entity_test', $values);
    $translation = $entity_test->getTranslation('en');
    $translation->name->value = $this->randomName();
    $translation->field_test_text->value = $this->randomName();
    $translation->field_test_text->format = 'text_plain';
    $translation->field_test_text[1]->value = $this->randomName();
    $translation->field_test_text[1]->format = 'text_plain';
    $entity_test->save();

    $job = tmgmt_job_create('en', 'de');
    $job->save();
    $job_item = tmgmt_job_item_create('entity', 'entity_test', $entity_test->id(), array('tjid' => $job->id()));
    $job_item->save();

    $source_plugin = tmgmt_source_plugin_controller('entity');
    $data = $source_plugin->getData($job_item);

    // Test the name property.
    $this->assertEqual($data['name']['#label'], 'Name');
    $this->assertEqual($data['name'][0]['#label'], 'Delta #0');
    $this->assertEqual($data['name'][0]['value']['#label'], 'Text value');
    $this->assertEqual($data['name'][0]['value']['#text'], $entity_test->name->value);
    $this->assertEqual($data['name'][0]['value']['#translate'], TRUE);

    // Test the test field.
    // @todo: Fields need better labels, needs to be fixed in core.
    $this->assertEqual($data['field_test_text']['#label'], 'Field field_test_text');
    $this->assertEqual($data['field_test_text'][0]['#label'], 'Delta #0');
    $this->assertEqual($data['field_test_text'][0]['value']['#label'], 'Text value');
    $this->assertEqual($data['field_test_text'][0]['value']['#text'], $entity_test->field_test_text->value);
    $this->assertEqual($data['field_test_text'][0]['value']['#translate'], TRUE);
    $this->assertEqual($data['field_test_text'][0]['format']['#label'], 'Text format');
    $this->assertEqual($data['field_test_text'][0]['format']['#text'], $entity_test->field_test_text->format);
    $this->assertEqual($data['field_test_text'][0]['format']['#translate'], FALSE);
    $this->assertFalse(isset($data['field_test_text'][0]['processed']));

    $this->assertEqual($data['field_test_text']['#label'], 'Field field_test_text');
    $this->assertEqual($data['field_test_text'][1]['#label'], 'Delta #1');
    $this->assertEqual($data['field_test_text'][1]['value']['#label'], 'Text value');
    $this->assertEqual($data['field_test_text'][1]['value']['#text'], $entity_test->field_test_text[1]->value);
    $this->assertEqual($data['field_test_text'][1]['value']['#translate'], TRUE);
    $this->assertEqual($data['field_test_text'][1]['format']['#label'], 'Text format');
    $this->assertEqual($data['field_test_text'][1]['format']['#text'], $entity_test->field_test_text[1]->format);
    $this->assertEqual($data['field_test_text'][1]['format']['#translate'], FALSE);
    $this->assertFalse(isset($data['field_test_text'][1]['processed']));

  }

  /**
   * Test node field extraction.
   */
  public function testNode() {
    // Create an english node.
    $account = $this->createUser();
    $type = $this->drupalCreateContentType();
    $field = field_info_field('body');
    $field['translatable'] = TRUE;
    $field['cardinality'] = 2;
    field_update_field($field);

    $translation = entity_create('node', array(
      'uid' => $account->id(),
      'type' => $type->type,
      'title' => 'Test node',
      'status' => 1,
      'comment' => 2,
      'promote' => 0,
      'sticky' => 0,
      'langcode' => LANGUAGE_NOT_SPECIFIED,
      'created' => REQUEST_TIME,
      'changed' => REQUEST_TIME,
    ));
    $translation = $translation->getTranslation('en');
    $translation->title->value = $this->randomName();
    $translation->body->value = $this->randomName();
    $translation->body->summary = $this->randomName();
    $translation->body->format = 'text_plain';
    $translation->body[1]->value = $this->randomName();
    $translation->body[1]->summary = $this->randomName();
    $translation->body[1]->format = 'text_plain';
    $translation->save();

    $job = tmgmt_job_create('en', 'de');
    $job->save();
    $job_item = tmgmt_job_item_create('entity', 'node', $translation->id(), array('tjid' => $job->id()));
    $job_item->save();

    $source_plugin = tmgmt_source_plugin_controller('entity');
    $data = $source_plugin->getData($job_item);

    // Test the title property.
    /*$this->assertEqual($data['name']['#label'], 'Name');
    $this->assertEqual($data['name'][0]['#label'], 'Delta #0');
    $this->assertEqual($data['name'][0]['value']['#label'], 'Text value');
    $this->assertEqual($data['name'][0]['value']['#text'], $node->name->value);
    $this->assertEqual($data['name'][0]['value']['#translate'], TRUE);*/

    // Test the body field.
    // @todo: Fields need better labels, needs to be fixed in core.
    $this->assertEqual($data['body']['#label'], 'Field body');
    $this->assertEqual($data['body'][0]['#label'], 'Delta #0');
    $this->assertEqual($data['body'][0]['value']['#label'], 'Text value');
    $this->assertEqual($data['body'][0]['value']['#text'], $translation->body->value);
    $this->assertEqual($data['body'][0]['value']['#translate'], TRUE);
    $this->assertEqual($data['body'][0]['summary']['#label'], 'Summary text value');
    $this->assertEqual($data['body'][0]['summary']['#text'], $translation->body->summary);
    $this->assertEqual($data['body'][0]['summary']['#translate'], TRUE);
    $this->assertEqual($data['body'][0]['format']['#label'], 'Text format');
    $this->assertEqual($data['body'][0]['format']['#text'], $translation->body->format);
    $this->assertEqual($data['body'][0]['format']['#translate'], FALSE);
    $this->assertFalse(isset($data['body'][0]['processed']));

    $this->assertEqual($data['body']['#label'], 'Field body');
    $this->assertEqual($data['body'][1]['#label'], 'Delta #1');
    $this->assertEqual($data['body'][1]['value']['#label'], 'Text value');
    $this->assertEqual($data['body'][1]['value']['#text'], $translation->body[1]->value);
    $this->assertEqual($data['body'][1]['value']['#translate'], TRUE);
    $this->assertEqual($data['body'][1]['summary']['#label'], 'Summary text value');
    $this->assertEqual($data['body'][1]['summary']['#text'], $translation->body[1]->summary);
    $this->assertEqual($data['body'][1]['summary']['#translate'], TRUE);
    $this->assertEqual($data['body'][1]['format']['#label'], 'Text format');
    $this->assertEqual($data['body'][1]['format']['#text'], $translation->body[1]->format);
    $this->assertEqual($data['body'][1]['format']['#translate'], FALSE);
    $this->assertFalse(isset($data['body'][1]['processed']));
  }

  /**
   * Creates a custom content type based on default settings.
   *
   * @param $settings
   *   An array of settings to change from the defaults.
   *   Example: 'type' => 'foo'.
   * @return
   *   Created content type.
   */
  protected function drupalCreateContentType($settings = array()) {
    // Find a non-existent random type name.
    do {
      $name = strtolower($this->randomName(8));
    } while (node_type_load($name));

    // Populate defaults array.
    $defaults = array(
      'type' => $name,
      'name' => $name,
      'base' => 'node_content',
      'description' => '',
      'help' => '',
      'title_label' => 'Title',
      'body_label' => 'Body',
      'has_title' => 1,
      'has_body' => 1,
    );
    // Imposed values for a custom type.
    $forced = array(
      'orig_type' => '',
      'old_type' => '',
      'module' => 'node',
      'custom' => 1,
      'modified' => 1,
      'locked' => 0,
    );
    $type = $forced + $settings + $defaults;
    $type = (object) $type;

    $saved_type = node_type_save($type);
    node_types_rebuild();
    menu_router_rebuild();
    node_add_body_field($type);

    $this->assertEqual($saved_type, SAVED_NEW, t('Created content type %type.', array('%type' => $type->type)));

    return $type;
  }

}
