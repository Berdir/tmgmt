<?php

/**
 * @file
 * Contains EntitySourceUITest
 */

namespace Drupal\tmgmt_entity_ui\Tests;

use Drupal\tmgmt\Tests\EntityTestBase;

/**
 * Basic Node Source tests.
 */
class EntitySourceUiTest extends EntityTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('tmgmt_entity_ui', 'block', 'comment');

  static function getInfo() {
    return array(
      'name' => 'Entity Source UI tests',
      'description' => 'Tests the user interface for entity translation sources.',
      'group' => 'Translation Management',
    );
  }

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

    drupal_static_reset();
    entity_info_cache_clear();
    menu_router_rebuild();

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

    // Go to the translate tab.
    $this->drupalGet('node/' . $node->nid);
    $this->clickLink('Translate');

    // Assert some basic strings on that page.
    $this->assertText(t('Translations of @title', array('@title' => $node->title)));
    $this->assertText(t('Pending Translations'));

    // Request a translation for german.
    $edit = array(
      'languages[de]' => TRUE,
    );
    $this->drupalPost(NULL, $edit, t('Request translation'));

    // Verify that we are on the translate tab.
    $this->assertText(t('One job needs to be checked out.'));
    $this->assertText($node->title);

    // Submit.
    $this->drupalPost(NULL, array(), t('Submit to translator'));

    // Make sure that we're back on the translate tab.
    $this->assertEqual(url('node/' . $node->nid . '/translations', array('absolute' => TRUE)), $this->getUrl());
    $this->assertText(t('Test translation created.'));
    $this->assertText(t('The translation of @title to @language is finished and can now be reviewed.', array(
      '@title' => $node->title,
      '@language' => t('German')
    )));

    // Verify that the pending translation is shown.
    $this->clickLink(t('Needs review'));
    $this->drupalPost(NULL, array(), t('Save as completed'));

    $this->assertText(t('The translation for @title has been accepted.', array('@title' => $node->title)));

    // German node should now be listed and be clickable.
    // @todo Improve detection of the link, e.g. use xpath on the table or the
    // title module to get a better title.
    $this->clickLink($node->label(), 3);
    $this->assertText('de_' . $node->body['en'][0]['value']);

    // Test that the destination query argument does not break the redirect
    // and we are redirected back to the correct page.
    $this->drupalGet('node/' . $node->nid . '/translate', array('query' => array('destination' => 'node')));

    // Request a spanish translation.
    $edit = array(
      'languages[es]' => TRUE,
    );
    $this->drupalPost(NULL, $edit, t('Request translation'));

    // Verify that we are on the checkout page.
    $this->assertText(t('One job needs to be checked out.'));
    $this->assertText($node->title);
    $this->drupalPost(NULL, array(), t('Submit to translator'));

    // Make sure that we're back on the originally defined destination URL.
    $this->assertEqual(url('node', array('absolute' => TRUE)), $this->getUrl());
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
    $this->drupalGet('node/' . $node->nid);
    $this->clickLink('Translate');

    // Assert some basic strings on that page.
    $this->assertText(t('Translations of @title', array('@title' => $node->title)));
    $this->assertText(t('Pending Translations'));

    // Request a translation for german.
    $edit = array(
      'languages[de]' => TRUE,
      'languages[es]' => TRUE,
    );
    $this->drupalPost(NULL, $edit, t('Request translation'));

    // Verify that we are on the translate tab.
    $this->assertText(t('2 jobs need to be checked out.'));

    // Submit all jobs.
    $this->assertText($node->title);
    $this->drupalPost(NULL, array(), t('Submit to translator and continue'));
    $this->assertText($node->title);
    $this->drupalPost(NULL, array(), t('Submit to translator'));

    // Make sure that we're back on the translate tab.
    $this->assertEqual(url('node/' . $node->nid . '/translations', array('absolute' => TRUE)), $this->getUrl());
    $this->assertText(t('Test translation created.'));
    $this->assertNoText(t('The translation of @title to @language is finished and can now be reviewed.', array(
      '@title' => $node->title,
      '@language' => t('Spanish')
    )));
    $this->assertText(t('The translation for @title has been accepted.', array('@title' => $node->title)));

    // Translated nodes should now be listed and be clickable.
    // @todo Use links on translate tab.
    $this->drupalGet('de/node/' . $node->nid);
    $this->assertText('de_' . $node->body['en'][0]['value']);

    $this->drupalGet('es/node/' . $node->nid);
    $this->assertText('es_' . $node->body['en'][0]['value']);
  }

  /**
   * Test translating comments.
   *
   * @todo: Disabled pending resolution of http://drupal.org/node/1760270.
   */
  function dtestCommentTranslateTab() {

    // Login as admin to be able to submit config page.
    $this->loginAsAdmin(array('administer entity translation'));
    // Enable comment translation.
    $edit = array(
      'entity_translation_entity_types[comment]' => TRUE
    );
    $this->drupalPost('admin/config/regional/entity_translation', $edit, t('Save configuration'));

    // Change comment_body field to be translatable.
    $comment_body = field_info_field('comment_body');
    $comment_body['translatable'] = TRUE;
    field_update_field($comment_body);

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
    $this->drupalGet('node/' . $node->nid);
    $edit = array(
      'subject' => $this->randomName(),
      'comment_body[en][0][value]' => $this->randomName(),
    );
    $this->drupalPost(NULL, $edit, t('Save'));
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
    $this->drupalPost(NULL, $edit, t('Request translation'));

    // Verify that we are on the translate tab.
    $this->assertText(t('2 jobs need to be checked out.'));

    // Submit all jobs.
    $this->assertText($comment->subject);
    $this->drupalPost(NULL, array(), t('Submit to translator and continue'));
    $this->assertText($comment->subject);
    $this->drupalPost(NULL, array(), t('Submit to translator'));

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
}
