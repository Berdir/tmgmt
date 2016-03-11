<?php
/**
 * @file
 * Contains \Drupal\tmgmt_content\Tests\ContentEntitySourceTranslatableEntityTest.
 */

namespace Drupal\tmgmt_content\Tests;

use Drupal\tmgmt_composite_test\Entity\EntityTestComposite;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\tmgmt\Tests\EntityTestBase;

/**
 * Tests always embedded entity reference fields.
 *
 * @group tmgmt
 */
class ContentEntitySourceTranslatableEntityTest extends EntityTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array(
    'node',
    'field',
    'entity_reference',
    'tmgmt_composite_test',
    'tmgmt_content',
  );

  /**
   * {@inheritdoc}
   */
  function setUp() {
    parent::setUp();

    $this->loginAsAdmin(['administer tmgmt']);

    // Create article content type.
    $this->drupalCreateContentType(['type' => 'article', 'name' => 'Article']);

    // Enable entity translations for entity_test_composite and node.
    $content_translation_manager = \Drupal::service('content_translation.manager');
    $content_translation_manager->setEnabled('entity_test_composite', 'entity_test_composite', TRUE);
    $content_translation_manager->setEnabled('node', 'article', TRUE);
  }

  /**
   * Tests that the referenced entities are always embedded.
   */
  public function testTranslatableEntityReferences() {

    // Assert there is NO embedded references yet.
    $this->drupalGet('/admin/tmgmt/settings');
    $xpath = '//*[@id="edit-embedded-fields"]';
    $embedded_entity = '<label for="edit-embedded-fields-always-embedded">Always embedded</label>';
    $embedded_node = '<span class="fieldset-legend">Content</span>';
    $this->assertFalse(strpos($this->xpath($xpath)[0]->asXml(), $embedded_entity));
    $this->assertFalse(strpos($this->xpath($xpath)[0]->asXml(), $embedded_node));

    // Create the reference field to the composite entity test.
    $field_storage = FieldStorageConfig::create(array(
      'field_name' => 'entity_test_composite',
      'entity_type' => 'node',
      'type' => 'entity_reference',
      'settings' => array(
        'target_type' => 'entity_test_composite'
      ),
    ));
    $field_storage->save();
    $field = FieldConfig::create(array(
      'field_storage' => $field_storage,
      'bundle' => 'article',
      'translatable' => FALSE,
    ));
    $field->save();

    // Assert there IS the entity_test_composite as entity embedded now.
    $this->drupalGet('/admin/tmgmt/settings');
    $this->assertTrue(strpos($this->xpath($xpath)[0]->asXml(), $embedded_entity));
    $this->assertFalse(strpos($this->xpath($xpath)[0]->asXml(), $embedded_node));

    // Create the composite entity test.
    $composite = EntityTestComposite::create(array(
      'name' => 'composite name',
    ));
    $composite->save();

    // Create a node with a reference to the composite entity test.
    $node = $this->createNode(array(
      'title' => 'node title',
      'type' => 'article',
      'entity_test_composite' => $composite,
    ));

    // Create a job and job item for the node.
    $job = $this->createJob();
    $job->save();
    $job_item = tmgmt_job_item_create('content', $node->getEntityTypeId(), $node->id(), ['tjid' => $job->id()]);
    $job_item->save();

    // Get the data and check it contains the data for the composite entity.
    $data = $job_item->getData();
    $this->assertTrue(isset($data['entity_test_composite']));
    $this->assertEqual($data['entity_test_composite']['#label'], 'entity_test_composite');
    $this->assertFalse(isset($data['entity_test_composite'][0]['#label']));
    $this->assertEqual($data['entity_test_composite'][0]['entity']['name']['#label'], 'Name');
    $this->assertEqual($data['entity_test_composite'][0]['entity']['name'][0]['value']['#text'], 'composite name');

    // Ensure that only Content is shown in the source select form.
    $this->drupalGet('/admin/tmgmt/sources');
    $this->assertOption('edit-source', 'content:node');
    $this->assertNoOption('edit-source', 'content:entity_test_composite');
  }
}

