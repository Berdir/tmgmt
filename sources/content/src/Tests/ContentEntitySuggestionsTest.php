<?php

/**
 * @file
 * Contains \Drupal\tmgmt_content\Tests\ContentEntitySuggestionsTest.
 */

namespace Drupal\tmgmt_content\Tests;

use Drupal\Core\Language\Language;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldInstanceConfig;
use Drupal\node\Entity\Node;
use Drupal\tmgmt\Tests\TMGMTTestBase;

/**
 * Basic Source-Suggestions tests.
 */
class ContentEntitySuggestionsTest extends TMGMTTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('tmgmt_content', 'tmgmt_test', 'node');

  public static function getInfo() {
    return array(
      'name' => 'Entity Suggestions tests',
      'description' => 'Tests suggestion implementation for the entity source plugin',
      'group' => 'Translation Management',
    );
  }

  public function setUp() {
    parent::setUp();

    $edit = array(
      'id' => 'de',
    );
    $language = new Language($edit);
    language_save($language);
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
    $type = $this->drupalCreateContentType();

    $field1 = FieldConfig::create(array(
      'name' => 'field1',
      'entity_type' => 'node',
      'type' => 'entity_reference',
      'cardinality' => -1,
      'settings' => array('target_type' => 'node'),
    ));
    $field1->save();
    $field2 = FieldConfig::create(array(
      'name' => 'field2',
      'entity_type' => 'node',
      'type' => 'entity_reference',
      'cardinality' => -1,
      'translatable' => TRUE,
      'settings' => array('target_type' => 'node'),
    ));
    $field2->save();

    // Create field instances on the content type.
    FieldInstanceConfig::create(array(
      'field' => $field1,
      'bundle' => $type->type,
      'label' => 'Field 1',
      'settings' => array(),
    ))->save();
    FieldInstanceConfig::create(array(
      'field' => $field2,
      'bundle' => $type->type,
      'label' => 'Field 2',
      'settings' => array(),
    ))->save();

    // Make the body field translatable from node.
    $field = FieldConfig::loadByName('node', 'body');
    $field->translatable = TRUE;
    $field->save();

    // Create 4 nodes to be referenced.
    $references = array();
    for ($i = 0; $i < 4; $i++) {
      $references[$i] = Node::create(array(
        'title' => $this->randomName(),
        'body' => $this->randomName(),
        'type' => $type->type,
      ));
      $references[$i]->save();
    }

    // Create a node with two translatable and two non-translatable references.
    $node = Node::create(array(
      'type' => $type->type,
      'language' => 'en',
      'body' => $this->randomName(),
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
    $this->assertEqual($suggestion['job_item']->getWordCount(), 3, 'Three translatable words in the suggestion.');
    $this->assertEqual($suggestion['job_item']->getPlugin(), 'content', 'Got an entity as plugin in the suggestion.');
    $this->assertEqual($suggestion['job_item']->getItemType(), 'file', 'Got a file in the suggestion.');
    $this->assertEqual($suggestion['job_item']->getItemId(), $node->field1[LANGUAGE_NONE][1]['fid'], 'File id match between node and suggestion.');
    $this->assertEqual($suggestion['reason'], 'Field Field 1');
    $this->assertEqual($suggestion['from_item'], $item->id());
    $job->addExistingItem($suggestion['job_item']);

    $suggestion = array_shift($suggestions);
    $this->assertEqual($suggestion['job_item']->getWordCount(), 3, 'Three translatable words in the suggestion.');
    $this->assertEqual($suggestion['job_item']->getPlugin(), 'content', 'Got an entity as plugin in the suggestion.');
    $this->assertEqual($suggestion['job_item']->getItemType(), 'file', 'Got a file in the suggestion.');
    $this->assertEqual($suggestion['job_item']->getItemId(), $node->field2[LANGUAGE_NONE][1]['fid'], 'File id match between node and suggestion.');
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
