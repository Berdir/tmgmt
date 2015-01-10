<?php

/*
 * @file
 * Contains Drupal\tmgmt\Tests\TranslatorTest.
 */

namespace Drupal\tmgmt\Tests;

/**
 * Verifies functionality of translator handling
 *
 * @group tmgmt
 */
class TranslatorTest extends TMGMTTestBase {

  /**
   * {@inheritdoc}
   */
  function setUp() {
    parent::setUp();

    // Login as admin to be able to set environment variables.
    $this->loginAsAdmin();
    $this->addLanguage('de');
    $this->addLanguage('es');
    $this->addLanguage('el');

    // Login as translation administrator to run these tests.
    $this->loginAsTranslator(array(
      'administer tmgmt',
    ), TRUE);
  }


  /**
   * Tests creating and deleting a translator.
   */
  function testTranslatorHandling() {
    // Create a translator for later deletion.
    $translator = parent::createTranslator();
    // Does the translator exist in the listing?
    $this->drupalGet('admin/config/regional/tmgmt_translator');
    $this->assertText($translator->label());

    // Create job, attach to the translator and activate.
    $job = $this->createJob();
    $job->translator = $translator;
    $job->settings = array();
    $job->save();
    $job->setState(TMGMT_JOB_STATE_ACTIVE);

    // Try to delete the translator, should fail because of active job.
    $delete_url = 'tmgmt_translator/' . $translator->id() . '/delete';
    $this->drupalGet($delete_url);
    $this->assertLink(t('Cancel'));
    $this->drupalPostForm(NULL, array(), 'Delete');

    $this->assertText(t('This translator cannot be deleted as long as there are active jobs using it.'));

    // Change job state, delete again.
    $job->setState(TMGMT_JOB_STATE_FINISHED);
    $this->drupalPostForm(NULL, array(), 'Delete');
    $this->assertText(t('Add translator'));
    $this->assertNoText($translator->label());
  }
}
