<?php

/*
 * @file
 * Contains Drupal\tmgmt\Tests\LocalTasksTest.
 */

namespace Drupal\tmgmt\Tests;

/**
 * Verifies basic functionality of the local tasks.
 *
 * @group tmgmt
 */
class LocalTasksTests extends TMGMTTestBase {

  public static $modules = array(
    'dblog',
    'node',
    'views',
    'tmgmt_content',
    'tmgmt_file'
  );

  /**
   * {@inheritdoc}
   */
  function setUp() {
    parent::setUp();
    // Login as administrator to view Cart,Jobs and Sources.
    $this->loginAsAdmin(array('access administration pages'));

  }

  /**
   * Tests UI for translator local tasks.
   */
  public function testTranslatorLocalTaks() {

    $this->drupalCreateContentType(array(
      'type' => 'article',
      'name' => 'Article'
    ));
    $content_translation_manager = \Drupal::service('content_translation.manager');
    // Add a node type and enable translation for nodes and users.
    $content_translation_manager->setEnabled('node', 'article', TRUE);
    $content_translation_manager->setEnabled('user', 'user', TRUE);


    // Check the translator menu link.
    $this->drupalGet('admin');
    $this->clickLink(t('Translation'));

    // Make sure the Cart,Jobs and Sources pages are available.
    $this->clickLink(t('Cart'));
    $this->clickLink(t('Jobs'));
    $this->clickLink(t('Sources'));

    // Assert the availability of the enabled content.
    $this->assertLink(t('Content'));
    $this->assertLink(t('User'));
  }

  /**
   * Tests UI for translator local tasks without sources.
   */
  public function testTranslatorLocalTasksNoSource() {
    // Login as administrator to view Cart,Jobs and Sources.
    $this->loginAsAdmin(array('access administration pages'));
    $this->drupalGet('admin');
    $this->clickLink(t('Translation'));
    $this->assertNoLink(t('Sources'));
  }
}
