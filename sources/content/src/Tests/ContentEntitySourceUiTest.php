<?php

/**
 * @file
 * Contains \Drupal\tmgmt_content\Tests\ContentEntitySourceUiTest.
 */

namespace Drupal\tmgmt_content\Tests;

use Drupal\comment\Entity\Comment;
use Drupal\Core\Url;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\node\Entity\Node;
use Drupal\tmgmt\Entity\Translator;
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
  public static $modules = array('tmgmt_content', 'comment', 'ckeditor');

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
    $this->assertEqual($node->url('canonical', array('absolute' => TRUE)) . '/translations', $this->getUrl());
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

    // Go to the translate tab.
    $this->drupalGet('node/' . $node->id());
    $this->clickLink(t('Translate'));
    // Request a translation for french.
    $edit = array(
      'languages[fr]' => TRUE,
    );
    $this->drupalPostForm(NULL, $edit, t('Request translation'));
    $this->drupalGet('node/' . $node->id() . '/translations', array('query' => array('destination' => 'node/' . $node->id())));
    // Test that the translation in progress is working.
    $this->clickLink(t('In progress'));
    $this->assertText($node->getTitle());
    $this->assertRaw('<div data-drupal-selector="edit-actions" class="form-actions js-form-wrapper form-wrapper" id="edit-actions">');

    // Assert that the validation of HTML tags with editor works.
    $this->drupalPostForm(NULL, [], t('Validate HTML tags'));
    $this->assertText($node->label());
    $this->assertResponse(200);

    $this->drupalGet('node/' . $node->id() . '/translations', array('query' => array('destination' => 'node/' . $node->id())));

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
    $this->assertEqual($node->url('canonical', array('absolute' => TRUE)), $this->getUrl());

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
    \Drupal::entityManager()->getStorage('node')->resetCache();
    $node = Node::load($node->id());
    $node->getTranslation('de')->content_translation_outdated->value = 1;
    $node->save();
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

    // Test that a job can not be accepted if the entity does not exist.
    $deleted_node = $this->createNode('page', 'en');
    $this->drupalGet('node/' . $deleted_node->id() . '/translations');
    $edit = array(
      'languages[de]' => TRUE,
    );
    $this->drupalPostForm(NULL, $edit, t('Request translation'));
    $this->drupalPostForm(NULL, array(), t('Submit to translator'));
    $this->clickLink(t('Needs review'));

    // Delete the node and assert that the job can not be accepted.
    $deleted_node->delete();
    $this->drupalPostForm(NULL, array(), t('Save as completed'));
    $this->assertText(t('@id of type @type does not exist, the job can not be completed.', array('@id' => $deleted_node->id(), '@type' => $deleted_node->getEntityTypeId())));
  }

  /**
   * Test the translate tab for a single checkout.
   */
  function testNodeTranslateTabMultipeCheckout() {
    // Allow auto-accept.
    $default_translator = Translator::load('test_translator');
    $default_translator
      ->setSetting('auto_accept', TRUE)
      ->save();

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
    $this->assertEqual($node->url('canonical', array('absolute' => TRUE)) . '/translations', $this->getUrl());
    $this->assertText(t('Test translation created.'));
    $this->assertNoText(t('The translation of @title to @language is finished and can now be reviewed.', array(
      '@title' => $node->getTitle(),
      '@language' => t('Spanish')
    )));
    $this->assertText(t('The translation for @title has been accepted.', array('@title' => $node->getTitle())));

    //Assert link is clickable.
    $this->clickLink($node->getTitle());

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
   */
  function testCommentTranslateTab() {
    // Allow auto-accept.
    $default_translator = Translator::load('test_translator');
    $default_translator
      ->setSetting('auto_accept', TRUE)
      ->save();

    // Add default comment type.
    $this->addDefaultCommentField('node', 'article');

    // Enable comment translation.
    /** @var \Drupal\content_translation\ContentTranslationManagerInterface $content_translation_manager */
    $content_translation_manager = \Drupal::service('content_translation.manager');
    $content_translation_manager->setEnabled('comment', 'comment', TRUE);
    drupal_static_reset();
    \Drupal::entityManager()->clearCachedDefinitions();
    \Drupal::service('router.builder')->rebuild();
    \Drupal::service('entity.definition_update_manager')->applyUpdates();
    $this->applySchemaUpdates();

    // Change comment_body field to be translatable.
    $comment_body = FieldConfig::loadByName('comment', 'comment', 'comment_body');
    $comment_body->setTranslatable(TRUE)->save();

    // Create a user that is allowed to translate comments.
    $permissions = array_merge($this->translator_permissions, array(
      'translate comment',
      'post comments',
      'skip comment approval',
      'edit own comments',
      'access comments',
    ));
    $this->loginAsTranslator($permissions, TRUE);

    // Create an english source article.
    $node = $this->createNode('article', 'en');

    // Add a comment.
    $this->drupalGet('node/' . $node->id());
    $edit = array(
      'subject[0][value]' => $this->randomMachineName(),
      'comment_body[0][value]' => $this->randomMachineName(),
    );
    $this->drupalPostForm(NULL, $edit, t('Save'));
    $this->assertText(t('Your comment has been posted.'));

    // Go to the translate tab.
    $this->clickLink('Edit');
    $this->assertTrue(preg_match('|comment/(\d+)/edit$|', $this->getUrl(), $matches), 'Comment found');
    $comment = Comment::load($matches[1]);
    $this->clickLink('Translate');

    // Assert some basic strings on that page.
    $this->assertText(t('Translations of @title', array('@title' => $comment->getSubject())));
    $this->assertText(t('Pending Translations'));

    // Request translations.
    $edit = array(
      'languages[de]' => TRUE,
      'languages[es]' => TRUE,
    );
    $this->drupalPostForm(NULL, $edit, t('Request translation'));

    // Verify that we are on the translate tab.
    $this->assertText(t('2 jobs need to be checked out.'));

    // Submit all jobs.
    $this->assertText($comment->getSubject());
    $this->drupalPostForm(NULL, array(), t('Submit to translator and continue'));
    $this->assertText($comment->getSubject());
    $this->drupalPostForm(NULL, array(), t('Submit to translator'));

    // Make sure that we're back on the translate tab.
    $this->assertUrl($comment->url('canonical', array('absolute' => TRUE)) . '/translations');
    $this->assertText(t('Test translation created.'));
    $this->assertNoText(t('The translation of @title to @language is finished and can now be reviewed.', array(
      '@title' => $comment->getSubject(),
      '@language' => t('Spanish'),
    )));
    $this->assertText(t('The translation for @title has been accepted.', array('@title' => $comment->getSubject())));

    // The translated content should be in place.
    $this->clickLink('de_' . $comment->getSubject());
    $this->assertText('de_' . $comment->get('comment_body')->value);
    // @todo Remove when https://www.drupal.org/node/2444267 is fixed.
    \Drupal::entityManager()->getViewBuilder('node')->resetCache();
    $this->drupalGet('comment/1/translations');
    $this->clickLink('es_' . $comment->getSubject());
    $this->drupalGet('es/node/' . $comment->id());
    $this->assertText('es_' . $comment->get('comment_body')->value);
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
        array('@count' => 2, '@url' => Url::fromRoute('tmgmt.cart')->toString())));

    $this->drupalPostForm(NULL, array(), t('Add to cart'));
    $this->assertRaw(t('@count content source was added into the <a href="@url">cart</a>.', array('@count' => 1, '@url' => Url::fromRoute('tmgmt.cart')->toString())));
    $this->assertRaw(t('There are @count items in the <a href="@url">translation cart</a> including the current item.',
        array('@count' => 3, '@url' => Url::fromRoute('tmgmt.cart')->toString())));
  }

  /**
   * Tests the embedded references.
   */
  function testEmbeddedReferences() {
    // Create two reference fields, one to a translatable and untranslatable
    // node type, one only for a untranslatable. Only the first one should be
    // available.

    $field1 = FieldStorageConfig::create(
        array(
          'field_name' => 'field1',
          'entity_type' => 'node',
          'type' => 'entity_reference',
          'cardinality' => -1,
          'settings' => array('target_type' => 'node'),
        )
      );
    $field1->save();
    $field2 = FieldStorageConfig::create(
      array(
        'field_name' => 'field2',
        'entity_type' => 'node',
        'type' => 'entity_reference',
        'cardinality' => -1,
        'settings' => array('target_type' => 'node'),
      )
    );
    $field2->save();

    $this->createNodeType('untranslatable', 'Untranslatable', FALSE);

    // Create field instances on the content type.
    FieldConfig::create(
      array(
        'field_storage' => $field1,
        'bundle' => 'article',
        'label' => 'Field 1',
        'translatable' => FALSE,
        'settings' => array(),
      )
    )->save();
    FieldConfig::create(
      array(
        'field_storage' => $field1,
        'bundle' => 'untranslatable',
        'label' => 'Field 1',
        'translatable' => FALSE,
        'settings' => array(),
      )
    )->save();
    FieldConfig::create(
      array(
        'field_storage' => $field2,
        'bundle' => 'untranslatable',
        'label' => 'Field 2',
        'translatable' => FALSE,
        'settings' => array(),
      )
    )->save();

    $this->drupalGet('admin/config/regional/tmgmt_settings');

    $checked_reference_fields = array(
      'embedded_fields[node][field1]' => TRUE,
    );

    $this->assertNoField('embedded_fields[node][field_image]');
    $this->assertNoField('embedded_fields[node][field_tags]');
    $this->assertNoField('embedded_fields[node][title]');
    $this->assertNoField('embedded_fields[node][uid]');
    $this->assertNoField('embedded_fields[node][field2]');
    $this->assertNoField('embedded_fields[node][type]');

    $this->drupalPostForm(NULL, $checked_reference_fields, t('Save configuration'));

    // Check if the save was successful.
    $this->assertText(t('The configuration options have been saved.'));
    $this->assertFieldChecked('edit-embedded-fields-node-field1');
  }

}
