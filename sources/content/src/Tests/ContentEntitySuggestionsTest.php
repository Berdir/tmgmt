<?php

/**
 * @file
 * Contains \Drupal\tmgmt_content\Tests\ContentEntitySuggestionsTest.
 */

namespace Drupal\tmgmt_content\Tests;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\tmgmt\Tests\TMGMTKernelTestBase;

/**
 * Basic Source-Suggestions tests.
 *
 * @group tmgmt
 */
class ContentEntitySuggestionsTest extends TMGMTKernelTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('tmgmt_content', 'tmgmt_test', 'content_translation', 'node', 'filter', 'entity_reference');

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->installEntitySchema('node');
    $this->installConfig(['node']);
  }

  /**
   * Prepare a node to get suggestions from.
   *
   * Creates a node with two file fields. The first one is not translatable,
   * the second one is. Both fields got two files attached, where one has
   * translatable content (title and atl-text) and the other one not.
   *
   * @return object
   *   The node which is prepared with all needed fields for the suggestions.
   */
  protected function prepareTranslationSuggestions() {
    // Create a content type with fields.
    // Only the first field is a translatable reference.
    $type = NodeType::create(['type' => $this->randomMachineName()]);
    $type->save();

    $content_translation_manager = \Drupal::service('content_translation.manager');
    $content_translation_manager->setEnabled('node', $type->id(), TRUE);

    $field1 = FieldStorageConfig::create(array(
      'field_name' => 'field1',
      'entity_type' => 'node',
      'type' => 'entity_reference',
      'cardinality' => -1,
      'settings' => array('target_type' => 'node'),
    ));
    $field1->save();
    $field2 = FieldStorageConfig::create(array(
      'field_name' => 'field2',
      'entity_type' => 'node',
      'type' => 'entity_reference',
      'cardinality' => -1,
      'settings' => array('target_type' => 'node'),
    ));
    $field2->save();

    // Create field instances on the content type.
    FieldConfig::create(array(
      'field_storage' => $field1,
      'bundle' => $type->id(),
      'label' => 'Field 1',
      'translatable' => FALSE,
      'settings' => array(),
    ))->save();
    FieldConfig::create(array(
      'field_storage' => $field2,
      'bundle' => $type->id(),
      'label' => 'Field 2',
      'translatable' => TRUE,
      'settings' => array(),
    ))->save();

    // Create a translatable body field.
    node_add_body_field($type);
    $field = FieldConfig::loadByName('node', $type->id(), 'body');
    $field->setTranslatable(TRUE);
    $field->save();

    // Create 4 nodes to be referenced.
    $references = array();
    for ($i = 0; $i < 4; $i++) {
      $references[$i] = Node::create(array(
        'title' => $this->randomMachineName(),
        'body' => $this->randomMachineName(),
        'type' => $type->id(),
      ));
      $references[$i]->save();
    }

    // Create a node with two translatable and two non-translatable references.
    $node = Node::create(array(
      'title' => $this->randomMachineName(),
      'type' => $type->id(),
      'language' => 'en',
      'body' => $this->randomMachineName(),
      $field1->getName() => array(
        array(
          'target_id' => $references[0]->id(),
        ),
        array(
          'target_id' => $references[1]->id(),
        ),
      ),
      $field2->getName() => array(
      array(
        'target_id' => $references[2]->id(),
      ),
      array(
        'target_id' => $references[3]->id(),
      ),
    )));
    $node->save();
    return $node;
  }

  /**
   * Test suggested entities from a translation job.
   */
  public function testSuggestions() {
    // Prepare a job and a node for testing.
    $job = $this->createJob();
    $node = $this->prepareTranslationSuggestions();
    $item = $job->addItem('content', 'node', $node->id());

    // Get all suggestions and clean the list.
    $suggestions = $job->getSuggestions();
    $job->cleanSuggestionsList($suggestions);

    // Check for one suggestion.
    $this->assertEqual(count($suggestions), 2, 'Found two suggestions.');

    // Check for valid attributes on the suggestions.
    $suggestion = array_shift($suggestions);
    $this->assertEqual($suggestion['job_item']->getWordCount(), 2, 'Two translatable words in the suggestion.');
    $this->assertEqual($suggestion['job_item']->getPlugin(), 'content', 'Got a content entity as plugin in the suggestion.');
    $this->assertEqual($suggestion['job_item']->getItemType(), 'node', 'Got a node in the suggestion.');
    $this->assertEqual($suggestion['job_item']->getItemId(), $node->field2[0]->target_id, 'Node id match between node and suggestion.');
    $this->assertEqual($suggestion['reason'], 'Field Field 2');
    $this->assertEqual($suggestion['from_item'], $item->id());
    $job->addExistingItem($suggestion['job_item']);

    $suggestion = array_shift($suggestions);
    $this->assertEqual($suggestion['job_item']->getWordCount(), 2, 'Two translatable words in the suggestion.');
    $this->assertEqual($suggestion['job_item']->getPlugin(), 'content', 'Got a content entity as plugin in the suggestion.');
    $this->assertEqual($suggestion['job_item']->getItemType(), 'node', 'Got a node in the suggestion.');
    $this->assertEqual($suggestion['job_item']->getItemId(), $node->field2[1]->target_id, 'Node id match between node and suggestion.');
    $this->assertEqual($suggestion['reason'], 'Field Field 2');
    $this->assertEqual($suggestion['from_item'], $item->id());

    // Add the suggestion to the job and re-get all suggestions.
    $job->addExistingItem($suggestion['job_item']);
    $suggestions = $job->getSuggestions();
    $job->cleanSuggestionsList($suggestions);

    // Check for no more suggestions.
    $this->assertEqual(count($suggestions), 0, 'Found no more suggestion.');
  }

}
