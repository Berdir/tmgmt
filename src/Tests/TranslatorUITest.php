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
    $this->drupalPostForm('admin/config/regional/tmgmt_translator/add', array(
      'label' => 'Test translator',
      'description' => 'Test translator description',
      'name' => 'translator_test',
    ), t('Save'));
    $this->assertText('testTranslator configuration has been created.');

    // Test translator edit page.
    $this->drupalGet('admin/config/regional/tmgmt_translator/manage/translator_test');
    $this->assertFieldByName('label', 'Test translator');
    $this->assertFieldByName('description', 'Test translator description');
    $this->assertFieldByName('name', 'translator_test');
    $this->drupalPostForm(NULL, array(
      'label' => 'testTranslatorChanged',
      'description' => 'testTranslatorDescriptionChanged',
    ), t('Save'));
    $this->assertText('testTranslatorChanged configuration has been updated.');

    // Test translator overview page.
    $this->drupalGet('admin/config/regional/tmgmt_translator');
    $this->assertText('testTranslatorChanged');
    $this->assertLink(t('Edit'));
    $this->assertLink(t('Delete'));
  }
}
