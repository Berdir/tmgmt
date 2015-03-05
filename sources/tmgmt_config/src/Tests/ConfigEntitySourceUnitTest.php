<?php

/**
 * @file
 * Contains Drupal\tmgmt_config\Tests\ConfigEntitySourceUnitTest.
 */

namespace Drupal\tmgmt_config\Tests;

use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\node\Entity\NodeType;
use Drupal\system\Tests\Entity\EntityUnitTestBase;
use Drupal\views\Entity\View;

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
  public static $modules = array('tmgmt', 'tmgmt_config', 'tmgmt_test', 'node', 'entity', 'filter', 'language', 'config_translation', 'locale', 'menu_link', 'views', 'views_ui', 'options');

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
    $this->installSchema('locale', array('locales_location'));

    \Drupal::service('router.builder')->rebuild();

    tmgmt_translator_auto_create(\Drupal::service('plugin.manager.tmgmt.translator')->getDefinition('test_translator'));
  }

  /**
   * Tests the node type config entity.
   */
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
    $this->assertEqual($data['name']['#text'], $node_type->label());
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

    $this->assertEqual($node_type->label(), $data['name']['#translation']['#text']);
    $this->assertEqual($node_type->getDescription(), $data['description']['#translation']['#text']);
  }

  /**
   * Tests the view config entity.
   */
  public function testView() {
    $this->installConfig(['system', 'tmgmt']);
    $job = tmgmt_job_create('en', 'de');
    $job->translator = 'test_translator';
    $job->save();
    $job_item = tmgmt_job_item_create('config', 'view', 'tmgmt_job_overview', array('tjid' => $job->id()));
    $job_item->save();

    $source_plugin = $this->container->get('plugin.manager.tmgmt.source')->createInstance('config');
    $data = $source_plugin->getData($job_item);

    // Test the name property.
    $this->assertEqual($data['label']['#label'], 'Label');
    $this->assertEqual($data['label']['#text'], $job_item->label());
    $this->assertEqual($data['label']['#translate'], TRUE);
    $this->assertEqual($data['description']['#label'], 'Administrative description');
    $this->assertEqual($data['description']['#text'], 'Gives a bulk operation overview of translation jobs in the system.');
    $this->assertEqual($data['description']['#translate'], TRUE);
    $this->assertEqual($data['display']['default']['display_title']['#text'], 'Master');
    $this->assertEqual($data['display']['default']['display_options']['exposed_form']['options']['submit_button']['#label'], 'Submit button text');
    $this->assertEqual($data['display']['default']['display_options']['pager']['options']['expose']['items_per_page_label']['#label'], 'Items per page label');

    // Test item types.
    $this->assertEqual($source_plugin->getItemTypes()['view'], t('View'));

    // Now request a translation and save it back.
    $job->requestTranslation();
    $items = $job->getItems();
    $item = reset($items);
    $item->acceptTranslation();
    $data = $item->getData();

    // Check that the translations were saved correctly.
    $language_manager = \Drupal::languageManager();
    $language_manager->setConfigOverrideLanguage($language_manager->getLanguage('de'));
    $view = View::load('tmgmt_job_overview');

    $this->assertEqual($view->label(), $data['label']['#translation']['#text']);
    $this->assertEqual($view->get('description'), $data['description']['#translation']['#text']);

    $display = $view->get('display');
    $this->assertEqual($display['default']['display_options']['title'], $data['label']['#translation']['#text']);
    $this->assertEqual($display['default']['display_options']['exposed_form']['options']['submit_button'], $data['display']['default']['display_options']['exposed_form']['options']['submit_button']['#translation']['#text']);
  }

}
