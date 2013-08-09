<?php

/**
 * @file
 * Contains \Drupal\tmgmt_entity_ui\Tests\EntitySourceListTest.
 */

namespace Drupal\tmgmt_entity_ui\Tests;

use Drupal\tmgmt\Tests\EntityTestBase;

class EntitySourceListTest extends EntityTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('tmgmt_entity_ui', 'taxonomy', 'comment');

  protected $nodes = array();

  static function getInfo() {
    return array(
      'name' => 'Entity Source List tests',
      'description' => 'Tests the user interface for entity translation lists.',
      'group' => 'Translation Management',
    );
  }

  function setUp() {
    parent::setUp();
    $this->loginAsAdmin();

    $this->addLanguage('de');
    $this->addLanguage('fr');

    $this->createNodeType('article', 'Article', TRUE);
    $this->createNodeType('page', 'Page', TRUE);

    // Enable entity translations for nodes and comments.
    content_translation_set_config('node', 'article', 'enabled', TRUE);
    content_translation_set_config('node', 'page', 'enabled', TRUE);
    content_translation_set_config('comment', 'comment_node_page', 'enabled', TRUE);
    content_translation_set_config('comment', 'comment_node_article', 'enabled', TRUE);
    menu_router_rebuild();

    // Create nodes that will be used during tests.
    // NOTE that the order matters as results are read by xpath based on
    // position in the list.
    $this->nodes['page']['en'][] = $this->createNode('page');
    $this->nodes['article']['de'][0] = $this->createNode('article', 'de');
    $this->nodes['article']['fr'][0] = $this->createNode('article', 'fr');
    $this->nodes['article']['en'][3] = $this->createNode('article', 'en');
    $this->nodes['article']['en'][2] = $this->createNode('article', 'en');
    $this->nodes['article']['en'][1] = $this->createNode('article', 'en');
    $this->nodes['article']['en'][0] = $this->createNode('article', 'en');
  }

  /**
   * Tests that the term bundle filter works.
   */
  function dtestTermBundleFilter() {

    $vocabulary1 = entity_create('taxonomy_vocabulary', array(
      'vid' => 'vocab1',
      'name' => $this->randomName(),
    ));
    $vocabulary1->save();

    $term1 = entity_create('taxonomy_term', array(
      'name' => $this->randomName(),
      'vid' => $vocabulary1->vid,
    ));
    $term1->save();

    $vocabulary2 = entity_create('taxonomy_vocabulary', array(
      'vid' => 'vocab2',
      'name' => $this->randomName(),
    ));
    $vocabulary2->save();

    $term2 = entity_create('taxonomy_term', array(
      'name' => $this->randomName(),
      'vid' => $vocabulary2->vid,
    ));
    $term2->save();

    content_translation_set_config('taxonomy_term', $vocabulary1->id(), 'enabled', TRUE);
    content_translation_set_config('taxonomy_term', $vocabulary2->id(), 'enabled', TRUE);

    $this->drupalGet('admin/tmgmt/sources/entity_taxonomy_term');
    // Both terms should be displayed with their bundle.
    $this->assertText($term1->label());
    $this->assertText($term2->label());
    $this->assertTrue($this->xpath('//td[text()=@vocabulary]', array('@vocabulary' => $vocabulary1->label())));
    $this->assertTrue($this->xpath('//td[text()=@vocabulary]', array('@vocabulary' => $vocabulary2->label())));

    // Limit to the first vocabulary.
    $edit = array();
    $edit['search[vocabulary_machine_name]'] = $vocabulary1->id();
    $this->drupalPost(NULL, $edit, t('Search'));
    // Only term 1 should be displayed now.
    $this->assertText($term1->label());
    $this->assertNoText($term2->label());
    $this->assertTrue($this->xpath('//td[text()=@vocabulary]', array('@vocabulary' => $vocabulary1->label())));
    $this->assertFalse($this->xpath('//td[text()=@vocabulary]', array('@vocabulary' => $vocabulary2->label())));

  }

  function dtestAvailabilityOfEntityLists() {

    $this->drupalGet('admin/tmgmt/sources/entity_comment');
    // Check if we are at comments page.
    $this->assertText(t('Comment overview (Entity)'));
    // No comments yet - empty message is expected.
    $this->assertText(t('No entities matching given criteria have been found.'));

    $this->drupalGet('admin/tmgmt/sources/entity_node');
    // Check if we are at nodes page.
    $this->assertText(t('Content overview (Entity)'));
    // We expect article title as article node type is entity translatable.
    $this->assertText($this->nodes['article']['en'][0]->label());
    // Page node type should not be listed as it is not entity translatable.
    $this->assertNoText($this->nodes['page']['en'][0]->label());
  }

  function dtestTranslationStatuses() {

    // Test statuses: Source, Missing.
    $this->drupalGet('admin/tmgmt/sources/entity_node');
    $langstatus_en = $this->xpath('//table[@id="tmgmt-entities-list"]/tbody/tr[1]/td[@class="langstatus-en"]');
    $langstatus_de = $this->xpath('//table[@id="tmgmt-entities-list"]/tbody/tr[1]/td[@class="langstatus-de"]');

    $this->assertEqual($langstatus_en[0]->div['title'], t('Source language'));
    $this->assertEqual($langstatus_de[0]->div['title'], t('Not translated'));

    // Test status: Active job item.
    $job = $this->createJob('en', 'de');
    $job->translator = $this->default_translator->name;
    $job->settings = array();
    $job->save();

    $job->addItem('entity', 'node', $this->nodes['article']['en'][0]->id());
    $job->requestTranslation();

    $this->drupalGet('admin/tmgmt/sources/entity_node');
    $langstatus_de = $this->xpath('//table[@id="tmgmt-entities-list"]/tbody/tr[1]/td[@class="langstatus-de"]/a');

    $items = $job->getItems();
    $states = tmgmt_job_item_states();
    $label = t('Active job item: @state', array('@state' => $states[reset($item)->getState()]));

    $this->assertEqual($langstatus_de[0]->div['title'], $label);

    // Test status: Current
    foreach ($job->getItems() as $job_item) {
      $job_item->acceptTranslation();
    }

    $this->drupalGet('admin/tmgmt/sources/entity_node');
    $langstatus_de = $this->xpath('//table[@id="tmgmt-entities-list"]/tbody/tr[1]/td[@class="langstatus-de"]');

    $this->assertEqual($langstatus_de[0]->div['title'], t('Translation up to date'));
  }

  function dtestTranslationSubmissions() {

    // Simple submission.
    $nid = $this->nodes['article']['en'][0]->id();
    $edit = array();
    $edit["items[$nid]"] = 1;
    $this->drupalPost('admin/tmgmt/sources/entity_node', $edit, t('Request translation'));
    $this->assertText(t('One job needs to be checked out.'));

    // Submission of two entities of the same source language.
    $nid1 = $this->nodes['article']['en'][0]->id();
    $nid2 = $this->nodes['article']['en'][1]->id();
    $edit = array();
    $edit["items[$nid1]"] = 1;
    $edit["items[$nid2]"] = 1;
    $this->drupalPost('admin/tmgmt/sources/entity_node', $edit, t('Request translation'));
    $this->assertText(t('One job needs to be checked out.'));

    // Submission of several entities of different source languages.
    $nid1 = $this->nodes['article']['en'][0]->id();
    $nid2 = $this->nodes['article']['en'][1]->id();
    $nid3 = $this->nodes['article']['en'][2]->id();
    $nid4 = $this->nodes['article']['en'][3]->id();
    $nid5 = $this->nodes['article']['de'][0]->id();
    $nid6 = $this->nodes['article']['fr'][0]->id();
    $edit = array();
    $edit["items[$nid1]"] = 1;
    $edit["items[$nid2]"] = 1;
    $edit["items[$nid3]"] = 1;
    $edit["items[$nid4]"] = 1;
    $edit["items[$nid5]"] = 1;
    $edit["items[$nid6]"] = 1;
    $this->drupalPost('admin/tmgmt/sources/entity_node', $edit, t('Request translation'));
    $this->assertText(t('@count jobs need to be checked out.', array('@count' => '3')));
  }

  function testNodeEntityListings() {

    // Turn off the entity translation.
    content_translation_set_config('node', 'article', 'enabled', FALSE);
    content_translation_set_config('node', 'page', 'enabled', FALSE);
    //menu_router_rebuild();

    // Check if we have appropriate message in case there are no entity
    // translatable content types.
    $this->drupalGet('admin/tmgmt/sources/entity_node');
    $this->assertText(t('Entity translation is not enabled for any of existing content types. To use this functionality go to Content types administration and enable entity translation for desired content types.'));

    // Turn on the entity translation for both - article and page - to test
    // search form.
    content_translation_set_config('node', 'article', 'enabled', TRUE);
    content_translation_set_config('node', 'page', 'enabled', TRUE);
    menu_router_rebuild();

    // Create page node after entity translation is enabled.
    $page_node_translatable = $this->createNode('page');

    $this->drupalGet('admin/tmgmt/sources/entity_node');
    // We have both listed - one of articles and page.
    $this->assertText($this->nodes['article']['en'][0]->label());
    $this->assertText($page_node_translatable->label());

    // Try the search by content type.
    $edit = array();
    $edit['search[type]'] = 'article';
    $this->drupalPost('admin/tmgmt/sources/entity_node', $edit, t('Search'));
    // There should be article present.
    $this->assertText($this->nodes['article']['en'][0]->label());
    // The page node should not be listed.
    $this->assertNoText($page_node_translatable->label());

    // Try cancel button - despite we do post content type search value
    // we should get nodes of botch content types.
    $this->drupalPost('admin/tmgmt/sources/entity_node', $edit, t('Cancel'));
    $this->assertText($this->nodes['article']['en'][0]->label());
    $this->assertText($page_node_translatable->label());
  }

  function dtestEntitySourceListSearch() {

    // We need a node with title composed of several words to test
    // "any words" search.
    $title_part_1 = $this->randomName('4');
    $title_part_2 = $this->randomName('4');
    $title_part_3 = $this->randomName('4');

    $this->nodes['article']['en'][0]->title = "$title_part_1 $title_part_2 $title_part_3";
    $this->nodes['article']['en'][0]->save();

    // Submit partial node title and see if we have a result.
    $edit = array();
    $edit['search[title]'] = "$title_part_1 $title_part_3";
    $this->drupalPost('admin/tmgmt/sources/entity_node', $edit, t('Search'));
    $this->assertText("$title_part_1 $title_part_2 $title_part_3", 'Searching on partial node title must return the result.');

    // Check if there is only one result in the list.
    $search_result_rows = $this->xpath('//table[@id="tmgmt-entities-list"]/tbody/tr');
    $this->assert(count($search_result_rows) == 1, 'The search result must return only one row.');

    // To test if other entity types work go for simple comment search.
    $comment = entity_create('comment', array('node_type' => 'comment_node_article'));
    $comment->comment_body->value = $this->randomName();
    $comment->subject = $this->randomName();
    // We need to associate the comment with entity translatable node object.
    $comment->nid = $this->nodes['article']['en'][0]->id();
    // Will add further comment variables.
    $comment->save();
    // Do search for the comment.
    $edit = array();
    $edit['search[subject]'] = $comment->subject->value;
    $this->drupalPost('admin/tmgmt/sources/entity_comment', $edit, t('Search'));
    $this->assertText($comment->subject->value, 'Searching for a comment subject.');
  }
}
