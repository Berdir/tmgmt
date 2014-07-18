<?php

/**
 * @file
 * Contains \Drupal\tmgmt_content\Tests\ContentEntitySourceUiTest.
 */

namespace Drupal\tmgmt_content\Tests;

use Drupal\field\Entity\FieldStorageConfig;
use Drupal\tmgmt\Tests\EntityTestBase;

/**
 * Content entity source UI tests.
 *
 * @group tmgmt
 */
class ContentEntitySourceUiTest extends EntityTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('tmgmt_content', 'comment');

  /**
   * {@inheritdoc}
   */
  function setUp() {
    parent::setUp();

    $this->loginAsAdmin(array(
      'create translation jobs',
      'submit translation jobs',
      'accept translation jobs',
      'administer blocks',
      'administer content translation',
    ));

    $this->addLanguage('de');
    $this->addLanguage('fr');
    $this->addLanguage('es');
    $this->addLanguage('el');

    $this->createNodeType('page', 'Page', TRUE);
    $this->createNodeType('article', 'Article', TRUE);

    // @todo: Find a way that doesn't require the block.
    $this->drupalPlaceBlock('system_main_block', array('region' => 'content'));
  }

  /**
   * Test the translate tab for a single checkout.
   */
  function testNodeTranslateTabSingleCheckout() {
    $this->loginAsTranslator(array('translate any entity', 'create content translations'));

    // Create an english source node.
    $node = $this->createNode('page', 'en');
    // Create a nodes that will not be translated to test the missing
    // translation filter.
    $node_not_translated = $this->createNode('page', 'en');
    $node_german = $this->createNode('page', 'de');

    // Go to the translate tab.
    $this->drupalGet('node/' . $node->id());
    $this->clickLink('Translate');

    // Assert some basic strings on that page.
    $this->assertText(t('Translations of @title', array('@title' => $node->getTitle())));
    $this->assertText(t('Pending Translations'));

    // Request a translation for german.
    $edit = array(
      'languages[de]' => TRUE,
    );
    $this->drupalPostForm(NULL, $edit, t('Request translation'));

    // Verify that we are on the translate tab.
    $this->assertText(t('One job needs to be checked out.'));
    $this->assertText($node->getTitle());

    // Submit.
    $this->drupalPostForm(NULL, array(), t('Submit to translator'));

    // Make sure that we're back on the translate tab.
    $this->assertEqual(url('node/' . $node->id() . '/translations', array('absolute' => TRUE)), $this->getUrl());
    $this->assertText(t('Test translation created.'));
    $this->assertText(t('The translation of @title to @language is finished and can now be reviewed.', array(
      '@title' => $node->getTitle(),
      '@language' => t('German')
    )));

    // Verify that the pending translation is shown.
    $this->clickLink(t('Needs review'));
    $this->drupalPostForm(NULL, array(), t('Save as completed'));

    $this->assertText(t('The translation for @title has been accepted.', array('@title' => $node->getTitle())));

    // German node should now be listed and be clickable.
    $this->clickLink('de_' . $node->label());
    $this->assertText('de_' . $node->getTitle());
    $this->assertText('de_' . $node->body->value);

    // Test that the destination query argument does not break the redirect
    // and we are redirected back to the correct page.
    $this->drupalGet('node/' . $node->id() . '/translations', array('query' => array('destination' => 'node')));

    // Request a spanish translation.
    $edit = array(
      'languages[es]' => TRUE,
    );
    $this->drupalPostForm(NULL, $edit, t('Request translation'));

    // Verify that we are on the checkout page.
    $this->assertText(t('One job needs to be checked out.'));
    $this->assertText($node->getTitle());
    $this->drupalPostForm(NULL, array(), t('Submit to translator'));

    // Make sure that we're back on the originally defined destination URL.
    $this->assertEqual(url('node', array('absolute' => TRUE)), $this->getUrl());

    // Test the missing translation filter.
    $this->drupalGet('admin/tmgmt/sources/content/node');
    $this->assertText($node->getTitle());
    $this->assertText($node_not_translated->getTitle());
    $this->drupalPostForm(NULL, array(
      'search[target_language]' => 'de',
      'search[target_status]' => 'untranslated',
    ), t('Search'));
    $this->assertNoText($node->getTitle());
    $this->assertNoText($node_german->getTitle());
    $this->assertText($node_not_translated->getTitle());
    // Update the the outdated flag of the translated node and test if it is
    // listed among sources with missing translation.
    db_update('content_translation')
      ->fields(array('outdated' => 1))
      ->condition('entity_type', 'node')
      ->condition('entity_id', $node->id())
      ->execute();
    $this->drupalPostForm(NULL, array(
      'search[target_language]' => 'de',
      'search[target_status]' => 'outdated',
    ), t('Search'));
    $this->assertText($node->getTitle());
    $this->assertNoText($node_german->getTitle());
    $this->assertNoText($node_not_translated->getTitle());

    $this->drupalPostForm(NULL, array(
      'search[target_language]' => 'de',
      'search[target_status]' => 'untranslated_or_outdated',
    ), t('Search'));
    $this->assertText($node->getTitle());
    $this->assertNoText($node_german->getTitle());
    $this->assertText($node_not_translated->getTitle());
  }

  /**
   * Test the translate tab for a single checkout.
   */
  function testNodeTranslateTabMultipeCheckout() {
    // Allow auto-accept.
    $default_translator = tmgmt_translator_load('test_translator');
    $default_translator->settings = array(
      'auto_accept' => TRUE,
    );
    $default_translator->save();

    $this->loginAsTranslator(array('translate any entity', 'create content translations'));

    // Create an english source node.
    $node = $this->createNode('page', 'en');

    // Go to the translate tab.
    $this->drupalGet('node/' . $node->id());
    $this->clickLink('Translate');

    // Assert some basic strings on that page.
    $this->assertText(t('Translations of @title', array('@title' => $node->getTitle())));
    $this->assertText(t('Pending Translations'));

    // Request a translation for german.
    $edit = array(
      'languages[de]' => TRUE,
      'languages[es]' => TRUE,
    );
    $this->drupalPostForm(NULL, $edit, t('Request translation'));

    // Verify that we are on the translate tab.
    $this->assertText(t('2 jobs need to be checked out.'));

    // Submit all jobs.
    $this->assertText($node->getTitle());
    $this->drupalPostForm(NULL, array(), t('Submit to translator and continue'));
    $this->assertText($node->getTitle());
    $this->drupalPostForm(NULL, array(), t('Submit to translator'));

    // Make sure that we're back on the translate tab.
    $this->assertEqual(url('node/' . $node->id() . '/translations', array('absolute' => TRUE)), $this->getUrl());
    $this->assertText(t('Test translation created.'));
    $this->assertNoText(t('The translation of @title to @language is finished and can now be reviewed.', array(
      '@title' => $node->getTitle(),
      '@language' => t('Spanish')
    )));
    $this->assertText(t('The translation for @title has been accepted.', array('@title' => $node->getTitle())));

    // Translated nodes should now be listed and be clickable.
    // @todo Use links on translate tab.
    $this->drupalGet('de/node/' . $node->id());
    $this->assertText('de_' . $node->getTitle());
    $this->assertText('de_' . $node->body->value);

    $this->drupalGet('es/node/' . $node->id());
    $this->assertText('es_' . $node->getTitle());
    $this->assertText('es_' . $node->body->value);
  }

  /**
   * Test translating comments.
   *
   * @todo: Disabled pending resolution of http://drupal.org/node/1760270.
   */
  function dtestCommentTranslateTab() {

    // Login as admin to be able to submit config page.
    $this->loginAsAdmin(array('translate any entity', 'create comment translations'));
    // Enable comment translation.
    $edit = array(
      'entity_translation_entity_types[comment]' => TRUE
    );
    $this->drupalPostForm('admin/config/regional/entity_translation', $edit, t('Save configuration'));

    // Change comment_body field to be translatable.
    $comment_body = FieldStorageConfig::loadByName('comment', 'comment_body');
    $comment_body->translatable = TRUE;
    $comment_body->save();

    // Create a user that is allowed to translate comments.
    $permissions = array(
      'translate comment entities',
      'create translation jobs',
      'submit translation jobs',
      'accept translation jobs',
      'post comments',
      'skip comment approval',
      'edit own comments',
      'access comments'
    );
    $entity_translation_permissions = entity_translation_permission();
    // The new translation edit form of entity_translation requires a new
    // permission that does not yet exist in older versions. Add it
    // conditionally.
    if (isset($entity_translation_permissions['edit original values'])) {
      $permissions[] = 'edit original values';
    }
    $this->loginAsTranslator($permissions, TRUE);

    // Create an english source term.
    $node = $this->createNode('article', 'en');

    // Add a comment.
    $this->drupalGet('node/' . $node->id());
    $edit = array(
      'subject' => $this->randomName(),
      'comment_body[en][0][value]' => $this->randomName(),
    );
    $this->drupalPostForm(NULL, $edit, t('Save'));
    $this->assertText(t('Your comment has been posted.'));

    // Go to the translate tab.
    $this->clickLink('edit');
    $this->assertTrue(preg_match('|comment/(\d+)/edit$|', $this->getUrl(), $matches), 'Comment found');
    $comment = comment_load($matches[1]);
    $this->clickLink('Translate');

    // Assert some basic strings on that page.
    $this->assertText(t('Translations of @title', array('@title' => $comment->subject)));
    $this->assertText(t('Pending Translations'));

    // Request a translation for german.
    $edit = array(
      'languages[de]' => TRUE,
      'languages[es]' => TRUE,
    );
    $this->drupalPostForm(NULL, $edit, t('Request translation'));

    // Verify that we are on the translate tab.
    $this->assertText(t('2 jobs need to be checked out.'));

    // Submit all jobs.
    $this->assertText($comment->subject);
    $this->drupalPostForm(NULL, array(), t('Submit to translator and continue'));
    $this->assertText($comment->subject);
    $this->drupalPostForm(NULL, array(), t('Submit to translator'));

    // Make sure that we're back on the translate tab.
    $this->assertEqual(url('comment/' . $comment->cid . '/translate', array('absolute' => TRUE)), $this->getUrl());
    $this->assertText(t('Test translation created.'));
    $this->assertNoText(t('The translation of @title to @language is finished and can now be reviewed.', array(
      '@title' => $comment->subject,
      '@language' => t('Spanish')
    )));
    $this->assertText(t('The translation for @title has been accepted.', array('@title' => $comment->subject)));

    // @todo Use links on translate tab.
    $this->drupalGet('de/comment/' . $comment->cid);
    $this->assertText('de_' . $comment->comment_body['en'][0]['value']);

    // @todo Use links on translate tab.
    $this->drupalGet('es/node/' . $comment->cid);
    $this->assertText('es_' . $comment->comment_body['en'][0]['value']);
  }

  /**
   * Test the entity source specific cart functionality.
   */
  function testCart() {
    $this->loginAsTranslator(array('translate any entity', 'create content translations'));

    $nodes = array();
    for ($i = 0; $i < 4; $i++) {
      $nodes[$i] = $this->createNode('page');
    }

    // Test the source overview.
    $this->drupalPostForm('admin/tmgmt/sources/content/node', array(
      'items[' . $nodes[1]->id() . ']' => TRUE,
      'items[' . $nodes[2]->id() . ']' => TRUE,
    ), t('Add to cart'));

    $this->drupalGet('admin/tmgmt/cart');
    $this->assertText($nodes[1]->getTitle());
    $this->assertText($nodes[2]->getTitle());

    // Test the translate tab.
    $this->drupalGet('node/' . $nodes[3]->id() . '/translations');
    $this->assertRaw(t('There are @count items in the <a href="@url">translation cart</a>.',
        array('@count' => 2, '@url' => url('admin/tmgmt/cart'))));

    $this->drupalPostForm(NULL, array(), t('Add to cart'));
    $this->assertRaw(t('@count content source was added into the <a href="@url">cart</a>.', array('@count' => 1, '@url' => url('admin/tmgmt/cart'))));
    $this->assertRaw(t('There are @count items in the <a href="@url">translation cart</a> including the current item.',
        array('@count' => 3, '@url' => url('admin/tmgmt/cart'))));
  }
}
