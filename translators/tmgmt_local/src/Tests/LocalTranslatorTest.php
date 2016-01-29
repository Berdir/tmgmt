<?php

/**
 * @file
 * Contains \Drupal\tmgmt_local\Tests\LocalTranslatorTest.
 */

namespace Drupal\tmgmt_local\Tests;

use Drupal\filter\Entity\FilterFormat;
use Drupal\tmgmt\Entity\Job;
use Drupal\tmgmt\Entity\JobItem;
use Drupal\tmgmt\Entity\Translator;
use Drupal\tmgmt\Tests\TMGMTTestBase;
use Drupal\tmgmt_local\Entity\LocalTask;

/**
 * Basic tests for the local translator.
 *
 * @group tmgmt
 */
class LocalTranslatorTest extends TMGMTTestBase {

  /**
   * Translator user.
   *
   * @var object
   */
  protected $assignee;

  protected $localTranslatorPermissions = array(
    'provide translation services',
  );

  protected $localManagerPermissions = [
    'administer translation tasks',
    'provide translation services',
  ];

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'user',
    'tmgmt',
    'tmgmt_language_combination',
    'tmgmt_local',
    'ckeditor',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->loginAsAdmin();
    $this->addLanguage('de');
    $this->drupalPlaceBlock('system_breadcrumb_block');
  }

  /**
   * Test assignee skills.
   */
  public function testAssigneeSkillsForTasks() {

    $this->addLanguage('fr');

    $assignee1 = $this->drupalCreateUser($this->localTranslatorPermissions);
    $this->drupalLogin($assignee1);
    $edit = array(
      'tmgmt_translation_skills[0][language_from]' => 'en',
      'tmgmt_translation_skills[0][language_to]' => 'de',
    );
    $this->drupalPostForm('user/' . $assignee1->id() . '/edit', $edit, t('Save'));

    $assignee2 = $this->drupalCreateUser($this->localTranslatorPermissions);
    $this->drupalLogin($assignee2);
    $edit = array(
      'tmgmt_translation_skills[0][language_from]' => 'en',
      'tmgmt_translation_skills[0][language_to]' => 'de',
    );
    $this->drupalPostForm('user/' . $assignee2->id() . '/edit', $edit, t('Save'));
    $edit = array(
      'tmgmt_translation_skills[1][language_from]' => 'de',
      'tmgmt_translation_skills[1][language_to]' => 'en',
    );
    $this->drupalPostForm('user/' . $assignee2->id() . '/edit', $edit, t('Save'));

    $assignee3 = $this->drupalCreateUser($this->localTranslatorPermissions);
    $this->drupalLogin($assignee3);
    $edit = array(
      'tmgmt_translation_skills[0][language_from]' => 'en',
      'tmgmt_translation_skills[0][language_to]' => 'de',
    );
    $this->drupalPostForm('user/' . $assignee3->id() . '/edit', $edit, t('Save'));
    $edit = array(
      'tmgmt_translation_skills[1][language_from]' => 'de',
      'tmgmt_translation_skills[1][language_to]' => 'en',
    );
    $this->drupalPostForm('user/' . $assignee3->id() . '/edit', $edit, t('Save'));
    $edit = array(
      'tmgmt_translation_skills[2][language_from]' => 'en',
      'tmgmt_translation_skills[2][language_to]' => 'fr',
    );
    $this->drupalPostForm('user/' . $assignee3->id() . '/edit', $edit, t('Save'));

    $job1 = $this->createJob('en', 'de');
    $job2 = $this->createJob('de', 'en');
    $job3 = $this->createJob('en', 'fr');

    $local_task1 = LocalTask::create(array(
      'uid' => $job1->getOwnerId(),
      'tjid' => $job1->id(),
      'title' => 'Task 1',
    ));
    $local_task1->save();

    $local_task2 = LocalTask::create(array(
      'uid' => $job2->getOwnerId(),
      'tjid' => $job2->id(),
      'title' => 'Task 2',
    ));
    $local_task2->save();

    $local_task3 = LocalTask::create(array(
      'uid' => $job3->getOwnerId(),
      'tjid' => $job3->id(),
      'title' => 'Task 3',
    ));
    $local_task3->save();

    // Test languages involved in tasks.
    $this->assertEqual(
      tmgmt_local_tasks_languages(array(
        $local_task1->id(),
        $local_task2->id(),
        $local_task3->id(),
      )),
      array(
        'en' => array('de', 'fr'),
        'de' => array('en'),
      )
    );

    // Test available translators for task en - de.
    $this->assertEqual(
      tmgmt_local_get_assignees_for_tasks(array($local_task1->id())),
      array(
        $assignee1->id() => $assignee1->getUsername(),
        $assignee2->id() => $assignee2->getUsername(),
        $assignee3->id() => $assignee3->getUsername(),
      )
    );

    // Test available translators for tasks en - de, de - en.
    $this->assertEqual(
      tmgmt_local_get_assignees_for_tasks(array($local_task1->id(), $local_task2->id())),
      array(
        $assignee2->id() => $assignee2->getUsername(),
        $assignee3->id() => $assignee3->getUsername(),
      )
    );

    // Test available translators for tasks en - de, de - en, en - fr.
    $this->assertEqual(
      tmgmt_local_get_assignees_for_tasks(array(
        $local_task1->id(),
        $local_task2->id(),
        $local_task3->id(),
      )),
      array(
        $assignee3->id() => $assignee3->getUsername(),
      )
    );
  }

  /**
   * Test the basic translation workflow.
   */
  public function testBasicWorkflow() {
    $translator = Translator::load('local');

    /** @var FilterFormat $basic_html_format */
    $basic_html_format = FilterFormat::create(array(
      'format' => 'basic_html',
      'name' => 'Basic HTML',
    ));
    $basic_html_format->save();

    // Create a job and request a local translation.
    $this->loginAsTranslator();
    $job = $this->createJob();
    $job->translator = $translator->id();
    $job->addItem('test_source', 'test', '1');
    \Drupal::state()->set('tmgmt.test_source_data', array(
      'dummy' => array(
        'deep_nesting' => array(
          '#text' => file_get_contents(drupal_get_path('module', 'tmgmt') . '/tests/testing_html/sample.html'),
          '#label' => 'Label for job item with type test and id 2.',
          '#translate' => TRUE,
          '#format' => 'basic_html',
        ),
      ),
    ));
    $job->addItem('test_source', 'test', '2');
    $job->save();

    // Make sure that the checkout page works as expected when there are no
    // roles.
    $this->drupalGet($job->toUrl());
    $this->assertText(t('@translator can not translate from @source to @target.', array(
      '@translator' => 'Local translator (auto created)',
      '@source' => 'English',
      '@target' => 'German',
    )));
    $xpath = $this->xpath('//*[@id="edit-translator"]')[0];
    $this->assertEqual($xpath->option[0], 'Local translator (auto created) (unsupported)');
    $this->assignee = $this->drupalCreateUser(
      array_merge($this->localTranslatorPermissions, [$basic_html_format->getPermissionName()])
    );

    // The same when there is a single role.
    $this->drupalGet($job->toUrl());
    $this->assertText(t('@translator can not translate from @source to @target.', array(
      '@translator' => 'Local translator (auto created)',
      '@source' => 'English',
      '@target' => 'German',
    )));

    // Create another local translator with the required abilities.
    $other_assignee_same = $this->drupalCreateUser($this->localTranslatorPermissions);

    // And test again with two roles but still no abilities.
    $this->drupalGet($job->toUrl());
    $this->assertText(t('@translator can not translate from @source to @target.', array(
      '@translator' => 'Local translator (auto created)',
      '@source' => 'English',
      '@target' => 'German',
    )));

    $this->drupalLogin($other_assignee_same);
    // Configure language abilities.
    $edit = array(
      'tmgmt_translation_skills[0][language_from]' => 'en',
      'tmgmt_translation_skills[0][language_to]' => 'de',
    );
    $this->drupalPostForm('user/' . $other_assignee_same->id() . '/edit', $edit, t('Save'));

    // Check that the user is not listed in the translator selection form.
    $this->loginAsAdmin();
    $this->drupalGet($job->toUrl());
    $xpath = $this->xpath('//*[@id="edit-translator"]')[0];
    $this->assertEqual($xpath->option[0], 'Local translator (auto created)');
    $this->assertText(t('Assign job to'));
    $this->assertText($other_assignee_same->getUsername());
    $this->assertNoText($this->assignee->getUsername());

    $this->drupalLogin($this->assignee);
    // Configure language abilities.
    $edit = array(
      'tmgmt_translation_skills[0][language_from]' => 'en',
      'tmgmt_translation_skills[0][language_to]' => 'de',
    );
    $this->drupalPostForm('user/' . $this->assignee->id() . '/edit', $edit, t('Save'));

    // Check that the translator is now listed.
    $this->loginAsAdmin();
    $this->drupalGet($job->toUrl());
    $this->assertText($this->assignee->getUsername());

    // Test assign task while submitting job.
    $job_comment = 'Dummy job comment';
    $edit = [
      'settings[translator]' => $this->assignee->id(),
      'settings[job_comment]' => $job_comment,
    ];
    $this->drupalPostForm(NULL, $edit, t('Submit to translator'));
    $this->drupalLogin($this->assignee);
    $this->drupalGet('translate/pending');
    $this->assertText(t('Task for @job', array('@job' => $job->label())));

    $this->loginAsAdmin($this->localManagerPermissions);
    $this->drupalGet('manage-translate/assigned');
    $this->assertNoLink(t('Delete'));
    $this->clickLink(t('Unassign'));
    $this->drupalPostForm(NULL, [], t('Unassign'));

    // Test for job comment in the job checkout info pane.
    $this->drupalGet($job->toUrl());
    $this->assertText($job_comment);

    $this->drupalLogin($this->assignee);

    // Create a second local translator with different language abilities,
    // make sure that he does not see the task.
    $other_translator = $this->drupalCreateUser($this->localTranslatorPermissions);
    $this->drupalLogin($other_translator);
    // Configure language abilities.
    $edit = array(
      'tmgmt_translation_skills[0][language_from]' => 'de',
      'tmgmt_translation_skills[0][language_to]' => 'en',
    );
    $this->drupalPostForm('user/' . $other_translator->id() . '/edit', $edit, t('Save'));
    $this->drupalGet('translate');
    $this->assertNoText(t('Task for @job', array('@job' => $job->label())));

    $this->drupalLogin($this->assignee);

    // Check the translate overview.
    $this->drupalGet('translate');
    $this->assertText(t('Task for @job', array('@job' => $job->label())));
    // @todo: Fails, encoding problem?
    // $this->assertText(t('@from => @to', array('@from' => 'en', '@to' => 'de')));

    // Test LocalTaskForm.
    $this->clickLink('View');
    $this->assertText('Unassigned');
    $xpath = $this->xpath('//*[@id="edit-status"]');
    $this->assertTrue(empty($xpath));

    $this->loginAsAdmin($this->localManagerPermissions);
    $this->drupalGet('translate');
    $this->clickLink('View');
    $xpath = $this->xpath('//*[@id="edit-tuid"]');
    $this->assertFalse(empty($xpath));
    $edit = array(
      'tuid' => $this->assignee->id(),
    );
    $this->drupalPostForm(NULL, $edit, t('Save task'));
    $this->assertText(t('Assigned to user @assignee.', ['@assignee' => $this->assignee->getDisplayName()]));

    $this->drupalGet('manage-translate/assigned');
    $this->clickLink('View');
    $edit = array(
      'tuid' => 0,
    );
    $this->drupalPostForm(NULL, $edit, t('Save task'));

    $this->drupalLogin($this->assignee);
    $this->drupalGet('translate');

    // Assign to action not working yet.
    $edit = array(
      'tmgmt_local_task_bulk_form[0]' => TRUE,
      'action' => 'tmgmt_local_task_assign_to_me',
    );
    $this->drupalPostForm(NULL, $edit, t('Apply'));
    $this->assertText(t('Assign to me was applied to 1 item.'));

    // Unassign again.
    $edit = array(
      'tmgmt_local_task_bulk_form[0]' => TRUE,
      'action' => 'tmgmt_local_task_unassign_multiple',
    );
    $this->drupalPostForm(NULL, $edit, t('Apply'));
    $this->assertText(t('Unassign was applied to 1 item.'));

    // Now test the assign link.
    // @todo Action should not redirect to mine.
    $this->drupalGet('translate');
    $this->clickLink(t('Assign to me'));

    // @todo Not working the link, delete that when works again.
    $this->drupalGet('translate');
    $edit = array(
      'tmgmt_local_task_bulk_form[0]' => TRUE,
      'action' => 'tmgmt_local_task_assign_to_me',
    );
    $this->drupalPostForm(NULL, $edit, t('Apply'));
    $this->assertText(t('Assign to me was applied to 1 item.'));

    // Log in with the translator with the same abilities, make sure that he
    // does not see the assigned task.
    $this->drupalLogin($other_assignee_same);
    $this->drupalGet('translate');
    $this->assertNoText(t('Task for @job', array('@job' => $job->label())));

    $this->drupalLogin($this->assignee);

    // Translate the task.
    $this->drupalGet('translate/pending');
    $this->clickLink(t('View'));

    // Assert created local task and task items.
    $this->assertTrue(preg_match('|translate/(\d+)|', $this->getUrl(), $matches), 'Task found');
    /** @var \Drupal\tmgmt_local\Entity\LocalTask $task */
    $task = entity_load('tmgmt_local_task', $matches[1], TRUE);
    $this->assertTrue($task->isPending());
    $this->assertEqual($task->getCountCompleted(), 0);
    $this->assertEqual($task->getCountTranslated(), 0);
    $this->assertEqual($task->getCountUntranslated(), 2);

    $items = $task->getItems();
    /** @var \Drupal\tmgmt_local\Entity\LocalTaskItem $first_task_item */
    $first_task_item = reset($items);
    $this->assertTrue($first_task_item->isPending());
    $this->assertEqual($first_task_item->getCountCompleted(), 0);
    $this->assertEqual($first_task_item->getCountTranslated(), 0);
    $this->assertEqual($first_task_item->getCountUntranslated(), 1);
    $this->asserTaskItemProgress(1, '1/0/0');
    $this->asserTaskItemProgress(2, '1/0/0');
    $this->assertRaw('Untranslated: 1, translated: 0, completed: 0.');

    $this->assertText('test_source:test:1');
    $this->assertText('test_source:test:2');
    $this->assertRaw('tmgmt/icons/ready.svg" title="Untranslated"');

    // Translate the first item.
    $this->clickLink(t('Translate'));

    // Assert the breadcrumb.
    $this->assertLink(t('Home'));
    $this->assertLink(t('Local Tasks'));
    $this->assertText(t('Task for @job', array('@job' => $job->label())));

    $this->assertText(t('Dummy'));
    // Check if Save as completed button is displayed.
    $elements = $this->xpath('//*[@id="edit-save-as-completed"]');
    $this->assertTrue(!empty($elements), "'Save as completed' button appears.");

    // Job comment is present in the translate tool as well.
    $this->assertText($job_comment);
    $this->assertText('test_source:test:1');

    // Try to complete a translation when translations are missing.
    $edit = array(
      'dummy|deep_nesting[translation]' => '',
    );
    $this->drupalPostForm(NULL, $edit, t('Save as completed'));
    $this->assertText(t('Missing translation.'));

    $edit = array(
      'dummy|deep_nesting[translation]' => $translation1 = 'German translation of source 1',
    );
    $this->drupalPostForm(NULL, $edit, t('Save as completed'));
    $this->assertRaw('tmgmt/icons/gray-check.svg" title="Translated"');
    $this->assertText('The translation for ' . $first_task_item->label() . ' has been saved as completed.');

    // Review and accept the first item.
    \Drupal::entityTypeManager()->getStorage('tmgmt_job_item')->resetCache();
    \Drupal::entityTypeManager()->getStorage('tmgmt_local_task')->resetCache();
    \Drupal::entityTypeManager()->getStorage('tmgmt_local_task_item')->resetCache();
    drupal_static_reset('tmgmt_local_task_statistics_load');
    /** @var \Drupal\tmgmt\JobItemInterface $item1 */
    $item1 = JobItem::load(1);
    $item1->acceptTranslation();

    // The first item should be accepted now, the second still in progress.
    $this->drupalGet('translate/1');
    $this->assertRaw('icons/73b355/check.svg" title="Completed"');
    $this->assertRaw('tmgmt/icons/ready.svg" title="Untranslated"');
    // Checking if the 'Save as completed' button is displayed.
    $this->drupalGet('translate/items/1');
    $elements = $this->xpath('//*[@id="edit-save-as-completed"]');
    $this->assertTrue(empty($elements), "'Save as completed' button does not appear.");
    $this->clickLink($task->label());

    /** @var \Drupal\tmgmt_local\Entity\LocalTask $task */
    $task = entity_load('tmgmt_local_task', $task->id(), TRUE);
    $this->assertTrue($task->isPending());
    $this->assertEqual($task->getCountCompleted(), 1);
    $this->assertEqual($task->getCountTranslated(), 0);
    $this->assertEqual($task->getCountUntranslated(), 1);
    /** @var \Drupal\tmgmt_local\Entity\LocalTaskItem $second_task_item */
    list($first_task_item, $second_task_item) = array_values($task->getItems());
    $this->assertTrue($first_task_item->isClosed());
    $this->assertEqual($first_task_item->getCountCompleted(), 1);
    $this->assertEqual($first_task_item->getCountTranslated(), 0);
    $this->assertEqual($first_task_item->getCountUntranslated(), 0);
    $this->assertTrue($second_task_item->isPending());
    $this->assertEqual($second_task_item->getCountCompleted(), 0);
    $this->assertEqual($second_task_item->getCountTranslated(), 0);
    $this->assertEqual($second_task_item->getCountUntranslated(), 1);
    $this->asserTaskItemProgress(1, '0/0/1');
    $this->asserTaskItemProgress(2, '1/0/0');

    // Translate the second item but do not mark as translated it yet.
    $this->clickLink(t('Translate'));
    $xpath = $this->xpath('//*[@id="edit-dummydeep-nesting-translation-format-guidelines"]/div')[0];
    $this->assertEqual($xpath[0]->h4[0], t('Basic HTML'));
    $edit = array(
      'dummy|deep_nesting[translation][value]' => $translation2 = 'German translation of source 2',
    );
    $this->drupalPostForm(NULL, $edit, t('Save'));
    $this->assertText('The translation for ' . $second_task_item->label() . ' has been saved.');
    // The first item is still completed, the second still untranslated.
    $this->assertRaw('icons/73b355/check.svg" title="Completed"');
    $this->assertRaw('tmgmt/icons/ready.svg" title="Untranslated"');

    \Drupal::entityTypeManager()->getStorage('tmgmt_local_task')->resetCache();
    \Drupal::entityTypeManager()->getStorage('tmgmt_local_task_item')->resetCache();
    drupal_static_reset('tmgmt_local_task_statistics_load');
    /** @var \Drupal\tmgmt_local\Entity\LocalTask $task */
    $task = entity_load('tmgmt_local_task', $task->id(), TRUE);
    $this->assertTrue($task->isPending());
    $this->assertEqual($task->getCountCompleted(), 1);
    $this->assertEqual($task->getCountTranslated(), 0);
    $this->assertEqual($task->getCountUntranslated(), 1);
    list($first_task_item, $second_task_item) = array_values($task->getItems());
    $this->assertTrue($first_task_item->isClosed());
    $this->assertEqual($first_task_item->getCountCompleted(), 1);
    $this->assertEqual($first_task_item->getCountTranslated(), 0);
    $this->assertEqual($first_task_item->getCountUntranslated(), 0);
    $this->assertTrue($second_task_item->isPending());
    $this->assertEqual($second_task_item->getCountCompleted(), 0);
    $this->assertEqual($second_task_item->getCountTranslated(), 0);
    $this->assertEqual($second_task_item->getCountUntranslated(), 1);
    $this->asserTaskItemProgress(1, '0/0/1');
    $this->asserTaskItemProgress(2, '1/0/0');

    // Mark the data item as translated but don't save the task item as
    // completed.
    $this->clickLink(t('Translate'));
    $this->drupalPostAjaxForm(NULL, [], 'finish-dummy|deep_nesting');
    $this->assertRaw('name="reject-dummy|deep_nesting"', "'✗' button appears.");

    \Drupal::entityTypeManager()->getStorage('tmgmt_local_task')->resetCache();
    \Drupal::entityTypeManager()->getStorage('tmgmt_local_task_item')->resetCache();
    drupal_static_reset('tmgmt_local_task_statistics_load');
    /** @var \Drupal\tmgmt_local\Entity\LocalTask $task */
    $task = entity_load('tmgmt_local_task', $task->id(), TRUE);
    $this->assertTrue($task->isPending());
    $this->assertEqual($task->getCountCompleted(), 1);
    $this->assertEqual($task->getCountTranslated(), 1);
    $this->assertEqual($task->getCountUntranslated(), 0);
    list($first_task_item, $second_task_item) = array_values($task->getItems());
    $this->assertTrue($first_task_item->isClosed());
    $this->assertEqual($first_task_item->getCountCompleted(), 1);
    $this->assertEqual($first_task_item->getCountTranslated(), 0);
    $this->assertEqual($first_task_item->getCountUntranslated(), 0);
    $this->assertTrue($second_task_item->isPending());
    $this->assertEqual($second_task_item->getCountCompleted(), 0);
    $this->assertEqual($second_task_item->getCountTranslated(), 1);
    $this->assertEqual($second_task_item->getCountUntranslated(), 0);
    $this->drupalPostForm(NULL, [], t('Save'));
    $this->asserTaskItemProgress(1, '0/0/1');
    $this->asserTaskItemProgress(2, '0/1/0');

    // Check the job data.
    \Drupal::entityTypeManager()->getStorage('tmgmt_job')->resetCache();
    \Drupal::entityTypeManager()->getStorage('tmgmt_job_item')->resetCache();
    $job = Job::load($job->id());
    list($item1, $item2) = array_values($job->getItems());
    // The text in the first item should be available for review, the
    // translation of the second item not.
    $this->assertEqual($item1->getData(array('dummy', 'deep_nesting', '#translation', '#text')), $translation1);
    $this->assertEqual($item2->getData(array('dummy', 'deep_nesting', '#translation', '#text')), '');

    // Check the overview page, the task should still show in progress.
    $this->drupalGet('translate');
    $this->assertText(t('Pending'));

    // Mark the second item as completed now.
    $this->clickLink(t('View'));
    $this->clickLink(t('Translate'));
    $this->drupalPostForm(NULL, array(), t('Save as completed'));
    $this->assertText('The translation for ' . $second_task_item->label() . ' has been saved as completed.');
    $this->clickLink('View');
    $this->asserTaskItemProgress(1, '0/0/1');
    $this->asserTaskItemProgress(2, '0/0/1');

    // Review and accept the second item.
    \Drupal::entityTypeManager()->getStorage('tmgmt_job_item')->resetCache();
    \Drupal::entityTypeManager()->getStorage('tmgmt_local_task')->resetCache();
    \Drupal::entityTypeManager()->getStorage('tmgmt_local_task_item')->resetCache();
    drupal_static_reset('tmgmt_local_task_statistics_load');
    $item1 = JobItem::load(2);
    $item1->acceptTranslation();

    // Refresh the page.
    $this->drupalGet('translate');

    $task = tmgmt_local_task_load($task->id());
    $this->assertTrue($task->isClosed());
    $this->assertEqual($task->getCountCompleted(), 2);
    $this->assertEqual($task->getCountTranslated(), 0);
    $this->assertEqual($task->getCountUntranslated(), 0);
    list($first_task_item, $second_task_item) = array_values($task->getItems());
    $this->assertTrue($first_task_item->isClosed());
    $this->assertEqual($first_task_item->getCountCompleted(), 1);
    $this->assertEqual($first_task_item->getCountTranslated(), 0);
    $this->assertEqual($first_task_item->getCountUntranslated(), 0);
    $this->assertTrue($second_task_item->isClosed());
    $this->assertEqual($second_task_item->getCountCompleted(), 1);
    $this->assertEqual($second_task_item->getCountTranslated(), 0);
    $this->assertEqual($second_task_item->getCountUntranslated(), 0);

    // We should have been redirect back to the overview, the task should be
    // completed now.
    $this->assertNoText($task->getJob()->label());
    $this->clickLink(t('Closed'));
    $this->assertText($task->getJob()->label());
    $this->assertText(t('Completed'));

    \Drupal::entityTypeManager()->getStorage('tmgmt_job')->resetCache();
    \Drupal::entityTypeManager()->getStorage('tmgmt_job_item')->resetCache();
    $job = Job::load($job->id());
    list($item1, $item2) = array_values($job->getItems());
    // Job was accepted and finished automatically due to the default approve
    // setting.
    $this->assertTrue($job->isFinished());
    $this->assertEqual($item1->getData(array(
      'dummy',
      'deep_nesting',
      '#translation',
      '#text',
    )), $translation1);
    $this->assertEqual($item2->getData(array(
      'dummy',
      'deep_nesting',
      '#translation',
      '#text',
    )), $translation2);

    // Delete the job, make sure that the corresponding task and task items were
    // deleted.
    $job->delete();
    $this->assertFalse(tmgmt_local_task_item_load($task->id()));
    $this->assertFalse($task->getItems());
  }

  /**
   * Test the allow all setting.
   */
  public function testAllowAll() {
    /** @var Translator $translator */
    $translator = Translator::load('local');

    // Create a job and request a local translation.
    $this->loginAsTranslator();
    $job = $this->createJob();
    $job->translator = $translator->id();
    $job->addItem('test_source', 'test', '1');
    $job->addItem('test_source', 'test', '2');

    $this->assertFalse($job->requestTranslation(), 'Translation request was denied.');

    // Now enable the setting.
    $translator->setSetting('allow_all', TRUE);
    $translator->save();
    /** @var Job $job */
    $job = entity_load('tmgmt_job', $job->id(), TRUE);
    $job->translator = $translator->id();

    $this->assertIdentical(NULL, $job->requestTranslation(), 'Translation request was successfull');
    $this->assertTrue($job->isActive());
  }

  public function testAbilitiesAPI() {

    $this->addLanguage('fr');
    $this->addLanguage('ru');
    $this->addLanguage('it');

    $all_assignees = array();

    $assignee1 = $this->drupalCreateUser($this->localTranslatorPermissions);
    $all_assignees[$assignee1->id()] = $assignee1->getUsername();
    $this->drupalLogin($assignee1);
    $edit = array(
      'tmgmt_translation_skills[0][language_from]' => 'en',
      'tmgmt_translation_skills[0][language_to]' => 'de',
    );
    $this->drupalPostForm('user/' . $assignee1->id() . '/edit', $edit, t('Save'));

    $assignee2 = $this->drupalCreateUser($this->localTranslatorPermissions);
    $all_assignees[$assignee2->id()] = $assignee2->getUsername();
    $this->drupalLogin($assignee2);
    $edit = array(
      'tmgmt_translation_skills[0][language_from]' => 'en',
      'tmgmt_translation_skills[0][language_to]' => 'ru',
    );
    $this->drupalPostForm('user/' . $assignee2->id() . '/edit', $edit, t('Save'));
    $edit = array(
      'tmgmt_translation_skills[1][language_from]' => 'en',
      'tmgmt_translation_skills[1][language_to]' => 'fr',
    );
    $this->drupalPostForm('user/' . $assignee2->id() . '/edit', $edit, t('Save'));
    $edit = array(
      'tmgmt_translation_skills[2][language_from]' => 'fr',
      'tmgmt_translation_skills[2][language_to]' => 'it',
    );
    $this->drupalPostForm('user/' . $assignee2->id() . '/edit', $edit, t('Save'));

    $assignee3 = $this->drupalCreateUser($this->localTranslatorPermissions);
    $all_assignees[$assignee3->id()] = $assignee3->getUsername();
    $this->drupalLogin($assignee3);
    $edit = array(
      'tmgmt_translation_skills[0][language_from]' => 'fr',
      'tmgmt_translation_skills[0][language_to]' => 'ru',
    );
    $this->drupalPostForm('user/' . $assignee3->id() . '/edit', $edit, t('Save'));
    $edit = array(
      'tmgmt_translation_skills[1][language_from]' => 'it',
      'tmgmt_translation_skills[1][language_to]' => 'en',
    );
    $this->drupalPostForm('user/' . $assignee3->id() . '/edit', $edit, t('Save'));

    // Test target languages.
    $target_languages = tmgmt_local_supported_target_languages('fr');
    $this->assertTrue(isset($target_languages['it']));
    $this->assertTrue(isset($target_languages['ru']));
    $target_languages = tmgmt_local_supported_target_languages('en');
    $this->assertTrue(isset($target_languages['fr']));
    $this->assertTrue(isset($target_languages['ru']));

    // Test language pairs.
    $this->assertEqual(tmgmt_local_supported_language_pairs(), array (
      'en__de' =>
        array(
          'source_language' => 'en',
          'target_language' => 'de',
        ),
      'en__ru' =>
        array(
          'source_language' => 'en',
          'target_language' => 'ru',
        ),
      'en__fr' =>
        array(
          'source_language' => 'en',
          'target_language' => 'fr',
        ),
      'fr__it' =>
        array(
          'source_language' => 'fr',
          'target_language' => 'it',
        ),
      'fr__ru' =>
        array(
          'source_language' => 'fr',
          'target_language' => 'ru',
        ),
      'it__en' =>
        array(
          'source_language' => 'it',
          'target_language' => 'en',
        ),
    ));
    $this->assertEqual(tmgmt_local_supported_language_pairs('fr', array($assignee2->id())), array(
      'fr__it' =>
        array(
          'source_language' => 'fr',
          'target_language' => 'it',
        ),
    ));

    // Test if we got all translators.
    $assignees = tmgmt_local_assignees();
    foreach ($all_assignees as $uid => $name) {
      if (!isset($assignees[$uid])) {
        $this->fail('Expected translator not present');
      }
      if (!in_array($name, $all_assignees)) {
        $this->fail('Expected translator name not present');
      }
    }

    // Only translator2 has such abilities.
    $assignees = tmgmt_local_assignees('en', array('ru', 'fr'));
    $this->assertTrue(isset($assignees[$assignee2->id()]));
  }

  /**
   * Test permissions for the tmgmt_local VBO actions.
   */
  public function testVBOPermissions() {
    $translator = Translator::load('local');
    $job = $this->createJob();
    $job->translator = $translator->id();
    $job->settings->job_comment = $job_comment = 'Dummy job comment';
    $job->addItem('test_source', 'test', '1');
    $job->addItem('test_source', 'test', '2');

    // Create another local translator with the required abilities.
    $assignee = $this->loginAsTranslator($this->localTranslatorPermissions);
    // Configure language abilities.
    $edit = array(
      'tmgmt_translation_skills[0][language_from]' => 'en',
      'tmgmt_translation_skills[0][language_to]' => 'de',
    );
    $this->drupalPostForm('user/' . $assignee->id() . '/edit', $edit, t('Save'));

    $job->requestTranslation();

    $this->drupalGet('manage-translate');
    $this->assertResponse(403);
    $this->drupalGet('translate');
    $edit = array(
      'tmgmt_local_task_bulk_form[0]' => TRUE,
      'action' => 'tmgmt_local_task_assign_to_me',
    );
    $this->drupalPostForm(NULL, $edit, t('Apply'));
    $this->assertText(t('Assign to me was applied to 1 item.'));
    $edit = array(
      'tmgmt_local_task_bulk_form[0]' => TRUE,
      'action' => 'tmgmt_local_task_unassign_multiple',
    );
    $this->drupalPostForm(NULL, $edit, t('Apply'));
    $this->assertText(t('Unassign was applied to 1 item.'));

    // Login as admin and check VBO submit actions are present.
    $this->loginAsAdmin($this->localManagerPermissions);
    $this->drupalGet('manage-translate');
    $edit = array(
      'tmgmt_local_task_bulk_form[0]' => TRUE,
      'action' => 'tmgmt_local_task_assign_multiple',
    );
    $this->drupalPostForm(NULL, $edit, t('Apply'));
    $edit = array(
      'tuid' => $assignee->id(),
    );
    $this->drupalPostForm(NULL, $edit, t('Assign tasks'));
    $this->assertText(t('Assigned 1 to user @assignee.', ['@assignee' => $assignee->getAccountName()]));
  }

  /**
   * Asserts job item language codes.
   *
   * @param int $row
   *   The row of the item you want to check.
   * @param string $progress
   *   The progress text.
   */
  private function asserTaskItemProgress($row, $progress) {
    $result = $this->xpath('//*[@id="edit-items"]/div/div/table/tbody/tr[' . $row . ']/td[2]')[0];
    $this->assertEqual($result->span, $progress);
  }

}
