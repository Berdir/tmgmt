<?php

/*
 * @file
 * Contains Drupal\tmgmt\Tests\TMGMTUnitTestBase.
 */

namespace Drupal\tmgmt\Tests;

use Drupal\Core\Language\Language;
use Drupal\simpletest\DrupalUnitTestBase;
use Drupal\tmgmt\Entity\Job;

/**
 * Base class for tests.
 */
abstract class TMGMTUnitTestBase extends DrupalUnitTestBase {

  /**
   * A default translator using the test translator.
   *
   * @var \Drupal\tmgmt\Entity\Translator
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
    $this->installSchema('system', array('url_alias', 'router'));
    $this->installSchema('user', array('users'));
    $this->installSchema('tmgmt', array('tmgmt_job', 'tmgmt_job_item', 'tmgmt_message'));

    $this->default_translator = entity_create('tmgmt_translator', array('name' => 'test_translator', 'plugin' => 'test_translator'));
    $this->default_translator->save();

    $this->addLanguage('de');
  }

  /**
   * Creates, saves and returns a translator.
   *
   * @return \Drupal\tmgmt\Entity\Translator
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
   * @return \Drupal\tmgmt\Entity\Job
   *   A new job.
   */
  function createJob($source = 'en', $target = 'de', $uid = 0)  {
    $job = tmgmt_job_create($source, $target, $uid);
    $this->assertEqual(SAVED_NEW, $job->save());

    // Assert that the translator was assigned a tid.
    $this->assertTrue($job->id() > 0);
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
      'id' => $langcode,
    );
    $language = new Language($edit);
    language_save($language);
  }

}
