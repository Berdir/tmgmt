<?php

/*
 * @file
 * Contains Drupal\tmgmt\Tests\TMGMTKernelTestBase.
 */

namespace Drupal\tmgmt\Tests;

use Drupal\Core\Language\Language;
use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\simpletest\KernelTestBase;
use Drupal\tmgmt\JobItemInterface;

/**
 * Base class for tests.
 */
abstract class TMGMTKernelTestBase extends KernelTestBase {

  /**
   * A default translator using the test translator.
   *
   * @var \Drupal\tmgmt\TranslatorInterface
   */
  protected $default_translator;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('user', 'system', 'field', 'text', 'entity_test', 'language', 'locale', 'tmgmt', 'tmgmt_test', 'options');

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
   * @return \Drupal\tmgmt\TranslatorInterface
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
   * @return \Drupal\tmgmt\JobInterface
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

  /**
   * Asserts job item language codes.
   *
   * @param \Drupal\tmgmt\JobItemInterface $job_item
   *   Job item to check.
   * @param string $expected_source_lang
   *   Expected source language.
   * @param array $actual_lang_codes
   *   Expected existing language codes (translations).
   */
  function assertJobItemLangCodes(JobItemInterface $job_item, $expected_source_lang, array $actual_lang_codes) {
    $this->assertEqual($job_item->getSourceLangCode(), $expected_source_lang);
    $existing = $job_item->getExistingLangCodes();
    sort($existing);
    sort($actual_lang_codes);
    $this->assertEqual($existing, $actual_lang_codes);
  }

}
