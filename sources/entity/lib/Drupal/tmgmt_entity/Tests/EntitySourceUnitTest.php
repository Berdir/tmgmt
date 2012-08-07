<?php

/**
 * @file
 * Contains Drupal\tmgmt_entity\Tests\EntitySourceUnitTest.php
 */

namespace Drupal\tmgmt_entity\Tests;

use Drupal\system\Tests\Entity\EntityUnitTestBase;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Language\Language;

/**
 * Entity Source unit tests.
 */
class EntitySourceUnitTest extends EntityUnitTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('tmgmt', 'tmgmt_entity', 'tmgmt_test', 'node', 'entity', 'filter', 'file', 'image', 'language');

  protected $entity_type = 'entity_test_mul';

  protected $image_label;

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

    // Add the languages.
    $edit = array(
      'id' => Language::LANGCODE_NOT_SPECIFIED,
    );
    $language = new Language($edit);
    language_save($language);
    $edit = array(
      'id' => 'en',
    );
    $language = new Language($edit);
    language_save($language);
    $edit = array(
      'id' => 'de',
    );
    $language = new Language($edit);
    language_save($language);

    $this->installSchema('node', array('node', 'node_revision', 'node_field_revision', 'node_field_data'));
    $this->installSchema('tmgmt', array('tmgmt_job', 'tmgmt_job_item', 'tmgmt_message'));
    $this->installSchema('entity_test', array('entity_test_rev', 'entity_test_mul', 'entity_test_mulrev', 'entity_test_mul_property_data'));
    $this->installSchema('system', array('router'));
    entity_test_install();

    // Make the test field translatable.
    $field = $this->container->get('field.info')->getField('entity_test_mul', 'field_test_text');
    $field->translatable = 1;
    $field->cardinality = 2;
    $field->save();

    // Add an image field and make it translatable.
    $this->installSchema('file', array('file_managed', 'file_usage'));

    \Drupal::service('router.builder')->rebuild();

    entity_create('field_config', array(
      'name' => 'image_test',
      'entity_type' => $this->entity_type,
      'type' => 'image',
      'cardinality' => FieldDefinitionInterface::CARDINALITY_UNLIMITED,
      'translatable' => TRUE,
    ))->save();
    entity_create('field_instance_config', array(
      'entity_type' => $this->entity_type,
      'field_name' => 'image_test',
      'bundle' => $this->entity_type,
      'label' => $this->image_label = $this->randomName(),
    ))->save();
    file_unmanaged_copy(DRUPAL_ROOT . '/core/misc/druplicon.png', 'public://example.jpg');
    $this->image = entity_create('file', array(
      'uri' => 'public://example.jpg',
    ));
    $this->image->save();

    tmgmt_translator_auto_create(\Drupal::service('plugin.manager.tmgmt.translator')->getDefinition('test_translator'));
  }

  public function testEntityTest() {
    // Create an english test entity.
    $values = array(
      'langcode' => 'en',
      'user_id' => 1,
    );
    $entity_test = entity_create($this->entity_type, $values);
    $translation = $entity_test->getTranslation('en');
    $translation->name->value = $this->randomName();
    $translation->field_test_text->value = $this->randomName();
    $translation->field_test_text->format = 'text_plain';
    $translation->field_test_text[1]->value = $this->randomName();
    $translation->field_test_text[1]->format = 'text_plain';
    $translation->image_test->target_id = $this->image->id();
    $translation->image_test->alt = $alt = $this->randomName();
    $translation->image_test->title = $title = $this->randomName();
    $entity_test->save();

    $job = tmgmt_job_create('en', 'de');
    $job->translator = 'test_translator';
    $job->save();
    $job_item = tmgmt_job_item_create('entity', $this->entity_type, $entity_test->id(), array('tjid' => $job->id()));
    $job_item->save();

    $source_plugin = $this->container->get('plugin.manager.tmgmt.source')->createInstance('entity');
    $data = $source_plugin->getData($job_item);

    // Test the name property.
    $this->assertEqual($data['name']['#label'], 'Name');
    $this->assertEqual($data['name'][0]['#label'], 'Delta #0');
    $this->assertEqual($data['name'][0]['value']['#label'], 'Text value');
    $this->assertEqual($data['name'][0]['value']['#text'], $entity_test->name->value);
    $this->assertEqual($data['name'][0]['value']['#translate'], TRUE);

    // Test the test field.
    // @todo: Fields need better labels, needs to be fixed in core.
    $this->assertEqual($data['field_test_text']['#label'], 'Test text-field');
    $this->assertEqual($data['field_test_text'][0]['#label'], 'Delta #0');
    $this->assertEqual($data['field_test_text'][0]['value']['#label'], 'Text value');
    $this->assertEqual($data['field_test_text'][0]['value']['#text'], $entity_test->field_test_text->value);
    $this->assertEqual($data['field_test_text'][0]['value']['#translate'], TRUE);
    $this->assertEqual($data['field_test_text'][0]['format']['#label'], 'Text format');
    $this->assertEqual($data['field_test_text'][0]['format']['#text'], $entity_test->field_test_text->format);
    $this->assertEqual($data['field_test_text'][0]['format']['#translate'], FALSE);
    $this->assertFalse(isset($data['field_test_text'][0]['processed']));

    $this->assertEqual($data['field_test_text'][1]['#label'], 'Delta #1');
    $this->assertEqual($data['field_test_text'][1]['value']['#label'], 'Text value');
    $this->assertEqual($data['field_test_text'][1]['value']['#text'], $entity_test->field_test_text[1]->value);
    $this->assertEqual($data['field_test_text'][1]['value']['#translate'], TRUE);
    $this->assertEqual($data['field_test_text'][1]['format']['#label'], 'Text format');
    $this->assertEqual($data['field_test_text'][1]['format']['#text'], $entity_test->field_test_text[1]->format);
    $this->assertEqual($data['field_test_text'][1]['format']['#translate'], FALSE);
    $this->assertFalse(isset($data['field_test_text'][1]['processed']));

    // Test the image field.
    $image_item = $data['image_test'][0];
    $this->assertEqual($data['image_test']['#label'], $this->image_label);
    $this->assertEqual($image_item['#label'], 'Delta #0');
    $this->assertFalse($image_item['target_id']['#translate']);
    $this->assertFalse($image_item['width']['#translate']);
    $this->assertFalse($image_item['height']['#translate']);
    $this->assertTrue($image_item['alt']['#translate']);
    $this->assertEqual($image_item['alt']['#label'], t("Alternative image text, for the image's 'alt' attribute."));
    $this->assertEqual($image_item['alt']['#text'], $entity_test->image_test->alt);
    $this->assertTrue($image_item['title']['#translate']);
    $this->assertEqual($image_item['title']['#label'], t("Image title text, for the image's 'title' attribute."));
    $this->assertEqual($image_item['title']['#text'], $entity_test->image_test->title);

    // Now request a translation and save it back.
    $job->requestTranslation();
    $items = $job->getItems();
    $item = reset($items);
    $item->acceptTranslation();
    $data = $item->getData();

    // Check that the translations were saved correctly.
    $entity_test = entity_load($this->entity_type, $entity_test->id());
    $translation = $entity_test->getTranslation('de');

    $this->assertEqual($translation->name->value, $data['name'][0]['value']['#translation']['#text']);
    $this->assertEqual($translation->field_test_text[0]->value, $data['field_test_text'][0]['value']['#translation']['#text']);
    $this->assertEqual($translation->field_test_text[1]->value, $data['field_test_text'][1]['value']['#translation']['#text']);
  }

  /**
   * Test node field extraction.
   */
  public function testNode() {
    // Create an english node.
    $account = $this->createUser();
    $type = $this->drupalCreateContentType();
    $field = $this->container->get('field.info')->getField('node', 'body');
    $field->translatable = TRUE;
    $field->cardinality = 2;
    $field->save();

    $node = entity_create('node', array(
      'uid' => $account->id(),
      'type' => $type->id(),
      'langcode' => 'en',
    ));
    $node->title->value = $this->randomName();
    $node->body->value = $this->randomName();
    $node->body->summary = $this->randomName();
    $node->body->format = 'text_plain';
    $node->body[1]->value = $this->randomName();
    $node->body[1]->summary = $this->randomName();
    $node->body[1]->format = 'text_plain';
    $node->save();

    $job = tmgmt_job_create('en', 'de');
    $job->save();
    $job_item = tmgmt_job_item_create('entity', 'node', $node->id(), array('tjid' => $job->id()));
    $job_item->save();

    $source_plugin = $this->container->get('plugin.manager.tmgmt.source')->createInstance('entity');
    $data = $source_plugin->getData($job_item);

    // Test the title property.
    $this->assertEqual($data['title']['#label'], 'Title');
    $this->assertEqual($data['title'][0]['#label'], 'Delta #0');
    $this->assertEqual($data['title'][0]['value']['#label'], 'Text value');
    $this->assertEqual($data['title'][0]['value']['#text'], $node->title->value);
    $this->assertEqual($data['title'][0]['value']['#translate'], TRUE);

    // Test the body field.
    // @todo: Fields need better labels, needs to be fixed in core.
    $this->assertEqual($data['body']['#label'], 'Body');
    $this->assertEqual($data['body'][0]['#label'], 'Delta #0');
    $this->assertEqual($data['body'][0]['value']['#label'], 'Text value');
    $this->assertEqual($data['body'][0]['value']['#text'], $node->body->value);
    $this->assertEqual($data['body'][0]['value']['#translate'], TRUE);
    $this->assertEqual($data['body'][0]['summary']['#label'], 'Summary text value');
    $this->assertEqual($data['body'][0]['summary']['#text'], $node->body->summary);
    $this->assertEqual($data['body'][0]['summary']['#translate'], TRUE);
    $this->assertEqual($data['body'][0]['format']['#label'], 'Text format');
    $this->assertEqual($data['body'][0]['format']['#text'], $node->body->format);
    $this->assertEqual($data['body'][0]['format']['#translate'], FALSE);
    $this->assertFalse(isset($data['body'][0]['processed']));

    $this->assertEqual($data['body'][1]['#label'], 'Delta #1');
    $this->assertEqual($data['body'][1]['value']['#label'], 'Text value');
    $this->assertEqual($data['body'][1]['value']['#text'], $node->body[1]->value);
    $this->assertEqual($data['body'][1]['value']['#translate'], TRUE);
    $this->assertEqual($data['body'][1]['summary']['#label'], 'Summary text value');
    $this->assertEqual($data['body'][1]['summary']['#text'], $node->body[1]->summary);
    $this->assertEqual($data['body'][1]['summary']['#translate'], TRUE);
    $this->assertEqual($data['body'][1]['format']['#label'], 'Text format');
    $this->assertEqual($data['body'][1]['format']['#text'], $node->body[1]->format);
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
    $name = strtolower($this->randomName(8));
    $values = array(
      'type' => $name,
      'name' => $name,
      'base' => 'node_content',
      'title_label' => 'Title',
      'body_label' => 'Body',
      'has_title' => 1,
      'has_body' => 1,
    );

    $type = entity_create('node_type', $values);
    $saved = $type->save();

    $this->assertEqual($saved, SAVED_NEW, t('Created content type %type.', array('%type' => $type->id())));

    return $type;
  }

}
