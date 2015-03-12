<?php

/**
 * @file
 * Contains \Drupal\tmgmt_config\Tests\ConfigtEntitySourceListTest.
 */

namespace Drupal\tmgmt_config\Tests;

use Drupal\tmgmt\Tests\EntityTestBase;
use Drupal\Core\Language\LanguageInterface;

/**
 * Tests the user interface for entity translation lists.
 *
 * @group tmgmt
 */
class ConfigEntitySourceListTest extends EntityTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('tmgmt_config', 'config_translation', 'views', 'views_ui');

  protected $nodes = array();

  function setUp() {
    parent::setUp();
    $this->loginAsAdmin();

    $this->loginAsTranslator(array('translate configuration'));

    $this->addLanguage('de');
    $this->addLanguage('it');

    $this->drupalCreateContentType(array(
      'type' => 'article',
      'name' => 'Article',
    ));

    $this->drupalCreateContentType(array(
      'type' => 'page',
      'name' => 'Page',
    ));
  }

  function testNodeTypeSubmissions() {

    // Simple submission.
    $edit = array(
      'items[article]' => TRUE,
    );
    $this->drupalPostForm('admin/tmgmt/sources/config/node_type', $edit, t('Request translation'));

    // Verify that we are on the translate tab.
    $this->assertText(t('One job needs to be checked out.'));
    $this->assertText(t('Article content type (English to ?, Unprocessed)'));

    // Submit.
    $this->drupalPostForm(NULL, array(), t('Submit to translator'));

    // Make sure that we're back on the originally defined destination URL.
    $this->assertUrl('admin/tmgmt/sources/config/node_type');

    $this->assertText(t('Test translation created.'));
    $this->assertText(t('The translation of Article content type to German is finished and can now be reviewed.'));

    // Submission of two different entity types.
    $edit = array(
      'items[article]' => TRUE,
      'items[page]' => TRUE,
    );
    $this->drupalPostForm('admin/tmgmt/sources/config/node_type', $edit, t('Request translation'));

    // Verify that we are on the translate tab.
    // This is still one job, unlike when selecting more languages.
    $this->assertText(t('One job needs to be checked out.'));
    $this->assertText(t('Article content type and 1 more (English to ?, Unprocessed)'));

    // Submit.
    $this->drupalPostForm(NULL, array(), t('Submit to translator'));

    // Make sure that we're back on the originally defined destination URL.
    $this->assertUrl('admin/tmgmt/sources/config/node_type');

    $this->assertText(t('Test translation created.'));
    $this->assertText(t('The translation of Article content type to German is finished and can now be reviewed.'));
    $this->assertText(t('The translation of Page content type to German is finished and can now be reviewed.'));
  }

  function testViewTranslation() {

    // Check if we have appropriate message in case there are no entity
    // translatable content types.
    $this->drupalGet('admin/tmgmt/sources/config/view');
    $this->assertText(t('View overview (Config Entity)'));

    // Request a translation for archive.
    $edit = array(
      'items[archive]' => TRUE,
    );
    $this->drupalPostForm(NULL, $edit, t('Request translation'));

    // Verify that we are on the translate tab.
    $this->assertText(t('One job needs to be checked out.'));
    $this->assertText(t('Archive view (English to ?, Unprocessed)'));

    // Submit.
    $this->drupalPostForm(NULL, array(), t('Submit to translator'));

    // Make sure that we're back on the originally defined destination URL.
    $this->assertUrl('admin/tmgmt/sources/config/view');

    $this->assertText(t('Test translation created.'));
    $this->assertText(t('The translation of Archive view to German is finished and can now be reviewed.'));

    // Request a translation for more archive, recent comments, content and job
    // overview.
    $edit = array(
      'items[archive]' => TRUE,
      'items[content_recent]' => TRUE,
      'items[content]' => TRUE,
      'items[tmgmt_job_overview]' => TRUE,
    );
    $this->drupalPostForm(NULL, $edit, t('Request translation'));

    // Verify that we are on the translate tab.
    $this->assertText(t('One job needs to be checked out.'));
    $this->assertText(t('Archive view and 3 more (English to ?, Unprocessed)'));

    // Submit.
    $this->drupalPostForm(NULL, array(), t('Submit to translator'));

    // Make sure that we're back on the originally defined destination URL.
    $this->assertUrl('admin/tmgmt/sources/config/view');

    $this->assertText(t('Test translation created.'));
    $this->assertText(t('The translation of Archive view to German is finished and can now be reviewed.'));
    $this->assertText(t('The translation of Recent content view to German is finished and can now be reviewed.'));
    $this->assertText(t('The translation of Content view to German is finished and can now be reviewed.'));
    $this->assertText(t('The translation of Job overview view to German is finished and can now be reviewed.'));

  }
}
