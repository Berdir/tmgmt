<?php

/**
 * @file
 * Contains Drupal\tmgmt_config\Tests\ConfigEntitySourceUnitTest.php
 */

namespace Drupal\tmgmt_config\Tests;

use Drupal\Core\Language\LanguageInterface;
use Drupal\node\Entity\NodeType;
use Drupal\system\Tests\Entity\EntityUnitTestBase;
use Drupal\Core\Language\Language;

/**
 * Config entity source unit tests.
 */
class ConfigEntitySourceUnitTest extends EntityUnitTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('tmgmt', 'tmgmt_config', 'tmgmt_test', 'node', 'entity', 'filter', 'language', 'config_translation', 'locale');

  /**
   * {@inheritdoc}
   */
  static function getInfo() {
    return array(
      'name' => 'Config Entity Source Unit tests',
      'description' => 'Unit tests for exporting translatable data from config entities and saving it back.',
      'group' => 'Translation Management',
    );
  }

  public function setUp() {
    parent::setUp();

    // Add the languages.
    $edit = array(
      'id' => LanguageInterface::LANGCODE_NOT_SPECIFIED,
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

    $this->installSchema('tmgmt', array('tmgmt_job', 'tmgmt_job_item', 'tmgmt_message'));
    $this->installSchema('system', array('router'));

    \Drupal::service('router.builder')->rebuild();

    tmgmt_translator_auto_create(\Drupal::service('plugin.manager.tmgmt.translator')->getDefinition('test_translator'));
  }

  public function testNodeType() {
    // Create an english test entity.
    $node_type = NodeType::create(array(
      'type' => 'test',
      'name' => 'Node type name',
      'description' => 'Node type description',
      'title_label' => 'Title label',
      'langcode' => 'en',
    ));
    $node_type->save();

    $job = tmgmt_job_create('en', 'de');
    $job->translator = 'test_translator';
    $job->save();
    $job_item = tmgmt_job_item_create('config', 'node_type', $node_type->id(), array('tjid' => $job->id()));
    $job_item->save();

    $source_plugin = $this->container->get('plugin.manager.tmgmt.source')->createInstance('config');
    $data = $source_plugin->getData($job_item);

    // Test the name property.
    $this->assertEqual($data['name']['#label'], 'Name');
    $this->assertEqual($data['name']['#text'], $node_type->name);
    $this->assertEqual($data['name']['#translate'], TRUE);
    $this->assertEqual($data['description']['#label'], 'Description');
    $this->assertEqual($data['description']['#text'], $node_type->description);
    $this->assertEqual($data['description']['#translate'], TRUE);
    $this->assertEqual($data['title_label']['#label'], 'Title label');
    $this->assertEqual($data['title_label']['#text'], $node_type->title_label);
    $this->assertEqual($data['title_label']['#translate'], TRUE);

    // Test item types.
    $this->assertEqual($source_plugin->getItemTypes()['node_type'], t('Content type'));

    // Now request a translation and save it back.
    $job->requestTranslation();
    $items = $job->getItems();
    $item = reset($items);
    $item->acceptTranslation();
    $data = $item->getData();

    // Check that the translations were saved correctly.
    $node_type = entity_load('node_type', $node_type->id());
    $translation = $node_type->getTranslation('de');

    $this->assertEqual($translation->name->value, $data['name'][0]['#translation']['#text']);
    $this->assertEqual($translation->field_test_text[0]->value, $data['field_test_text'][0]['#translation']['#text']);
    $this->assertEqual($translation->field_test_text[1]->value, $data['field_test_text'][1]['#translation']['#text']);
  }

}
