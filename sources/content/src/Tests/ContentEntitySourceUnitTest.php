<?php

/**
 * @file
 * Contains Drupal\tmgmt_content\Tests\ContentEntitySourceUnitTest.php
 */

namespace Drupal\tmgmt_content\Tests;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\entity_test\Entity\EntityTestMul;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\node\Entity\Node;
use Drupal\system\Tests\Entity\EntityUnitTestBase;
use Drupal\tmgmt\Entity\Translator;
use Drupal\tmgmt\JobItemInterface;

/**
 * Content entity Source unit tests.
 *
 * @group tmgmt
 */
class ContentEntitySourceUnitTest extends EntityUnitTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('tmgmt', 'tmgmt_content', 'tmgmt_test', 'node', 'filter', 'file', 'image', 'language', 'content_translation', 'options', 'entity_reference');

  protected $entityTypeId = 'entity_test_mul';

  protected $image_label;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    // Add the languages.
    $this->installConfig(['language']);
    ConfigurableLanguage::createFromLangcode('de')->save();
    ConfigurableLanguage::createFromLangcode('cs')->save();

    $this->installEntitySchema('tmgmt_job');
    $this->installEntitySchema('tmgmt_job_item');
    $this->installEntitySchema('tmgmt_message');
    $this->installEntitySchema('entity_test_rev');
    $this->installEntitySchema('entity_test_mulrev');
    $this->installEntitySchema('entity_test_mul');
    $this->installSchema('system', array('router'));
    $this->installSchema('node', array('node_access'));
    \Drupal::moduleHandler()->loadInclude('entity_test', 'install');
    entity_test_install();

    // Make the test field translatable.
    $field_storage = FieldStorageConfig::loadByName('entity_test_mul', 'field_test_text');
    $field_storage->setCardinality(2);
    $field_storage->save();
    $field = FieldConfig::loadByName('entity_test_mul', 'entity_test_mul', 'field_test_text');
    $field->setTranslatable(TRUE);
    $field->save();

    // Add an image field and make it translatable.
    $this->installEntitySchema('file');
    $this->installSchema('file', array('file_usage'));

    $this->installConfig(array('node'));

    \Drupal::service('router.builder')->rebuild();

    $field_storage = FieldStorageConfig::create(array(
      'field_name' => 'image_test',
      'entity_type' => $this->entityTypeId,
      'type' => 'image',
      'cardinality' => FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED,
      'translatable' => TRUE,
    ));
    $field_storage->save();
    FieldConfig::create(array(
      'entity_type' => $this->entityTypeId,
      'field_storage' => $field_storage,
      'bundle' => $this->entityTypeId,
      'label' => $this->image_label = $this->randomMachineName(),
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
    $entity_test = entity_create($this->entityTypeId, $values);
    $translation = $entity_test->getTranslation('en');
    $translation->name->value = $this->randomMachineName();
    $values = array(
      'value' => $this->randomMachineName(),
      'format' => 'text_plain'
    );
    $translation->field_test_text->appendItem($values);
    $translation->field_test_text->appendItem($values);

    $values = array(
      'target_id' => $this->image->id(),
      'alt' => $this->randomMachineName(),
      'title' => $this->randomMachineName(),
    );
    $translation->image_test->appendItem($values);
    $entity_test->save();

    $job = tmgmt_job_create('en', 'de');
    $job->translator = 'test_translator';
    $job->save();
    $job_item = tmgmt_job_item_create('content', $this->entityTypeId, $entity_test->id(), array('tjid' => $job->id()));
    $job_item->save();

    $source_plugin = $this->container->get('plugin.manager.tmgmt.source')->createInstance('content');
    $data = $source_plugin->getData($job_item);

    // Test the name property.
    $this->assertEqual($data['name']['#label'], 'Name');
    $this->assertEqual($data['name'][0]['#label'], 'Delta #0');
    $this->assertEqual((string) $data['name'][0]['value']['#label'], 'Text value');
    $this->assertEqual($data['name'][0]['value']['#text'], $entity_test->name->value);
    $this->assertEqual($data['name'][0]['value']['#translate'], TRUE);

    // Test the test field.
    $this->assertEqual($data['field_test_text']['#label'], 'Test text-field');
    $this->assertEqual($data['field_test_text'][0]['#label'], 'Delta #0');
    $this->assertEqual((string) $data['field_test_text'][0]['value']['#label'], 'Text');
    $this->assertEqual($data['field_test_text'][0]['value']['#text'], $entity_test->field_test_text->value);
    $this->assertEqual($data['field_test_text'][0]['value']['#translate'], TRUE);
    $this->assertEqual((string) $data['field_test_text'][0]['format']['#label'], 'Text format');
    $this->assertEqual($data['field_test_text'][0]['value']['#format'], 'text_plain');
    $this->assertEqual($data['field_test_text'][0]['format']['#text'], $entity_test->field_test_text->format);
    $this->assertEqual($data['field_test_text'][0]['format']['#translate'], FALSE);
    $this->assertFalse(isset($data['field_test_text'][0]['processed']));

    $this->assertEqual($data['field_test_text'][1]['#label'], 'Delta #1');
    $this->assertEqual((string) $data['field_test_text'][1]['value']['#label'], 'Text');
    $this->assertEqual($data['field_test_text'][1]['value']['#text'], $entity_test->field_test_text[1]->value);
    $this->assertEqual($data['field_test_text'][1]['value']['#translate'], TRUE);
    $this->assertEqual((string) $data['field_test_text'][1]['format']['#label'], 'Text format');
    $this->assertEqual($data['field_test_text'][0]['value']['#format'], 'text_plain');
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
    $this->assertEqual($image_item['alt']['#label'], t('Alternative text'));
    $this->assertEqual($image_item['alt']['#text'], $entity_test->image_test->alt);
    $this->assertTrue($image_item['title']['#translate']);
    $this->assertEqual($image_item['title']['#label'], t('Title'));
    $this->assertEqual($image_item['title']['#text'], $entity_test->image_test->title);

    // Now request a translation and save it back.
    $job->requestTranslation();
    $items = $job->getItems();
    $item = reset($items);
    $item->acceptTranslation();
    $data = $item->getData();

    // Check that the translations were saved correctly.
    $entity_test = entity_load($this->entityTypeId, $entity_test->id());
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
    $field = FieldStorageConfig::loadByName('node', 'body');
    $field->setTranslatable(TRUE);
    $field->setCardinality(2);
    $field->save();

    $node = entity_create('node', array(
      'title' => $this->randomMachineName(),
      'uid' => $account->id(),
      'type' => $type->id(),
      'langcode' => 'en',
    ));

    $value = array(
      'value' => $this->randomMachineName(),
      'summary' => $this->randomMachineName(),
      'format' => 'text_plain'
    );
    $node->body->appendItem($value);
    $node->body->appendItem($value);
    $node->save();

    $job = tmgmt_job_create('en', 'de');
    $job->save();
    $job_item = tmgmt_job_item_create('content', 'node', $node->id(), array('tjid' => $job->id()));
    $job_item->save();

    $source_plugin = $this->container->get('plugin.manager.tmgmt.source')->createInstance('content');
    $data = $source_plugin->getData($job_item);

    // Test the title property.
    $this->assertEqual($data['title']['#label'], 'Title');
    $this->assertEqual($data['title'][0]['#label'], 'Delta #0');
    $this->assertEqual((string) $data['title'][0]['value']['#label'], 'Text value');
    $this->assertEqual($data['title'][0]['value']['#text'], $node->title->value);
    $this->assertEqual($data['title'][0]['value']['#translate'], TRUE);

    // Test the body field.
    // @todo: Fields need better labels, needs to be fixed in core.
    $this->assertEqual($data['body']['#label'], 'Body');
    $this->assertEqual($data['body'][0]['#label'], 'Delta #0');
    $this->assertEqual((string) $data['body'][0]['value']['#label'], 'Text');
    $this->assertEqual($data['body'][0]['value']['#text'], $node->body->value);
    $this->assertEqual($data['body'][0]['value']['#translate'], TRUE);
    $this->assertEqual($data['body'][0]['value']['#format'], 'text_plain');
    $this->assertEqual((string) $data['body'][0]['summary']['#label'], 'Summary');
    $this->assertEqual($data['body'][0]['summary']['#text'], $node->body->summary);
    $this->assertEqual($data['body'][0]['summary']['#translate'], TRUE);
    $this->assertEqual((string) $data['body'][0]['format']['#label'], 'Text format');
    $this->assertEqual($data['body'][0]['format']['#text'], $node->body->format);
    $this->assertEqual($data['body'][0]['format']['#translate'], FALSE);
    $this->assertFalse(isset($data['body'][0]['processed']));

    $this->assertEqual($data['body'][1]['#label'], 'Delta #1');
    $this->assertEqual((string) $data['body'][1]['value']['#label'], 'Text');
    $this->assertEqual($data['body'][1]['value']['#text'], $node->body[1]->value);
    $this->assertEqual($data['body'][1]['value']['#translate'], TRUE);
    $this->assertEqual((string) $data['body'][1]['summary']['#label'], 'Summary');
    $this->assertEqual($data['body'][1]['summary']['#text'], $node->body[1]->summary);
    $this->assertEqual($data['body'][1]['summary']['#translate'], TRUE);
    $this->assertEqual($data['body'][0]['summary']['#format'], 'text_plain');
    $this->assertEqual((string) $data['body'][1]['format']['#label'], 'Text format');
    $this->assertEqual($data['body'][1]['format']['#text'], $node->body[1]->format);
    $this->assertEqual($data['body'][1]['format']['#translate'], FALSE);
    $this->assertFalse(isset($data['body'][1]['processed']));

    // Test if language neutral entities can't be added to a translation job.
    $node = entity_create('node', array(
      'title' => $this->randomMachineName(),
      'uid' => $account->id(),
      'type' => $type->id(),
      'langcode' => LanguageInterface::LANGCODE_NOT_SPECIFIED,
    ));
    $node->save();
    try {
      $job = tmgmt_job_create(LanguageInterface::LANGCODE_NOT_SPECIFIED, 'de');
      $job->save();
      $job_item = tmgmt_job_item_create('content', 'node', $node->id(), array('tjid' => $job->id()));
      $job_item->save();
      $this->fail("Adding of language neutral to a translation job did not fail.");
    }
    catch (\Exception $e){
      $this->pass("Adding of language neutral to a translation job did fail.");
    }
  }

  /**
   * Test node acceptTranslation.
   */
  public function testAcceptTranslation() {
    $account = $this->createUser();
    $type = $this->drupalCreateContentType();
    /** @var Translator $translator */
    $translator = Translator::load('test_translator');
    $translator->setAutoAccept(TRUE)->save();
    $node = entity_create('node', array(
      'title' => $this->randomMachineName(),
      'uid' => $account->id(),
      'type' => $type->id(),
      'langcode' => 'en',
    ));
    $node->save();
    $job = tmgmt_job_create('en', 'de');
    $job->translator = 'test_translator';
    $job->save();
    $job_item = tmgmt_job_item_create('content', $node->getEntityTypeId(), $node->id(), array('tjid' => $job->id()));
    $job_item->save();

    // Request translation. Here it fails.
    $job->requestTranslation();
    $items = $job->getItems();
    /** @var \Drupal\tmgmt\Entity\JobItem $item */
    $item = reset($items);
    // As was set to auto_accept, should be accepted.
    $this->assertEqual($item->getState(), JobItemInterface::STATE_ACCEPTED);
  }

  /**
    * Test if the source is able to pull content in requested language.
   */
  public function testRequestDataForSpecificLanguage() {
    // Create an english node.
    $account = $this->createUser();
    $type = $this->drupalCreateContentType();
    $field = FieldStorageConfig::loadByName('node', 'body');
    $field->setTranslatable(TRUE);
    $field->setCardinality(2);
    $field->save();

    $node = Node::create(array(
      'title' => $this->randomMachineName(),
      'uid' => $account->id(),
      'type' => $type->id(),
      'langcode' => 'cs',
    ));

    $node = $node->addTranslation('en');

    $node->title->appendItem(array('value' => $this->randomMachineName()));
    $value = array(
      'value' => $this->randomMachineName(),
      'summary' => $this->randomMachineName(),
      'format' => 'text_plain'
    );
    $node->body->appendItem($value);
    $node->body->appendItem($value);
    $node->save();

    $job = tmgmt_job_create('en', 'de');
    $job->save();
    $job_item = tmgmt_job_item_create('content', 'node', $node->id(), array('tjid' => $job->id()));
    $job_item->save();

    $source_plugin = $this->container->get('plugin.manager.tmgmt.source')->createInstance('content');
    $data = $source_plugin->getData($job_item);
    $this->assertEqual($data['body'][0]['value']['#text'], $value['value']);
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
    $name = strtolower($this->randomMachineName(8));
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
    node_add_body_field($type);

    $this->assertEqual($saved, SAVED_NEW, t('Created content type %type.', array('%type' => $type->id())));

    return $type;
  }

  /**
   * Test extraction and saving translation for embedded references.
   */
  public function testEmbeddedReferences() {
    $field1 = FieldStorageConfig::create(array(
      'field_name' => 'field1',
      'entity_type' => $this->entityTypeId,
      'type' => 'entity_reference',
      'cardinality' => -1,
      'settings' => array('target_type' => $this->entityTypeId),
    ));
    $field1->save();
    $field2 = FieldStorageConfig::create(array(
      'field_name' => 'field2',
      'entity_type' => $this->entityTypeId,
      'type' => 'entity_reference',
      'cardinality' => -1,
      'settings' => array('target_type' => $this->entityTypeId),
    ));
    $field2->save();

    // Create field instances on the content type.
    FieldConfig::create(array(
      'field_storage' => $field1,
      'bundle' => $this->entityTypeId,
      'label' => 'Field 1',
      'translatable' => FALSE,
      'settings' => array(),
    ))->save();
    FieldConfig::create(array(
      'field_storage' => $field2,
      'bundle' => $this->entityTypeId,
      'label' => 'Field 2',
      'translatable' => FALSE,
      'settings' => array(),
    ))->save();

    // Create a test entity that can be referenced.
    $referenced_values = [
      'langcode' => 'en',
      'user_id' => 1,
      'name' => $this->randomString(),
    ];

    $this->config('tmgmt_content.settings')
      ->set('embedded_fields.' . $this->entityTypeId . '.field1', TRUE)
      ->save();

    $referenced_entity = entity_create($this->entityTypeId, $referenced_values);
    $referenced_entity->save();

    // Create an english test entity.
    $values = array(
      'langcode' => 'en',
      'user_id' => 1,
    );
    $entity_test = entity_create($this->entityTypeId, $values);
    $translation = $entity_test->getTranslation('en');
    $translation->name->value = $this->randomMachineName();

    $translation->field1->target_id = $referenced_entity->id();
    $translation->field2->target_id = $referenced_entity->id();

    $entity_test->save();

    $job = tmgmt_job_create('en', 'de');
    $job->translator = 'test_translator';
    $job->save();
    $job_item = tmgmt_job_item_create('content', $this->entityTypeId, $entity_test->id(), array('tjid' => $job->id()));
    $job_item->save();

    $source_plugin = $this->container->get('plugin.manager.tmgmt.source')->createInstance('content');
    $data = $source_plugin->getData($job_item);

    // Ensure that field 2 is not in the extracted data.
    $this->assertFalse(isset($data['field2']));

    // Ensure some labels and structure for field 1.
    $this->assertEqual($data['field1']['#label'], 'Field 1');
    $this->assertEqual($data['field1'][0]['#label'], 'Delta #0');
    $this->assertEqual($data['field1'][0]['entity']['name']['#label'], 'Name');
    $this->assertEqual($data['field1'][0]['entity']['name'][0]['value']['#text'], $referenced_values['name']);

    // Now request a translation and save it back.
    $job->requestTranslation();
    $items = $job->getItems();
    $item = reset($items);
    $item->acceptTranslation();
    $data = $item->getData();

    // Check that the translations were saved correctly.
    $entity_test = entity_load($this->entityTypeId, $entity_test->id());
    $translation = $entity_test->getTranslation('de');

    $referenced_entity = entity_load($this->entityTypeId, $referenced_entity->id());
    $referenced_translation = $referenced_entity->getTranslation('de');
    $this->assertEqual($referenced_translation->name->value, $data['field1'][0]['entity']['name'][0]['value']['#translation']['#text']);

  }

}
