<?php
/**
 * @file
 * Contains \Drupal\tmgmt_demo\Tests\TMGMTDemoTest.
 */

namespace Drupal\tmgmt_demo\Tests;

use Drupal\tmgmt\Tests\TMGMTTestBase;

/**
 * Tests the demo module for TMGMT.
 *
 * @group TMGMT
 */
class TMGMTDemoTest extends TMGMTTestBase {

  /**
   * Modules to enable.
   *
   * @var string[]
   */
  public static $modules = array('tmgmt_demo');

  /**
   * {@inheritdoc}
   */
  function setUp() {
    parent::setUp();
    $this->loginAsAdmin([
      'access content overview',
      'administer tmgmt',
      'translate any entity'
    ]);
  }

  /**
   * Asserts translation jobs can be created.
   */
  protected function testInstalled() {
    // Try and translate node 1.
    $this->drupalGet('node');
    $this->assertText('First node');
    $this->assertText('Second node');
    $this->assertText('TMGMT Demo');
    $this->clickLink(t('First node'));
    $this->clickLink(t('Translate'));
    $edit = [
      'languages[de]' => TRUE,
      'languages[fr]' => TRUE,
    ];
    $this->drupalPostForm(NULL, $edit, t('Request translation'));
    $this->assertText(t('2 jobs need to be checked out.'));
    // Try and translate node 2.
    $this->drupalGet('admin/content');
    $this->clickLink(t('Second node'));
    $this->clickLink(t('Translate'));
    $this->drupalPostForm(NULL, $edit, t('Request translation'));
    $this->assertText(t('2 jobs need to be checked out.'));
    // Check to see if no items are selected and the error message pops up.
    $this->drupalPostForm('admin/tmgmt/sources', [], t('Request translation'));
    $this->assertUniqueText(t("You didn't select any source items."));
    $this->drupalPostForm(NULL, [], t('Search'));
    $this->assertNoText(t("You didn't select any source items."));
    $this->drupalPostForm(NULL, [], t('Cancel'));
    $this->assertNoText(t("You didn't select any source items."));
    $this->drupalPostForm(NULL, [], t('Add to cart'));
    $this->assertUniqueText(t("You didn't select any source items."));
  }

}
