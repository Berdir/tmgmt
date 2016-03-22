<?php

/**
 * @file
 * Contains Drupal\Tests\tmgmt_content\Kernel\ContentEntitySourceUnitTest.php
 */

namespace Drupal\Tests\tmgmt_content\Kernel;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\KernelTests\Core\Entity\EntityKernelTestBase;
use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\tmgmt\ContinuousTranslatorInterface;
use Drupal\tmgmt\Entity\Translator;
use Drupal\tmgmt\JobItemInterface;
use Drupal\tmgmt\Entity\Job;

/**
 * Content entity Source unit tests.
 *
 * @group tmgmt
 */
class ContentEntitySourceUnitTest extends EntityKernelTestBase {

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
    $this->installEntitySchema('tmgmt_remote');
    $this->installEntitySchema('tmgmt_message');
    $this->installEntitySchema('entity_test_rev');
    $this->installEntitySchema('entity_test_mulrev');
    $this->installEntitySchema('entity_test_mul');
    $this->container->get('content_translation.manager')->setEnabled('entity_test_mul', 'entity_test_mul', TRUE);
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
    $this->assertFalse(isset($data['name'][0]['#label']));
    $this->assertFalse(isset($data['name'][0]['value']['#label']));
    $this->assertEqual($data['name'][0]['value']['#text'], $entity_test->name->value);
    $this->assertEqual($data['name'][0]['value']['#translate'], TRUE);

    // Test the test field.
    $this->assertEqual($data['field_test_text']['#label'], 'Test text-field');
    $this->assertEqual($data['field_test_text'][0]['#label'], 'Delta #0');
    $this->assertFalse(isset($data['field_test_text'][0]['value']['#label']));
    $this->assertEqual($data['field_test_text'][0]['value']['#text'], $entity_test->field_test_text->value);
    $this->assertEqual($data['field_test_text'][0]['value']['#translate'], TRUE);
    $this->assertFalse(isset($data['field_test_text'][0]['format']['#label']));
    $this->assertEqual($data['field_test_text'][0]['value']['#format'], 'text_plain');
    $this->assertEqual($data['field_test_text'][0]['format']['#text'], $entity_test->field_test_text->format);
    $this->assertEqual($data['field_test_text'][0]['format']['#translate'], FALSE);
    $this->assertFalse(isset($data['field_test_text'][0]['processed']));

    $this->assertEqual($data['field_test_text'][1]['#label'], 'Delta #1');
    $this->assertFalse(isset($data['field_test_text'][1]['value']['#label']));
    $this->assertEqual($data['field_test_text'][1]['value']['#text'], $entity_test->field_test_text[1]->value);
    $this->assertEqual($data['field_test_text'][1]['value']['#translate'], TRUE);
    $this->assertFalse(isset($data['field_test_text'][1]['format']['#label']));
    $this->assertEqual($data['field_test_text'][0]['value']['#format'], 'text_plain');
    $this->assertEqual($data['field_test_text'][1]['format']['#text'], $entity_test->field_test_text[1]->format);
    $this->assertEqual($data['field_test_text'][1]['format']['#translate'], FALSE);
    $this->assertFalse(isset($data['field_test_text'][1]['processed']));

    // Test the image field.
    $image_item = $data['image_test'][0];
    $this->assertEqual($data['image_test']['#label'], $this->image_label);
    $this->assertFalse(isset($image_item['#label']));
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
    $this->assertFalse(isset($data['title'][0]['#label']));
    $this->assertFalse(isset($data['title'][0]['value']['#label']));
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
    $this->container->get('content_translation.manager')->setEnabled('node', $type->id(), TRUE);
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

    // Test that the source language is set correctly.
    $node = Node::load($node->id());
    $manager = $this->container->get('content_translation.manager');
    $this->assertEquals('en', $manager->getTranslationMetadata($node->getTranslation('de'))->getSource(), 'Source language is correct.');
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
    $this->assertFalse(isset($data['field1'][0]['#label']));
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

  /**
   * Test creation of continuous job items.
   */
  public function testContinuousJobItems() {
    $account = $this->createUser();
    $type = $this->drupalCreateContentType();
    $second_type = $this->drupalCreateContentType();

    // Enable entity translations for nodes.
    $content_translation_manager = \Drupal::service('content_translation.manager');
    $content_translation_manager->setEnabled('node', $type->label(), TRUE);
    $content_translation_manager->setEnabled('node', $second_type->label(), TRUE);

    // Create test translator for continuous job.
    $translator = Translator::load('test_translator');

    // Continuous settings configuration.
    $continuous_settings = [
      'content' => [
        'node' => [
          'enabled' => 1,
          'bundles' => [
            $type->id() => 1,
            $second_type->id() => 0,
          ],
        ],
      ],
    ];

    // Create continuous job with source language set to english.
    $continuous_job = tmgmt_job_create('en', 'de', $account->id(), [
      'job_type' => Job::TYPE_CONTINUOUS,
      'translator' => $translator,
      'continuous_settings' => $continuous_settings,
    ]);
    $this->assertEqual(SAVED_NEW, $continuous_job->save());

    // Create an english node.
    $node = entity_create('node', array(
      'title' => $this->randomMachineName(),
      'uid' => $account->id(),
      'type' => $type->id(),
      'langcode' => 'en',
    ));
    $node->save();

    // Test hook_entity_insert() for english node.
    $continuous_job_items = $continuous_job->getItems();
    $continuous_job_item = reset($continuous_job_items);
    $this->assertEqual($node->label(), $continuous_job_item->label(), 'Continuous job item is automatically created for an english node.');

    // Test that continuous job item is in state review.
    $this->assertEqual($continuous_job_item->getState(), JobItemInterface::STATE_REVIEW, 'Translation for an english node is in state review.');

    // Update english node.
    $node->set('title', $this->randomMachineName());
    $node->save();

    // Test that there is no new job item.
    $this->assertEqual(count($continuous_job->getItems()), 1, 'There are no new job items for an english node.');

    // Accept translation for an english node.
    $continuous_job_item->acceptTranslation();

    // Test that the translation for an english node is created and saved.
    $node = entity_load('node', $node->id());
    $translation = $node->getTranslation('de');
    $data = $continuous_job_item->getData();
    $this->assertEqual($translation->label(), $data['title'][0]['value']['#translation']['#text'], 'Translation for an english node has been saved correctly.');
    $this->assertEqual($continuous_job_item->getState(), JobItemInterface::STATE_ACCEPTED, 'Translation for an english node has been accepted.');

    // Create a german node.
    $german_node = entity_create('node', array(
      'title' => $this->randomMachineName(),
      'uid' => $account->id(),
      'type' => $type->id(),
      'langcode' => 'de',
    ));
    $german_node->save();

    // Test that there is no new item for german node.
    $this->assertEqual(count($continuous_job->getItems()), 1, 'Continuous job item is not created for a german node.');

    // Create new english node with different type.
    $second_node = entity_create('node', array(
      'title' => $this->randomMachineName(),
      'uid' => $account->id(),
      'type' => $second_type->id(),
      'langcode' => 'en',
    ));
    $second_node->save();

    // Test that there is no new item for second english node.
    $this->assertEqual(count($continuous_job->getItems()), 1, 'Continuous job item is not created for a second english node.');

    // Update english node.
    $node->set('title', $this->randomMachineName());
    $node->save();

    // Test that there are no new job items for english node because it's
    // translation is not outdated.
    $this->assertEqual(count($continuous_job->getItems()), 1, 'Continuous job item is not created for an updated english node.');

    // Set the outdated flag to true.
    $translation = $node->getTranslation('de');
    $translation->content_translation_outdated->value = 1;
    $translation->save();

    // Test that there are now two items for english node.
    $this->assertEqual(count($continuous_job->getItems()), 2, 'Continuous job item is automatically created for an updated english node.');

    $continuous_job_item_recent = $continuous_job->getMostRecentItem('content', $node->getEntityTypeId(), $node->id());

    // Set job item state to aborted.
    $continuous_job_item_recent->setState(JobItemInterface::STATE_ABORTED, NULL, array(), 'status');

    // Update english node.
    $node->set('title', $this->randomMachineName());
    $node->save();

    // Test that there are now three items for english node.
    $this->assertEqual(count($continuous_job->getItems()), 3, 'Continuous job item is automatically created for an updated english node.');
  }

  /**
   * Test submit continuous job items on cron.
   */
  public function testSubmitContinuousOnCron() {
    $account = $this->createUser();
    $type = $this->drupalCreateContentType();
    $second_type = $this->drupalCreateContentType();

    // Enable entity translations for nodes.
    $content_translation_manager = \Drupal::service('content_translation.manager');
    $content_translation_manager->setEnabled('node', $type->id(), TRUE);
    $content_translation_manager->setEnabled('node', $second_type->id(), TRUE);

    // Create test translator for continuous job.
    $translator = Translator::load('test_translator');

    // Continuous settings configuration.
    $continuous_settings = [
      'content' => [
        'node' => [
          'enabled' => 1,
          'bundles' => [
            $type->id() => 1,
            $second_type->id() => 0,
          ],
        ],
      ],
    ];

    $this->config('tmgmt.settings')
      ->set('submit_job_item_on_cron', TRUE)
      ->set('job_items_cron_limit', 3)
      ->save();

    $first_job = tmgmt_job_create('en', 'de', $account->id(), [
      'job_type' => Job::TYPE_CONTINUOUS,
      'translator' => $translator,
      'continuous_settings' => $continuous_settings,
    ]);
    $first_job->save();

    // Create an english node.
    $first_node = Node::create([
      'title' => $this->randomMachineName(),
      'uid' => $account->id(),
      'type' => $type->id(),
      'langcode' => 'en',
    ]);
    $first_node->save();

    $first_items = array_values($first_job->getItems());
    foreach ($first_items as $job_item) {
      $this->assertEqual($job_item->getState(), JobItemInterface::STATE_INACTIVE, 'Job item is inactive before cron run');
    }

    // Test that there is one job item for an english node.
    $this->assertEqual(count($first_job->getItems()), 1, 'There is one job item for an english node.');

    // Update english node.
    $first_node->set('title', $this->randomMachineName());
    $first_node->save();

    // Test that there is no new job item for updated english node.
    $this->assertEqual(count($first_job->getItems()), 1, 'There are no new job items for updated english node.');

    // Test that job item's data is updated properly.
    $first_job_items = $first_job->getItems();
    $first_job_item = reset($first_job_items);
    $data = $first_job_item->getData();
    $this->assertEqual($first_node->label(), $data['title'][0]['value']['#text'], 'Data in job item has been updated properly.');

    $second_job = tmgmt_job_create('de', 'en', $account->id(), [
      'job_type' => Job::TYPE_CONTINUOUS,
      'translator' => $translator,
      'continuous_settings' => $continuous_settings,
    ]);
    $second_job->save();

    // Create a german node.
    $second_node = Node::create([
      'title' => $this->randomMachineName(),
      'uid' => $account->id(),
      'type' => $type->id(),
      'langcode' => 'de',
    ]);
    $second_node->save();
    // Create a german node.
    $third_node = Node::create([
      'title' => $this->randomMachineName(),
      'uid' => $account->id(),
      'type' => $type->id(),
      'langcode' => 'de',
    ]);
    $third_node->save();

    $second_items = array_values($second_job->getItems());
    foreach ($second_items as $job_item) {
      $this->assertEqual($job_item->getState(), JobItemInterface::STATE_INACTIVE, 'Job item is inactive before cron run');
    }

    $third_job = tmgmt_job_create('cs', 'en', $account->id(), [
      'job_type' => Job::TYPE_CONTINUOUS,
      'translator' => $translator,
      'continuous_settings' => $continuous_settings,
    ]);
    $third_job->save();
    // Create 3 sample nodes.
    for ($i = 0; $i < 3; $i++) {
      $node = Node::create([
        'title' => $this->randomMachineName(),
        'uid' => $account->id(),
        'type' => $type->id(),
        'langcode' => 'cs',
      ]);
      $node->save();
    }

    tmgmt_cron();

    // Assert that the translator was called twice, once with the item of the
    // first job and once with the 2 items of the second job.
    $expected_groups = [
      [
        ['item_id' => $first_items[0]->id(), 'job_id' => $first_items[0]->getJobId()]
      ],
      [
        ['item_id' => $second_items[0]->id(), 'job_id' => $second_items[0]->getJobId()],
        ['item_id' => $second_items[1]->id(), 'job_id' => $second_items[1]->getJobId()]
      ]
    ];

    // Check job items is properly grouped and we have exactly 2 groups.
    $this->assertEqual(\Drupal::state()->get('job_item_groups'), $expected_groups, 'Job items groups are equal');

    foreach ($first_job->getItems() as $job_item) {
      $this->assertEqual($job_item->getState(), JobItemInterface::STATE_REVIEW, 'Job item is active after cron run');
    }

    foreach ($second_job->getItems() as $job_item) {
      $this->assertEqual($job_item->getState(), JobItemInterface::STATE_REVIEW, 'Job item is active after cron run');
    }

    // Run cron again to process 3 remaining job items.
    tmgmt_cron();

    $third_items = array_values($third_job->getItems());
    $expected_groups[] = [
      ['item_id' => $third_items[0]->id(), 'job_id' => $third_items[0]->getJobId()],
      ['item_id' => $third_items[1]->id(), 'job_id' => $third_items[1]->getJobId()],
      ['item_id' => $third_items[2]->id(), 'job_id' => $third_items[2]->getJobId()],
    ];
    // Assert there are 3 new job items appeared from the third job.
    $this->assertEqual(\Drupal::state()->get('job_item_groups'), $expected_groups, 'Job items groups are equal');
    foreach ($third_job->getItems() as $job_item) {
      $this->assertEqual($job_item->getState(), JobItemInterface::STATE_REVIEW, 'Job item is active after cron run');
    }
  }

  /**
   * Test abortion of continuous translators.
   */
  public function testContinuousTranslatorsAbortion() {
    \Drupal::service('router.builder')->rebuild();
    // Create a continuous translator.
    $translator = Translator::load('test_translator');
    $this->assertTrue($translator->getPlugin() instanceof ContinuousTranslatorInterface);

    // Create a node type.
    $type = NodeType::create(['type' => $this->randomMachineName()]);
    $type->save();

    // Enable the node type for translation.
    $content_translation_manager = \Drupal::service('content_translation.manager');
    $content_translation_manager->setEnabled('node', $type->id(), TRUE);

    // Create a continuous job.
    $continuous_job = tmgmt_job_create('en', 'de', 0, [
      'job_type' => Job::TYPE_CONTINUOUS,
      'continuous_settings' => [
        'content' => [
          'node' => [
            'enabled' => TRUE,
            'bundles' => [
              $type->id() => TRUE,
            ],
          ],
        ],
      ],
    ]);
    $continuous_job->translator = $translator;
    $continuous_job->save();

    // Abort a continuous job.
    $continuous_job->aborted();

    // Create a node.
    $node = Node::create(array(
      'title' => $this->randomMachineName(),
      'type' => $type->id(),
      'language' => 'en',
      'body' => $this->randomMachineName(),
    ));
    $node->save();

    // Assert that node has not been captured.
    $updated_continuous_job = Job::load($continuous_job->id());
    $this->assertEqual($updated_continuous_job->getItems(), []);
    $this->assertEqual($updated_continuous_job->getState(), Job::STATE_ABORTED);
  }

}
