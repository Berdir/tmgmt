<?php

/**
 * @file Contains \Drupal\tmgmt_locale\Tests\.
 */

namespace Drupal\tmgmt_locale\Tests;

use Drupal\locale\Gettext;
use Drupal\tmgmt\Tests\TMGMTTestBase;

/**
 * Basic Locale Source tests.
 */
class LocaleSourceUiTest extends TMGMTTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('tmgmt_ui', 'tmgmt_locale');

  static function getInfo() {
    return array(
      'name' => 'Locale Source UI tests',
      'description' => 'Tests the locale source overview',
      'group' => 'Translation Management',
    );
  }

  function setUp() {
    parent::setUp();
    $this->langcode = 'de';
    $this->context = 'default';
    file_unmanaged_copy(drupal_get_path('module', 'tmgmt_locale') . '/tests/test.de.po', 'translations://', FILE_EXISTS_REPLACE);
    $file = new \stdClass();
    $file->uri = drupal_realpath(drupal_get_path('module', 'tmgmt_locale') . '/tests/test.xx.po');
    $file->langcode = $this->langcode;
    Gettext::fileToDatabase($file, array());
    $this->addLanguage($this->langcode);
    $this->addLanguage('es');
  }

  public function testOverview() {
    $this->loginAsTranslator();
    $this->drupalGet('admin/tmgmt/sources/locale/default');

    $this->assertText('Hello World');
    $this->assertText('Example');
    $rows = $this->xpath('//tbody/tr');
    foreach ($rows as $row) {
      if ($row->td[1] == 'Hello World') {
        $this->assertEqual((string) $row->td[3]->div['title'], t('Translation up to date'));
        $this->assertEqual((string) $row->td[4]->div['title'], t('Not translated'));
      }
    }

    // Filter on the label.
    $edit = array('search[label]' => 'Hello');
    $this->drupalPostForm(NULL, $edit, t('Search'));

    $this->assertText('Hello World');
    $this->assertNoText('Example');

    $locale_object = db_query('SELECT * FROM {locales_source} WHERE source = :source LIMIT 1', array(':source' => 'Hello World'))->fetchObject();

    // First add source to the cart to test its functionality.
    $edit = array(
      'items[' . $locale_object->lid . ']' => TRUE,
    );
    $this->drupalPostForm(NULL, $edit, t('Add to cart'));
    $this->assertRaw(t('@count content source was added into the <a href="@url">cart</a>.', array('@count' => 1, '@url' => url('admin/tmgmt/cart'))));
    $edit['target_language[]'] = array('es');
    $this->drupalPostForm('admin/tmgmt/cart', $edit, t('Request translation'));

    // Assert that the job item is displayed.
    $this->assertText('Hello World');
    $this->assertText(t('Locale'));
    $this->assertText('2');
    $this->drupalPostForm(NULL, array('target_language' => 'es'), t('Submit to translator'));

    // Test for the translation flag title.
    $this->drupalGet('admin/tmgmt/sources/locale/default');
    $this->assertRaw(t('Active job item: Needs review'));

    // Review and accept the job item.
    $job_items = tmgmt_job_item_load_latest('locale', 'default', $locale_object->lid, 'en');
    $this->drupalGet('admin/tmgmt/items/' . $job_items['es']->id());
    $this->assertRaw('es_Hello World');
    $this->drupalPostForm(NULL, array(), t('Save as completed'));
    $this->drupalGet('admin/tmgmt/sources/locale/default');

    $this->assertNoRaw(t('Active job item: Needs review'));
    $rows = $this->xpath('//tbody/tr');
    foreach ($rows as $row) {
      if ($row->td[1] == 'Hello World') {
        $this->assertEqual((string) $row->td[3]->div['title'], t('Translation up to date'));
        $this->assertEqual((string) $row->td[4]->div['title'], t('Translation up to date'));
      }
    }

    // Test the missing translation filter.
    $this->drupalGet('admin/tmgmt/sources/locale/default');
    // Check that the source language (en) has been removed from the target language
    // select box.
    $elements = $this->xpath('//select[@name=:name]//option[@value=:option]', array(':name' => 'search[target_language]', ':option' => 'en'));
    $this->assertTrue(empty($elements));

    // Filter on the "Not translated to".
    $edit = array('search[missing_target_language]' => 'es');
    $this->drupalPostForm(NULL, $edit, t('Search'));
    // Hello World is translated to "es" therefore it must not show up in the
    // list.
    $this->assertNoText('Hello World');
  }
}
