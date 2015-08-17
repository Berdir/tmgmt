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
    $this->assertEqual(count($this->xpath('//tbody/tr')), 2);

    // Create job, attach to the translator and activate.
    $job = $this->createJob();
    $job->translator = $translator;
    $job->settings = array();
    $job->save();
    $job->setState(Job::STATE_ACTIVE);
    $item = $job->addItem('test_source', 'test', 1);

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
    // Check if the list of translators has 1 row.
    $this->assertEqual(count($this->xpath('//tbody/tr')), 1);
    $this->assertText(t('@label has been deleted.', array('@label' => $translator->label())));

    // Assert that the job works and there is a text saying that the translator
    // is missing.
    $this->drupalGet('admin/tmgmt/jobs/' . $job->id());
    $this->assertText(t('The job has no translator assigned.'));

    // Assert that also the job items are working.
    $this->drupalGet('admin/tmgmt/items/' . $item->id());
    $this->assertText(t('Missing translator'));

    // Testing the translators form with no installed translator plugins.
    // Uninstall the test module (which provides a translator).
    \Drupal::service('module_installer')->uninstall(array('tmgmt_test'), FALSE);

    // Assert that job deletion works correctly.
    \Drupal::service('module_installer')->install(array('tmgmt_file'), FALSE);
    $this->drupalPostForm('/admin/tmgmt/jobs/' . $job->id() . '/delete', [], t('Delete'));
    $this->assertResponse(200);
    $this->assertText(t('The translation job @value has been deleted.', array('@value' => $job->label())));
    \Drupal::service('module_installer')->uninstall(array('tmgmt_file'), FALSE);

    // Get the overview.
    $this->drupalGet('admin/config/regional/tmgmt_translator');
    $this->assertNoText(t('Add translator'));
    $this->assertText(t('There are no translator plugins available. Please install a translator plugin.'));
  }

  /**
   * Tests remote languages mappings support in the tmgmt core.
   */
  protected function testRemoteLanguagesMappings() {
    $mappings = $this->default_translator->getRemoteLanguagesMappings();
    $this->assertEqual($mappings, array(
      'en' => 'en-us',
      'de' => 'de-ch',
      'el' => 'el',
      'es' => 'es',
    ));

    $this->assertEqual($this->default_translator->mapToRemoteLanguage('en'), 'en-us');
    $this->assertEqual($this->default_translator->mapToRemoteLanguage('de'), 'de-ch');

    $this->default_translator->setSetting(['remote_languages_mappings', 'de'], 'de-de');
    $this->default_translator->setSetting(['remote_languages_mappings', 'en'], 'en-uk');
    $this->default_translator->save();

    $this->assertEqual($this->default_translator->mapToRemoteLanguage('en'), 'en-uk');
    $this->assertEqual($this->default_translator->mapToRemoteLanguage('de'), 'de-de');

    // Test the fallback.
    $this->container->get('state')->set('tmgmt_test_translator_map_languages', FALSE);
    $this->container->get('plugin.manager.tmgmt.translator')->clearCachedDefinitions();

    $this->assertEqual($this->default_translator->mapToRemoteLanguage('en'), 'en');
    $this->assertEqual($this->default_translator->mapToRemoteLanguage('de'), 'de');
  }

}
