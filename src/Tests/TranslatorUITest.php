<?php

/*
 * @file
 * Contains Drupal\tmgmt\Tests\TranslatorUITest.
 */

namespace Drupal\tmgmt\Tests;

/**
 * Tests the translator add, edit and overview user interfaces.
 *
 * @group tmgmt
 */
class TranslatorUITest extends TMGMTTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  static public $modules = array('tmgmt_file');

  /**
   * {@inheritdoc}
   */
  function setUp() {
    parent::setUp();

    // Login as administrator to add/edit and view translators.
    $this->loginAsAdmin();
  }

  /**
   * Tests UI for creating a translator.
   */
  public function testTranslatorUI() {

    // Test translator creation UI.
    $this->drupalGet('admin/config/regional/tmgmt_translator/add');
    $this->drupalPostForm('admin/config/regional/tmgmt_translator/add', array(
      'label' => 'Test translator',
      'description' => 'Test translator description',
      'name' => 'translator_test',
      'settings[scheme]' => 'private',
    ), t('Save'));
    $this->assertText('Test translator configuration has been created.');
    // Test translator edit page.
    $this->drupalGet('admin/config/regional/tmgmt_translator/manage/translator_test');
    $this->assertFieldByName('label', 'Test translator');
    $this->assertFieldByName('description', 'Test translator description');
    $this->assertFieldByName('name', 'translator_test');
    $this->assertFieldChecked('edit-settings-scheme-private');
    $this->drupalPostForm(NULL, array(
      'label' => 'Test translator changed',
      'description' => 'Test translator description changed',
    ), t('Save'));
    $this->assertText('Test translator changed configuration has been updated.');

    // Test translator overview page.
    $this->drupalGet('admin/config/regional/tmgmt_translator');
    $this->assertText('Test translator changed');
    $this->assertLink(t('Edit'));
    $this->assertLink(t('Delete'));
  }
}
