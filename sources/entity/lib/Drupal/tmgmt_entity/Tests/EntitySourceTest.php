<?php

/**
 * @file
 * Contains Drupal\tmgmt_entity\Tests\EntitySourceTest.php
 */

namespace Drupal\tmgmt_entity\Tests;

use Drupal\tmgmt\Tests\EntityTestBase;
use Drupal\tmgmt\TMGMTException;

/**
 * Basic Entity Source tests.
 */
class EntitySourceTest extends EntityTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('tmgmt_entity', 'taxonomy', 'content_translation');

  public $vocabulary;

  public static function getInfo() {
    return array(
      'name' => 'Entity Source tests',
      'description' => 'Exporting source data from entities and saving translations back to entities.',
      'group' => 'Translation Management',
    );
  }

  function setUp() {
    parent::setUp();
    $this->vocabulary = $this->createTaxonomyVocab(strtolower($this->randomName()), $this->randomName(), array(FALSE, TRUE, TRUE, TRUE));
    content_translation_set_config('taxonomy_term', $this->vocabulary->id(), 'enabled', TRUE);
  }

  /**
   * Tests nodes field translation.
   */
  function testEntitySourceNode() {
    $this->addLanguage('de');

    $this->createNodeType('article', 'Article', TRUE);

    // Create a translation job.
    $job = $this->createJob();
    $job->translator = $this->default_translator->name;
    $job->settings = array();
    $job->save();

    // Create some nodes.
    for ($i = 1; $i <= 5; $i++) {
      $node = $this->createNode('article');
      // Create a job item for this node and add it to the job.
      $item = $job->addItem('entity', 'node', $node->id());
      $this->assertEqual(t('@type (@bundle)', array('@type' => t('Content'), '@bundle' => 'Article')), $item->getSourceType());
    }

    // Translate the job.
    $job->requestTranslation();

    // Check the translated job items.
    foreach ($job->getItems() as $item) {
      // The source is available only for en.
      $this->assertJobItemLangCodes($item, 'en', array('en'));
      $item->acceptTranslation();
      $this->assertTrue($item->isAccepted());
      $entity = entity_load($item->item_type, $item->item_id);
      $data = $item->getData();
      $this->checkTranslatedData($entity, $data, 'de');
      $this->checkUntranslatedData($entity, $this->field_names['node']['article'], $data, 'de');
      // The source is now available for both en and de.
      $this->assertJobItemLangCodes($item, 'en', array('de', 'en'));
    }
  }

  /**
   * Tests taxonomy terms field translation.
   */
  function testEntitySourceTerm() {
    $this->addLanguage('de');

    // Create the job.
    $job = $this->createJob();
    $job->translator = $this->default_translator->name;
    $job->settings = array();
    $job->save();

    $term = NULL;

    //Create some terms.
    for ($i = 1; $i <= 5; $i++) {
      $term = $this->createTaxonomyTerm($this->vocabulary);
      // Create the item and assign it to the job.
      $item = $job->addItem('entity', 'taxonomy_term', $term->id());
      $this->assertEqual(t('@type (@bundle)', array('@type' => t('Taxonomy term'), '@bundle' => $this->vocabulary->name)), $item->getSourceType());
    }
    // Request the translation and accept it.
    $job->requestTranslation();

    // Check if the fields were translated.
    foreach ($job->getItems() as $item) {
      $this->assertJobItemLangCodes($item, 'en', array('en'));
      $item->acceptTranslation();
      $entity = entity_load($item->item_type, $item->item_id);
      $data = $item->getData();
      $this->checkTranslatedData($entity, $data, 'de');
      $this->checkUntranslatedData($entity, $this->field_names['taxonomy_term'][$this->vocabulary->id()], $data, 'de');
      $this->assertJobItemLangCodes($item, 'en', array('de', 'en'));
    }
  }

  function testAddingJobItemsWithEmptySourceText() {
    $this->addLanguage('de');

    // Create term with empty texts.
    $empty_term = entity_create('taxonomy_term', array(
      'name' => $this->randomName(),
      'description' => $this->randomName(),
      'vid' => $this->vocabulary->vid,
    ));
    $empty_term->save();

    // Create the job.
    $job = tmgmt_job_create('en', NULL);
    try {
      $job->addItem('entity', 'taxonomy_term', $empty_term->id());
      $this->fail('Job item added with empty source text.');
    }
    catch (\Exception $e) {
      $this->assert(empty($job->tjid), 'After adding a job item with empty source text its tjid has to be unset.');
    }

    // Create term with populated source content.
    $populated_content_term = $this->createTaxonomyTerm($this->vocabulary);

    // Lets reuse the last created term with populated source content.
    $job->addItem('entity', 'taxonomy_term', $populated_content_term->id());
    $this->assert(!empty($job->tjid), 'After adding another job item with populated source text its tjid must be set.');
  }

  /**
   * Compares the data from an entity with the translated data.
   *
   * @param $tentity
   *  The translated entity object.
   * @param $data
   *  An array with the translated data.
   * @param $langcode
   *  The code of the target language.
   */
  function checkTranslatedData($tentity, $data, $langcode) {
    $tentity = $tentity->getTranslation($langcode);
    foreach (element_children($data) as $field_name) {
      foreach (element_children($data[$field_name]) as $delta) {
        foreach (element_children($data[$field_name][$delta]) as $column) {
          $column_value = $data[$field_name][$delta][$column];
          if (!empty($column_value['#translate'])) {
            $this->assertEqual($tentity->get($field_name)->get($delta)->$column, $column_value['#translation']['#text'], format_string('The field %field:%delta has been populated with the proper translated data.', array('%field' => $field_name, 'delta' => $delta)));
          }
          else {
            $this->assertEqual($tentity->get($field_name)->get($delta)->$column, $column_value['#text'], format_string('The field %field:%delta has been populated with the proper untranslated data.', array('%field' => $field_name, 'delta' => $delta)));
          }
        }
      }
    }
  }

  /**
   * Checks the fields that should not be translated.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *  The translated entity object.
   * @param $fields
   *  An array with the field names to check.
   * @param $translation
   *  An array with the translated data.
   * @param $langcode
   *  The code of the target language.
   */
  function checkUntranslatedData($entity, $fields, $data, $langcode) {
    foreach ($fields as $field_name) {
      if (!$entity->getFieldDefinition($field_name)->isTranslatable()) {
        // Avoid some PHP warnings.
        if (isset($data[$field_name])) {
          $this->assertNull($data[$field_name]['#translation']['#text'], 'The not translatable field was not translated.');
        }
      }
    }
  }
}
