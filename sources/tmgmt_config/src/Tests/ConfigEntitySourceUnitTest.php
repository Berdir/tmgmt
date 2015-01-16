<?php

/**
 * @file
 * Contains Drupal\tmgmt_config\Tests\ConfigEntitySourceUnitTest.
 */

namespace Drupal\tmgmt_config\Tests;

use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\node\Entity\NodeType;
use Drupal\system\Tests\Entity\EntityUnitTestBase;

/**
 * Unit tests for exporting translatable data from config entities and saving it back.
 *
 * @group tmgmt
 */
class ConfigEntitySourceUnitTest extends EntityUnitTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('tmgmt', 'tmgmt_config', 'tmgmt_test', 'node', 'entity', 'filter', 'language', 'config_translation', 'locale', 'menu_link');

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    // Add the languages.
    $this->installConfig(['language']);
    ConfigurableLanguage::createFromLangcode('de')->save();

    $this->installEntitySchema('tmgmt_job');
    $this->installEntitySchema('tmgmt_job_item');
    $this->installEntitySchema('tmgmt_message');
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
    $this->assertEqual($data['name']['#text'], $node_type->id());
    $this->assertEqual($data['name']['#translate'], TRUE);
    $this->assertEqual($data['description']['#label'], 'Description');
    $this->assertEqual($data['description']['#text'], $node_type->getDescription());
    $this->assertEqual($data['description']['#translate'], TRUE);

    // Test item types.
    $this->assertEqual($source_plugin->getItemTypes()['node_type'], t('Content type'));

    // Now request a translation and save it back.
    $job->requestTranslation();
    $items = $job->getItems();
    $item = reset($items);
    $item->acceptTranslation();
    $data = $item->getData();

    // Check that the translations were saved correctly.
    $language_manager = \Drupal::languageManager();
    $language_manager->setConfigOverrideLanguage($language_manager->getLanguage('de'));
    $node_type = entity_load('node_type', $node_type->id());

    $this->assertEqual($node_type->id(), $data['name']['#translation']['#text']);
    $this->assertEqual($node_type->getDescription(), $data['description']['#translation']['#text']);
  }

}
