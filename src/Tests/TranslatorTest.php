<?php

/*
 * @file
 * Contains Drupal\tmgmt\Tests\TranslatorTest.
 */

namespace Drupal\tmgmt\Tests;
use Drupal\tmgmt\Entity\Job;

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
    $job->setState(Job::STATE_ACTIVE);

    // Try to delete the translator, should fail because of active job.
    $delete_url = 'tmgmt_translator/' . $translator->id() . '/delete';
    $this->drupalGet($delete_url);
    $this->assertLink(t('Cancel'));
    $this->drupalPostForm(NULL, array(), 'Delete');

    $this->assertText(t('This translator cannot be deleted as long as there are active jobs using it.'));

    // Change job state, delete again.
    $job->setState(Job::STATE_FINISHED);
    $this->drupalPostForm(NULL, array(), 'Delete');
    $this->assertText(t('Add translator'));
    $this->assertText(t('@label has been deleted.', array('@label' => $translator->label())));

    // Testing the translators form with no installed translator plugins.
    // Uninstall the test module (which provides a translator).
    \Drupal::service('module_installer')->uninstall(array('tmgmt_test'), FALSE);

    // Get the overview.
    $this->drupalGet('admin/config/regional/tmgmt_translator');
    $this->assertNoText(t('Add translator'));
    $this->assertText(t('There are no translator plugins available. Please install a translator plugin.'));
  }

}
