<?php

/*
 * @file
 * Contains Drupal\tmgmt\Tests\TMGMTUnitTestBase.
 */

namespace Drupal\tmgmt\Tests;

use Drupal\Core\Language\Language;
use Drupal\simpletest\DrupalUnitTestBase;
use Drupal\tmgmt\Plugin\Core\Entity\Job;
use Drupal\tmgmt\Plugin\Core\Entity\Translator;

/**
 * Base class for tests.
 */
class TMGMTUnitTestBase extends DrupalUnitTestBase {

  /**
   * A default translator using the test translator.
   *
   * @var Translator
   */
  protected $default_translator;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('user', 'system', 'field', 'text', 'field_sql_storage', 'entity_test', 'language', 'locale', 'tmgmt', 'tmgmt_test');

  /**
   * Overrides DrupalUnitTestBase::setUp().
   */
  function setUp() {
    parent::setUp();

    // @todo: Try to get rid of these.
    $this->installSchema('system', 'url_alias');
    $this->installSchema('system', 'variable');
    $this->installSchema('tmgmt', array('tmgmt_job', 'tmgmt_job_item', 'tmgmt_message'));

    $this->default_translator = entity_create('tmgmt_translator', array('name' => 'test_translator', 'plugin' => 'test_translator'));
    $this->default_translator->save();
  }

  /**
   * Creates, saves and returns a translator.
   *
   * @return Translator
   */
  function createTranslator() {
    $translator = entity_create('tmgmt_translator', array(
      'name' => strtolower($this->randomName()),
      'label' => $this->randomName(),
      'plugin' => 'test_translator',
      'settings' => array(
        'key' => $this->randomName(),
        'another_key' => $this->randomName(),
      )
    ));
    $this->assertEqual(SAVED_NEW, $translator->save());
    return $translator;
  }

  /**
   * Creates, saves and returns a translation job.
   *
   * @return Job
   */
  function createJob($source = 'en', $target = 'de', $uid = 0)  {
    $job = tmgmt_job_create($source, $target, $uid);
    $this->assertEqual(SAVED_NEW, $job->save());

    // Assert that the translator was assigned a tid.
    $this->assertTrue($job->tjid > 0);
    return $job;
  }


  /**
   * Sets the proper environment.
   *
   * Currently just adds a new language.
   *
   * @param string $langcode
   *   The language code.
   */
  function addLanguage($langcode) {
    // Add the language.
    $edit = array(
      'langcode' => $langcode,
    );
    $language = new Language($edit);
    language_save($language);
  }

}
