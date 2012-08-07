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

  public $vocabulary;

  /**
   * Modules to enable.
   * @var array
   */
  public static $modules = array('tmgmt_entity', 'taxonomy', 'translation_entity');

  /**
   * Implements getInfo().
   */
  static function getInfo() {
    return array(
      'name' => 'Entity Source tests',
      'description' => 'Exporting source data from entities and saving translations back to entities.',
      'group' => 'Translation Management',
    );
  }

  /**
   * Overrides SimplenewsTestCase::setUp()
   */
  function setUp() {
    // Admin user to perform settings on setup.
    $this->loginAsAdmin(array('translate any entity'));

    $this->vocabulary = $this->createTaxonomyVocab(strtolower($this->randomName()), $this->randomName(), array(FALSE, TRUE, TRUE, TRUE));
    translation_entity_set_config('taxonomy_term', $this->vocabulary->machine_name, 'enabled', TRUE);
  }

  /**
   * Tests nodes field translation.
   */
  function dtestEntitySourceNode() {
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
      $job->addItem('entity', 'node', $node->nid);
    }

    // Translate the job.
    $job->requestTranslation();

    // Check the translated job items.
    foreach ($job->getItems() as $item) {
      $item->acceptTranslation();
      $this->assertTrue($item->isAccepted());
      $entity = entity_load($item->item_type, $item->item_id);
      $data = $item->getData();
      $this->checkTranslatedData($entity, $data, 'de');
      $this->checkUntranslatedData($entity, $this->field_names['node']['article'], $data, 'de');
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
      $job->addItem('entity', 'taxonomy_term', $term->tid);
    }
    // Request the translation and accept it.
    $job->requestTranslation();

    // Check if the fields were translated.
    foreach ($job->getItems() as $item) {
      $item->acceptTranslation();
      $entity = entity_load($item->item_type, $item->item_id);
      $data = $item->getData();
      $this->checkTranslatedData($entity, $data, 'de');
      $this->checkUntranslatedData($entity, $this->field_names['taxonomy_term'][$this->vocabulary->machine_name], $data, 'de');
    }
  }

  function testAddingJobItemsWithEmptySourceText() {
    $this->addLanguage('de');


    // Create term with empty texts.
    $empty_term = new stdClass();
    $empty_term->name = $this->randomName();
    $empty_term->description = $this->randomName();
    $empty_term->vid = $this->vocabulary->vid;
    taxonomy_term_save($empty_term);

    // Create the job.
    $job = tmgmt_job_create('en', NULL);
    try {
      $job->addItem('entity', 'taxonomy_term', $empty_term->tid);
      $this->fail(t('Job item added with empty source text.'));
    }
    catch (TMGMTException $e) {
      $this->assert(empty($job->tjid), t('After adding a job item with empty source text its tjid has to be unset.'));
    }

    // Create term with populated source content.
    $populated_content_term = $this->createTaxonomyTerm($this->vocabulary);

    // Lets reuse the last created term with populated source content.
    $job->addItem('entity', 'taxonomy_term', $populated_content_term->tid);
    $this->assert(!empty($job->tjid), t('After adding another job item with populated source text its tjid must be set.'));
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
    foreach (element_children($data) as $field_name) {
      foreach (element_children($data[$field_name]) as $delta) {
        foreach (element_children($data[$field_name][$delta]) as $column) {
          $column_value = $data[$field_name][$delta][$column];
          if (!empty($column_value['#translate'])) {
            $this->assertEqual($tentity->{$field_name}[$langcode][$delta][$column], $column_value['#translation']['#text'], t('The field %field:%delta has been populated with the proper translated data.', array('%field' => $field_name, 'delta' => $delta)));
          }
          else {
            $this->assertEqual($tentity->{$field_name}[$langcode][$delta][$column], $column_value['#text'], t('The field %field:%delta has been populated with the proper untranslated data.', array('%field' => $field_name, 'delta' => $delta)));
          }
        }
      }
    }
  }

  /**
   * Checks the fields that should not be translated.
   *
   * @param $tentity
   *  The translated entity object.
   * @param $fields
   *  An array with the field names to check.
   * @param $translation
   *  An array with the translated data.
   * @param $langcode
   *  The code of the target language.
   */
  function checkUntranslatedData($tentity, $fields, $data, $langcode) {
    foreach ($fields as $field_name) {
      $field_info = field_info_field($field_name);
      if (!$field_info['translatable']) {
        // Avoid some PHP warnings.
        if (isset($data[$field_name])) {
          $this->assertNull($data[$field_name]['#translation']['#text'], t('The not translatable field was not translated.'));
        }
        if (isset($tentity->{$field_name}[$langcode])) {
          $this->assertNull($tentity->{$field_name}[$langcode], t('The entity has translated data in a field that is translatable.'));
        }
      }
    }
  }
}
