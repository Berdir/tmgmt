<?php

/**
 * @file
 * Contains \Drupal\tmgmt_config\Tests\ConfigSourceUiTest.
 */

namespace Drupal\tmgmt_config\Tests;

use Drupal\Core\Url;
use Drupal\tmgmt\Entity\Translator;
use Drupal\tmgmt\Tests\EntityTestBase;
use Drupal\views\Entity\View;

/**
 * Content entity source UI tests.
 *
 * @group tmgmt
 */
class ConfigSourceUiTest extends EntityTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('tmgmt_config', 'views', 'views_ui', 'config_translation');

  /**
   * {@inheritdoc}
   */
  function setUp() {
    parent::setUp();

    $this->loginAsAdmin(array(
      'create translation jobs',
      'submit translation jobs',
      'accept translation jobs',
    ));

    $this->addLanguage('de');
    $this->addLanguage('it');
    $this->addLanguage('es');
    $this->addLanguage('el');

    $this->createNodeType('article', 'Article', TRUE);
  }

  /**
   * Test the node type for a single checkout.
   */
  function testNodeTypeTranslateTabSingleCheckout() {
    $this->loginAsTranslator(array('translate configuration'));

    // Go to the translate tab.
    $this->drupalGet('admin/structure/types/manage/article/translate');

    // Assert some basic strings on that page.
    $this->assertText(t('Translations of Article content type'));
    $this->assertText(t('There are 0 items in the translation cart.'));

    // Request a translation for german.
    $edit = array(
      'languages[de]' => TRUE,
    );
    $this->drupalPostForm(NULL, $edit, t('Request translation'));

    // Verify that we are on the translate tab.
    $this->assertText(t('One job needs to be checked out.'));
    $this->assertText('Article content type (English to German, Unprocessed)');

    // Submit.
    $this->drupalPostForm(NULL, array(), t('Submit to translator'));

    // Make sure that we're back on the originally defined destination URL.
    $this->assertUrl('admin/structure/types/manage/article/translate');

    // We are redirected back to the correct page.
    $this->drupalGet('admin/structure/types/manage/article/translate');

    // Translated languages - german should now be listed as Needs review.
    $rows = $this->xpath('//tbody/tr');
    foreach ($rows as $value) {
      if ($value->td[2]->a == 'Needs review') {
        $this->assertEqual('German', (string) $value->td[1]);
      }
    }

    // Verify that the pending translation is shown.
    $this->clickLink(t('Needs review'));
    $this->drupalPostForm(NULL, array(), t('Save as completed'));

    // Request a spanish translation.
    $edit = array(
      'languages[es]' => TRUE,
    );
    $this->drupalPostForm(NULL, $edit, t('Request translation'));

    // Verify that we are on the checkout page.
    $this->assertText(t('One job needs to be checked out.'));
    $this->assertText('Article content type (English to Spanish, Unprocessed)');
    $this->drupalPostForm(NULL, array(), t('Submit to translator'));

    // Make sure that we're back on the originally defined destination URL.
    $this->assertUrl('admin/structure/types/manage/article/translate');

    // Translated languages should now be listed as Needs review.
    $rows = $this->xpath('//tbody/tr');
    $counter = 0;
    foreach ($rows as $value) {
      if ($value->td[2]->a == 'Needs review') {
        $this->assertEqual('Spanish' || 'German', (string) $value->td[1]);
        $counter++;
      }
    }
    $this->assertEqual($counter, 2);
  }

  /**
   * Test the node type for a single checkout.
   */
  function testNodeTypeTranslateTabMultipeCheckout() {
    // Allow auto-accept.
    $default_translator = Translator::load('test_translator');
    $default_translator
      ->setSetting('auto_accept', TRUE)
      ->save();

    $this->loginAsTranslator(array('translate configuration'));

    // Go to the translate tab.
    $this->drupalGet('admin/structure/types/manage/article/translate');

    // Assert some basic strings on that page.
    $this->assertText(t('Translations of Article content type'));
    $this->assertText(t('There are 0 items in the translation cart.'));

    // Request a translation for german and spanish.
    $edit = array(
      'languages[de]' => TRUE,
      'languages[es]' => TRUE,
    );
    $this->drupalPostForm(NULL, $edit, t('Request translation'));

    // Verify that we are on the translate tab.
    $this->assertText(t('2 jobs need to be checked out.'));

    // Submit all jobs.
    $this->assertText('Article content type (English to German, Unprocessed)');
    $this->drupalPostForm(NULL, array(), t('Submit to translator and continue'));
    $this->assertText('Article content type (English to Spanish, Unprocessed)');
    $this->drupalPostForm(NULL, array(), t('Submit to translator'));

    // Make sure that we're back on the translate tab.
    $this->assertUrl('admin/structure/types/manage/article/translate');
    $this->assertText(t('Test translation created.'));
    $this->assertNoText(t('The translation of @title to @language is finished and can now be reviewed.', array(
      '@title' => 'Article',
      '@language' => t('Spanish')
    )));

    // Translated languages should now be listed as Needs review.
    $rows = $this->xpath('//tbody/tr');
    $counter = 0;
    foreach ($rows as $value) {
      if ($value->td[2]->a == 'Needs review') {
        $this->assertEqual('German' || 'Spanish', (string) $value->td[1]);
        $counter++;
      }
    }
    $this->assertEqual($counter, 2);
  }

  /**
   * Test the node type for a single checkout.
   */
  function testViewTranslateTabSingleCheckout() {
    $this->loginAsTranslator(array('translate configuration'));

    // Go to the translate tab.
    $this->drupalGet('admin/structure/views/view/content/translate');

    // Assert some basic strings on that page.
    $this->assertText(t('Translations of Content view'));
    $this->assertText(t('There are 0 items in the translation cart.'));

    // Request a translation for german.
    $edit = array(
      'languages[de]' => TRUE,
    );
    $this->drupalPostForm(NULL, $edit, t('Request translation'));

    // Verify that we are on the translate tab.
    $this->assertText(t('One job needs to be checked out.'));
    $this->assertText('Content view (English to German, Unprocessed)');

    // Submit.
    $this->drupalPostForm(NULL, array(), t('Submit to translator'));

    // Make sure that we're back on the originally defined destination URL.
    $this->assertUrl('admin/structure/views/view/content/translate');

    // We are redirected back to the correct page.
    $this->drupalGet('admin/structure/views/view/content/translate');

    // Translated languages should now be listed as Needs review.
    $rows = $this->xpath('//tbody/tr');
    foreach ($rows as $value) {
      if ($value->td[2]->a == 'Needs review') {
        $this->assertEqual('German', (string) $value->td[1]);
      }
    }

    // Verify that the pending translation is shown.
    $this->clickLink(t('Needs review'));
    $this->drupalPostForm(NULL, array(), t('Save as completed'));

    // Request a spanish translation.
    $edit = array(
      'languages[es]' => TRUE,
    );
    $this->drupalPostForm(NULL, $edit, t('Request translation'));

    // Verify that we are on the checkout page.
    $this->assertText(t('One job needs to be checked out.'));
    $this->assertText('Content view (English to Spanish, Unprocessed)');
    $this->drupalPostForm(NULL, array(), t('Submit to translator'));

    // Make sure that we're back on the originally defined destination URL.
    $this->assertUrl('admin/structure/views/view/content/translate');

    // Translated languages should now be listed as Needs review.
    $rows = $this->xpath('//tbody/tr');
    $counter = 0;
    foreach ($rows as $value) {
      if ($value->td[2]->a == 'Needs review') {
        $this->assertEqual('Spanish' || 'German', (string) $value->td[1]);
        $counter++;
      }
    }
    $this->assertEqual($counter, 2);

    // Test that a job can not be accepted if the entity does not exist.
    $this->clickLink(t('Needs review'));

    // Delete the view  and assert that the job can not be accepted.
    $view_content = View::load('content');
    $view_content->delete();

    $this->drupalPostForm(NULL, array(), t('Save as completed'));
    $this->assertText(t('@id of type @type does not exist, the job can not be completed.', array('@id' => $view_content->id(), '@type' => $view_content->getEntityTypeId())));
  }

  /**
   * Test the entity source specific cart functionality.
   */
  function testCart() {
    $this->loginAsTranslator(array('translate configuration'));

    // Test the source overview.
    $this->drupalPostForm('admin/structure/views/view/content/translate', array(), t('Add to cart'));
    $this->drupalPostForm('admin/structure/types/manage/article/translate', array(), t('Add to cart'));

    // Test if the content and article are in the cart.
    $this->drupalGet('admin/tmgmt/cart');
    $this->assertLink('Content view');
    $this->assertLink('Article content type');

    // Test the label on the source overivew.
    $this->drupalGet('admin/structure/views/view/content/translate');
    $this->assertRaw(t('There are @count items in the <a href="@url">translation cart</a> including the current item.',
        array('@count' => 2, '@url' => Url::fromRoute('tmgmt.cart')->toString())));
  }

  /**
   * Test the node type for a single checkout.
   */
  function testSimpleConfiguration() {
    $this->loginAsTranslator(array('translate configuration'));

    // Go to the translate tab.
    $this->drupalGet('admin/config/system/site-information/translate');

    // Assert some basic strings on that page.
    $this->assertText(t('Translations of System information'));

    // Request a translation for german.
    $edit = array(
      'languages[de]' => TRUE,
    );
    $this->drupalPostForm(NULL, $edit, t('Request translation'));

    // Verify that we are on the translate tab.
    $this->assertText(t('One job needs to be checked out.'));
    $this->assertText('System information (English to German, Unprocessed)');

    // Submit.
    $this->drupalPostForm(NULL, array(), t('Submit to translator'));

    // Make sure that we're back on the originally defined destination URL.
    $this->assertUrl('admin/config/system/site-information/translate');

    // We are redirected back to the correct page.
    $this->drupalGet('admin/config/system/site-information/translate');

    // Translated languages should now be listed as Needs review.
    $rows = $this->xpath('//tbody/tr');
    foreach ($rows as $value) {
      if ($value->td[2]->a == 'Needs review') {
        $this->assertEqual('German', (string) $value->td[1]);
      }
    }

    // Verify that the pending translation is shown.
    $this->clickLink(t('Needs review'));
    $this->drupalPostForm(NULL, array(), t('Save'));

    // Request a spanish translation.
    $edit = array(
      'languages[es]' => TRUE,
    );
    $this->drupalPostForm(NULL, $edit, t('Request translation'));

    // Verify that we are on the checkout page.
    $this->assertText(t('One job needs to be checked out.'));
    $this->assertText('System information (English to Spanish, Unprocessed)');
    $this->drupalPostForm(NULL, array(), t('Submit to translator'));

    // Make sure that we're back on the originally defined destination URL.
    $this->assertUrl('admin/config/system/site-information/translate');

    // Translated languages should now be listed as Needs review.
    $rows = $this->xpath('//tbody/tr');
    $counter = 0;
    foreach ($rows as $value) {
      if ($value->td[2]->a == 'Needs review') {
        $this->assertEqual('Spanish' || 'German', (string) $value->td[1]);
        $counter++;
      }
    }
    $this->assertEqual($counter, 2);
  }

}
