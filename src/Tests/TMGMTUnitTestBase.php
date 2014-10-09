<?php

/*
 * @file
 * Contains Drupal\tmgmt\Tests\TMGMTUnitTestBase.
 */

namespace Drupal\tmgmt\Tests;

use Drupal\Core\Language\Language;
use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\simpletest\KernelTestBase;

/**
 * Base class for tests.
 */
abstract class TMGMTUnitTestBase extends KernelTestBase {

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
  public static $modules = array('user', 'system', 'field', 'text', 'entity_test', 'language', 'locale', 'tmgmt', 'tmgmt_test', 'menu_link');

  /**
   * Overrides DrupalUnitTestBase::setUp().
   */
  function setUp() {
    parent::setUp();

    // @todo: Try to get rid of these.
    $this->installSchema('system', array('url_alias', 'router'));
    $this->installEntitySchema('user');
    $this->installEntitySchema('tmgmt_job');
    $this->installEntitySchema('tmgmt_job_item');
    $this->installEntitySchema('tmgmt_message');

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
      'name' => strtolower($this->randomMachineName()),
      'label' => $this->randomMachineName(),
      'plugin' => 'test_translator',
      'settings' => array(
        'key' => $this->randomMachineName(),
        'another_key' => $this->randomMachineName(),
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
    $language = ConfigurableLanguage::createFromLangcode($langcode);
    $language->save();
  }

}
