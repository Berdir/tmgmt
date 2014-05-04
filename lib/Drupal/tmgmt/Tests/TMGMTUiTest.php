<?php

/*
 * @file
 * Contains Drupal\tmgmt\Tests\TMGMTUiTest.
 */

namespace Drupal\tmgmt\Tests;

/**
 * Test the UI of tmgmt, for example the checkout form.
 */
class TMGMTUiTest extends TMGMTTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('tmgmt_ui');

  static function getInfo() {
    return array(
      'name' => 'UI tests',
      'description' => 'Verifies basic functionality of the user interface',
      'group' => 'Translation Management',
    );
  }

  function setUp() {
    parent::setUp();

    // Login as admin to be able to set environment variables.
    $this->loginAsAdmin();
    $this->addLanguage('de');
    $this->addLanguage('es');
    $this->addLanguage('el');

    // Login as translator only with limited permissions to run these tests.
    $this->loginAsTranslator(array(
      'access administration pages',
      'create translation jobs',
      'submit translation jobs',
    ), TRUE);
  }

  /**
   * Test the page callbacks to create jobs and check them out.
   */
  function testCheckoutForm() {

    // Add a first item to the job. This will auto-create the job.
    $job = tmgmt_job_match_item('en', '');
    $job->addItem('test_source', 'test', 1);

    // Go to checkout form.
    $redirects = tmgmt_ui_job_checkout_multiple(array($job));
    $this->drupalGet(reset($redirects));

    // Check checkout form.
    $this->assertText('test_source:test:1');

    // Add two more job items.
    $job->addItem('test_source', 'test', 2);
    $job->addItem('test_source', 'test', 3);

    // Go to checkout form.
    $redirects = tmgmt_ui_job_checkout_multiple(array($job));
    $this->drupalGet(reset($redirects));

    // Check checkout form.
    $this->assertText('test_source:test:1');
    $this->assertText('test_source:test:2');
    $this->assertText('test_source:test:3');

    // @todo: Test ajax functionality.

    // Attempt to translate into greek.
    $edit = array(
      'target_language' => 'el',
      'settings[action]' => 'translate',
    );
    $this->drupalPostForm(NULL, $edit, t('Submit to translator'));
    $this->assertText(t('@translator can not translate from @source to @target.', array('@translator' => 'Test translator (auto created)', '@source' => 'English', '@target' => 'Greek')));

    // Job still needs to be in state new.
    $job = entity_load_unchanged('tmgmt_job', $job->id());
    $this->assertTrue($job->isUnprocessed());

    $edit = array(
      'target_language' => 'es',
      'settings[action]' => 'translate',
    );
    $this->drupalPostForm(NULL, $edit, t('Submit to translator'));

    // Job needs to be in state active.
    $job = entity_load_unchanged('tmgmt_job', $job->id());
    $this->assertTrue($job->isActive());
    foreach ($job->getItems() as $job_item) {
      /* @var $job_item TMGMTJobItem */
      $this->assertTrue($job_item->isNeedsReview());
    }
    $this->assertText(t('Test translation created'));
    $this->assertNoText(t('Test translator called'));

    // Test redirection.
    $this->assertText(t('Job overview'));

    // Another job.
    $previous_tjid = $job->id();
    $job = tmgmt_job_match_item('en', '');
    $job->addItem('test_source', 'test', 1);
    $this->assertNotEqual($job->id(), $previous_tjid);

    // Go to checkout form.
    $redirects = tmgmt_ui_job_checkout_multiple(array($job));
    $this->drupalGet(reset($redirects));

     // Check checkout form.
    $this->assertText('You can provide a label for this job in order to identify it easily later on.');
    $this->assertText('test_source:test:1');

    $edit = array(
      'target_language' => 'es',
      'settings[action]' => 'submit',
    );
    $this->drupalPostForm(NULL, $edit, t('Submit to translator'));
    $this->assertText(t('Test submit'));
    $job = entity_load_unchanged('tmgmt_job', $job->id());
    $this->assertTrue($job->isActive());

    // Another job.
    $job = tmgmt_job_match_item('en', 'es');
    $job->addItem('test_source', 'test', 1);

    // Go to checkout form.
    $redirects = tmgmt_ui_job_checkout_multiple(array($job));
    $this->drupalGet(reset($redirects));

     // Check checkout form.
    $this->assertText('You can provide a label for this job in order to identify it easily later on.');
    $this->assertText('test_source:test:1');

    $edit = array(
      'settings[action]' => 'reject',
    );
    $this->drupalPostForm(NULL, $edit, t('Submit to translator'));
    $this->assertText(t('This is not supported'));
    $job = entity_load_unchanged('tmgmt_job', $job->id());
    $this->assertTrue($job->isRejected());

    // Check displayed job messages.
    $args = array('@view' => 'view-tmgmt-ui-job-messages');
    $this->assertEqual(2, count($this->xpath('//div[contains(@class, @view)]//tbody/tr', $args)));

    // Check that the author for each is the current user.
    $message_authors = $this->xpath('////div[contains(@class, @view)]//td[contains(@class, @field)]/span', $args + array('@field' => 'views-field-name'));
    $this->assertEqual(2, count($message_authors));
    foreach ($message_authors as $message_author) {
      $this->assertEqual((string)$message_author, $this->translator_user->getUsername());
    }

    // Make sure that rejected jobs can be re-submitted.
    $this->assertTrue($job->isSubmittable());
    $edit = array(
      'settings[action]' => 'translate',
    );
    $this->drupalPostForm(NULL, $edit, t('Submit to translator'));
    $this->assertText(t('Test translation created'));

    // Another job.
    $job = tmgmt_job_match_item('en', 'es');
    $job->addItem('test_source', 'test', 1);

    // Go to checkout form.
    $redirects = tmgmt_ui_job_checkout_multiple(array($job));
    $this->drupalGet(reset($redirects));

     // Check checkout form.
    $this->assertText('You can provide a label for this job in order to identify it easily later on.');
    $this->assertText('test_source:test:1');

    $edit = array(
      'settings[action]' => 'fail',
    );
    $this->drupalPostForm(NULL, $edit, t('Submit to translator'));
    $this->assertText(t('Service not reachable'));
    $job = entity_load_unchanged('tmgmt_job', $job->id());
    $this->assertTrue($job->isUnprocessed());

    // Verify that we are still on the form.
    $this->assertText('You can provide a label for this job in order to identify it easily later on.');

    // Another job.
    $job = tmgmt_job_match_item('en', 'es');
    $job->addItem('test_source', 'test', 1);

    // Go to checkout form.
    $redirects = tmgmt_ui_job_checkout_multiple(array($job));
    $this->drupalGet(reset($redirects));

    // Check checkout form.
    $this->assertText('You can provide a label for this job in order to identify it easily later on.');
    $this->assertText('test_source:test:1');

    $edit = array(
      'settings[action]' => 'not_translatable',
    );
    $this->drupalPostForm(NULL, $edit, t('Submit to translator'));
    // @todo Update to correct failure message.
    $this->assertText(t('Fail'));
    $job = entity_load_unchanged('tmgmt_job', $job->id());
    $this->assertTrue($job->isUnprocessed());

    // Test default settings.
    $this->default_translator->settings['action'] = 'reject';
    $this->default_translator->save();
    $job = tmgmt_job_match_item('en', 'es');
    $job->addItem('test_source', 'test', 1);

    // Go to checkout form.
    $redirects = tmgmt_ui_job_checkout_multiple(array($job));
    $this->drupalGet(reset($redirects));

     // Check checkout form.
    $this->assertText('You can provide a label for this job in order to identify it easily later on.');
    $this->assertText('test_source:test:1');

    // The action should now default to reject.
    $this->drupalPostForm(NULL, array(), t('Submit to translator'));
    $this->assertText(t('This is not supported.'));
    $job = entity_load_unchanged('tmgmt_job', $job->id());
    $this->assertTrue($job->isRejected());
  }

  /**
   * Tests the tmgmt_ui_job_checkout() function.
   */
  function testCheckoutFunction() {
    $job = $this->createJob();

    // Check out a job when only the test translator is available. That one has
    // settings, so a checkout is necessary.
    $redirects = tmgmt_ui_job_checkout_multiple(array($job));
    $this->assertEqual($job->getSystemPath(), $redirects[0]);
    $this->assertTrue($job->isUnprocessed());
    $job->delete();

    // Hide settings on the test translator.
    $default_translator = tmgmt_translator_load('test_translator');
    $default_translator->settings = array(
      'expose_settings' => FALSE,
    );
    $default_translator->save();
    $job = $this->createJob();

    $redirects = tmgmt_ui_job_checkout_multiple(array($job));
    $this->assertFalse($redirects);
    $this->assertTrue($job->isActive());

    // A job without target language needs to be checked out.
    $job = $this->createJob('en', '');
    $redirects = tmgmt_ui_job_checkout_multiple(array($job));
    $this->assertEqual($job->getSystemPath(), $redirects[0]);
    $this->assertTrue($job->isUnprocessed());

    // Create a second file translator. This should check
    // out immediately.
    $job = $this->createJob();

    $second_translator = $this->createTranslator();
    $second_translator->settings = array(
      'expose_settings' => FALSE,
    );
    $second_translator->save();

    $redirects = tmgmt_ui_job_checkout_multiple(array($job));
    $this->assertEqual($job->getSystemPath(), $redirects[0]);
    $this->assertTrue($job->isUnprocessed());
  }

  /**
   * Tests of the job item review process.
   */
  public function testReview() {
    $job = $this->createJob();
    $job->translator = $this->default_translator->name;
    $job->settings = array();
    $job->save();
    $item = $job->addItem('test_source', 'test', 1);

    $data = tmgmt_flatten_data($item->getData());
    $keys = array_keys($data);
    $key = $keys[0];

    $this->drupalGet('admin/tmgmt/items/' . $item->id());
    // Testing the result of the
    // TMGMTTranslatorUIControllerInterface::reviewDataItemElement()
    $this->assertText(t('Testing output of review data item element @key from the testing translator.', array('@key' => $key)));

    // Test the review tool source textarea.
    $this->assertFieldByName('dummy|deep_nesting[source]', $data[$key]['#text']);

    // Save translation.
    $this->drupalPostForm(NULL, array('dummy|deep_nesting[translation]' => $data[$key]['#text'] . 'translated'), t('Save'));
    $this->drupalGet('admin/tmgmt/items/' . $item->id());
    // Check if translation has been saved.
    $this->assertFieldByName('dummy|deep_nesting[translation]', $data[$key]['#text'] . 'translated');
  }

  /**
   * Tests the UI of suggestions.
   */
  public function testSuggestions() {
    // Prepare a job and a node for testing.
    $job = $this->createJob();
    $job->addItem('test_source', 'test', 1);
    $job->addItem('test_source', 'test', 7);

    // Go to checkout form.
    $redirects = tmgmt_ui_job_checkout_multiple(array($job));
    $this->drupalGet(reset($redirects));

    $this->assertRaw('20');

    // Load all suggestions.
    $commands = $this->drupalPostAjaxForm(NULL, array(), array('op' => t('Load suggestions')));
    $this->assertEqual(count($commands), 3, 'Found 3 commands in AJAX-Request.');

    // Check each command for success.
    foreach ($commands as $command) {
      // No checks against the settings because we not use ajax to save.
      if ($command['command'] == 'settings') {
      }
      // Other commands must be from type "insert".
      else if ($command['command'] == 'insert') {
        // This should be the tableselect javascript file for the header.
        if (($command['method'] == 'prepend') && ($command['selector'] == 'head')) {
          $this->assertTrue(substr_count($command['data'], 'misc/tableselect.js'), 'Javascript for Tableselect found.');
        }
        // Check for the main content, the tableselect with the suggestions.
        else if (($command['method'] == NULL) && ($command['selector'] == NULL)) {
          $this->assertTrue(substr_count($command['data'], '</th>') == 5, 'Found five table header.');
          $this->assertTrue(substr_count($command['data'], '</tr>') == 3, 'Found two suggestion and one table header.');
          $this->assertTrue(substr_count($command['data'], '<td>11</td>') == 2, 'Found 10 words to translate per suggestion.');
          $this->assertTrue(substr_count($command['data'], 'value="Add suggestions"'), 'Found add button.');
        }
        // Nothing to prepend...
        else if (($command['method'] == 'prepend') && ($command['selector'] == NULL)) {
          $this->assertTrue(empty($command['data']), 'No content will be prepended.');
        }
        else {
          $this->fail('Unknown method/selector combination.');
        }
      }
      else {
        $this->fail('Unknown command.');
      }
    }

    $this->assertText('test_source:test_suggestion:1');
    $this->assertText('test_source:test_suggestion:7');
    $this->assertText('Test suggestion for test source 1');
    $this->assertText('Test suggestion for test source 7');

    // Add the second suggestion.
    $edit = array('suggestions_table[2]' => TRUE);
    $this->drupalPostForm(NULL, $edit, t('Add suggestions'));

    // Total word count should now include the added job.
    $this->assertRaw('31');
    // The suggestion for 7 was added, so there should now be a suggestion
    // or the suggestion instead.
    $this->assertNoText('Test suggestion for test source 7');
    $this->assertText('test_source:test_suggestion_suggestion:7');

  }

  /**
   * Test the process of aborting and resubmitting the job.
   */
  function testAbortJob() {
    $job = $this->createJob();
    $job->addItem('test_source', 'test', 1);
    $job->addItem('test_source', 'test', 2);
    $job->addItem('test_source', 'test', 3);

    $edit = array(
      'target_language' => 'es',
      'settings[action]' => 'translate',
    );
    $this->drupalPostForm('admin/tmgmt/jobs/' . $job->id(), $edit, t('Submit to translator'));

    // Abort job.
    $this->drupalPostForm('admin/tmgmt/jobs/' . $job->id(), array(), t('Abort job'));
    $this->drupalPostForm(NULL, array(), t('Confirm'));
    // Reload job and check its state.
    entity_get_controller('tmgmt_job')->resetCache();
    $job = tmgmt_job_load($job->id());
    $this->assertTrue($job->isAborted());
    foreach ($job->getItems() as $item) {
      $this->assertTrue($item->isAborted());
    }

    // Resubmit the job.
    $this->drupalPostForm('admin/tmgmt/jobs/' . $job->id(), array(), t('Resubmit'));
    $this->drupalPostForm(NULL, array(), t('Confirm'));
    // Test for the log message.
    $this->assertRaw(t('This job is a duplicate of the previously aborted job <a href="@url">#@id</a>',
      array('@url' => url('admin/tmgmt/jobs/' . $job->id()), '@id' => $job->id())));

    // Load the resubmitted job and check for its status and values.
    $url_parts = explode('/', $this->getUrl());
    $resubmitted_job = tmgmt_job_load(array_pop($url_parts));

    $this->assertTrue($resubmitted_job->isUnprocessed());
    $this->assertEqual($job->getTranslator(), $resubmitted_job->getTranslator());
    $this->assertEqual($job->getSourceLangcode(), $resubmitted_job->getSourceLangcode());
    $this->assertEqual($job->getTargetLangcode(), $resubmitted_job->getTargetLangcode());
    $this->assertEqual($job->get('settings')->getValue(), $resubmitted_job->get('settings')->getValue());

    // Test if job items were duplicated correctly.
    foreach ($job->getItems() as $item) {
      // We match job items based on "id #" string. This is not that straight
      // forward, but it works as the test source text is generated as follows:
      // Text for job item with type #type and id #id.
      $_items = $resubmitted_job->getItems(array('data' => array('value' => '%id ' . $item->getItemId() . '%', 'operator' => 'LIKE')));
      $_item = reset($_items);
      $this->assertNotEqual($_item->getJobId(), $item->getJobId());
      $this->assertEqual($_item->getPlugin(), $item->getPlugin());
      $this->assertEqual($_item->getItemId(), $item->getItemId());
      $this->assertEqual($_item->getItemType(), $item->getItemType());
      // Make sure counts have been recalculated.
      $this->assertTrue($_item->getWordCount() > 0);
      $this->assertTrue($_item->getCountPending() > 0);
      $this->assertEqual($_item->getCountTranslated(), 0);
      $this->assertEqual($_item->getCountAccepted(), 0);
      $this->assertEqual($_item->getCountReviewed(), 0);
    }

    // Navigate back to the aborted job and check for the log message.
    $this->drupalGet('admin/tmgmt/jobs/' . $job->id());
    $this->assertRaw(t('Job has been duplicated as a new job <a href="@url">#@id</a>.',
      array('@url' => url('admin/tmgmt/jobs/' . $resubmitted_job->id()), '@id' => $resubmitted_job->id())));

    $this->drupalGet('admin/tmgmt');
    $elements = $this->xpath('//table[contains(@class, @view)]//td[contains(., @text)]',
      array('@view' => 'views-table', '@text' => t('N/A')));
    $status = $elements[0];
    $this->assertEqual(trim((string)$status), t('N/A'));

  }

  /**
   * Test the cart functionality.
   */
  function testCart() {

    $this->addLanguage('fr');
    $job_items = array();
    // Create a few job items and add them to the cart.
    for ($i = 1; $i < 6; $i++) {
      $job_item = tmgmt_job_item_create('test_source', 'test', $i);
      $job_item->save();
      $job_items[$i] = $job_item;
    }

    $this->loginAsTranslator();
    foreach ($job_items as $job_item) {
      $this->drupalGet('tmgmt-add-to-cart/' . $job_item->id());
    }

    // Check if the items are displayed in the cart.
    $this->drupalGet('admin/tmgmt/cart');
    foreach ($job_items as $job_item) {
      $this->assertText($job_item->label());
    }

    // Test the remove items from cart functionality.
    $this->drupalPostForm(NULL, array('items[1]' => TRUE, 'items[4]' => TRUE), t('Remove selected'));
    $this->assertText($job_items[2]->label());
    $this->assertText($job_items[3]->label());
    $this->assertText($job_items[5]->label());
    $this->assertNoText($job_items[1]->label());
    $this->assertNoText($job_items[4]->label());
    $this->assertText(t('Job items were removed from the cart.'));

    // Test that removed job items from cart were deleted as well.
    $existing_items = tmgmt_job_item_load_multiple(NULL);
    $this->assertTrue(!isset($existing_items[$job_items[1]->id()]));
    $this->assertTrue(!isset($existing_items[$job_items[4]->id()]));


    $this->drupalPostForm(NULL, array(), t('Empty cart'));
    $this->assertNoText($job_items[2]->label());
    $this->assertNoText($job_items[3]->label());
    $this->assertNoText($job_items[5]->label());
    $this->assertText(t('All job items were removed from the cart.'));

    // No remaining job items.
    $existing_items = tmgmt_job_item_load_multiple(NULL);
    $this->assertTrue(empty($existing_items));

    $language_sequence = array('en', 'en', 'fr', 'fr', 'de', 'de');
    for ($i = 1; $i < 7; $i++) {
      $job_item = tmgmt_job_item_create('test_source', 'test', $i);
      $job_item->save();
      $job_items[$i] = $job_item;
      $languages[$job_items[$i]->id()] = $language_sequence[$i - 1];
    }
    \Drupal::state()->set('tmgmt.test_source_languages', $languages);
    foreach ($job_items as $job_item) {
      $this->drupalGet('tmgmt-add-to-cart/' . $job_item->id());
    }

    $this->drupalPostForm('admin/tmgmt/cart', array(
      'items[' . $job_items[1]->id() . ']' => TRUE,
      'items[' . $job_items[2]->id() . ']' => TRUE,
      'items[' . $job_items[3]->id() . ']' => TRUE,
      'items[' . $job_items[4]->id() . ']' => TRUE,
      'items[' . $job_items[5]->id() . ']' => TRUE,
      'target_language[]' => array('en', 'de'),
    ), t('Request translation'));

    $this->assertText(t('@count jobs need to be checked out.', array('@count' => 4)));

    // We should have four jobs with following language combinations:
    // [fr, fr] => [en]
    // [de] => [en]
    // [en, en] => [de]
    // [fr, fr] => [de]

    $jobs = entity_load_multiple_by_properties('tmgmt_job', array('source_language' => 'fr', 'target_language' => 'en'));
    $job = reset($jobs);
    $this->assertEqual(count($job->getItems()), 2);

    $jobs = entity_load_multiple_by_properties('tmgmt_job', array('source_language' => 'de', 'target_language' => 'en'));
    $job = reset($jobs);
    $this->assertEqual(count($job->getItems()), 1);

    $jobs = entity_load_multiple_by_properties('tmgmt_job', array('source_language' => 'en', 'target_language' => 'de'));
    $job = reset($jobs);
    $this->assertEqual(count($job->getItems()), 2);

    $jobs = entity_load_multiple_by_properties('tmgmt_job', array('source_language' => 'fr', 'target_language' => 'de'));
    $job = reset($jobs);
    $this->assertEqual(count($job->getItems()), 2);

    $this->drupalGet('admin/tmgmt/cart');
    // Both fr and one de items must be gone.
    $this->assertNoText($job_items[1]->label());
    $this->assertNoText($job_items[2]->label());
    $this->assertNoText($job_items[3]->label());
    $this->assertNoText($job_items[4]->label());
    $this->assertNoText($job_items[5]->label());
    // One de item is in the cart as it was not selected for checkout.
    $this->assertText($job_items[6]->label());
  }

}
