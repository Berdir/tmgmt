<?php

/**
 * @file
 * Contains Drupal\tmgmt_test\Plugin\tmgmt\Translator\TestSource.
 */

namespace Drupal\tmgmt_test\Plugin\tmgmt\Translator;

use Drupal\Core\Form\FormStateInterface;
use Drupal\tmgmt\Translator\AvailableResult;
use Drupal\tmgmt\Translator\TranslatableResult;
use Drupal\tmgmt\JobInterface;
use Drupal\tmgmt\JobItemInterface;
use Drupal\tmgmt\TranslatorInterface;
use Drupal\tmgmt\TranslatorPluginBase;
use Drupal\tmgmt\TranslatorRejectDataInterface;

/**
 * Test source plugin implementation.
 *
 * @TranslatorPlugin(
 *   id = "test_translator",
 *   label = @Translation("Test translator"),
 *   description = @Translation("Simple translator for testing purposes."),
 *   default_settings = {
 *     "expose_settings" = TRUE,
 *   },
 *   ui = "Drupal\tmgmt_test\TestTranslatorUi"
 * )
 */
class TestTranslator extends TranslatorPluginBase implements TranslatorRejectDataInterface {

 /**
   * {@inheritdoc}
   */
  protected $escapeStart = '[[[';

  /**
   * {@inheritdoc}
   */
  protected $escapeEnd = ']]]';

  /**
   * {@inheritdoc}
   */
  public function getDefaultRemoteLanguagesMappings() {
    return array(
      'en' => 'en-us',
      'de' => 'de-ch',
    );
  }

  /**
   * {@inheritdoc}
   */
  public function hasCheckoutSettings(JobInterface $job) {
    return $job->getTranslator()->getSetting('expose_settings');
  }

  /**
   * {@inheritdoc}
   */
  function requestTranslation(JobInterface $job) {
    // Add a debug message.
    $job->addMessage('Test translator called.', array(), 'debug');

    // Do something different based on the action, if defined.
    $action =$job->getSetting('action') ?: '';
    switch ($action) {
      case 'submit':
        $job->submitted('Test submit.');
        break;

      case 'reject':
        $job->rejected('This is not supported.');
        break;

      case 'fail':
        // Target not reachable.
        $job->addMessage('Service not reachable.', array(), 'error');
        break;

      case 'translate':
      default:
        // The dummy translation prefixes strings with the target language.
        $data = array_filter(\Drupal::service('tmgmt.data')->flatten($job->getData()), array(\Drupal::service('tmgmt.data'), 'filterData'));
        $tdata = array();
        foreach ($data as $key => $value) {
          if ($job->getTargetLangcode() != $job->getRemoteTargetLanguage()) {
            $tdata[$key]['#text'] = $job->getTargetLangcode() . '(' . $job->getRemoteTargetLanguage() . '): ' . $value['#text'];
          }
          else {
            $tdata[$key]['#text'] = $job->getTargetLangcode() . ': ' . $value['#text'];
          }
        }
        $job->submitted('Test translation created.');
        $job->addTranslatedData(\Drupal::service('tmgmt.data')->unflatten($tdata));
        break;
    }
  }

  /**
   * {@inheritdoc}
   */
  function checkTranslatable(TranslatorInterface $translator, JobInterface $job) {
    if ($job->getSetting('action') == 'not_translatable') {
      return TranslatableResult::no(t('@translator can not translate from @source to @target.', array(
        '@translator' => $job->getTranslator()->label(),
        '@source' => $job->getSourceLanguage()->getName(),
        '@target' => $job->getTargetLanguage()->getName()
      )));
    }
    return parent::checkTranslatable($translator, $job);
  }
  /**
   * {@inheritdoc}
   */
  function checkAvailable(TranslatorInterface $translator) {
    if ($translator->getSetting('action') == 'not_available') {
      return AvailableResult::no(t('@translator is not available. Make sure it is properly <a href=:configured>configured</a>.', [
        '@translator' => $translator->label(),
        ':configured' => $translator->url()
      ]));
    }
    return parent::checkAvailable($translator);
  }

  /**
   * {@inheritdoc}
   */
  public function getSupportedTargetLanguages(TranslatorInterface $translator, $source_language) {
    $languages = array('en', 'de', 'es', 'it', 'pt', 'zh-hans', 'gsw-berne');
    $languages = array_combine($languages, $languages);
    unset($languages[$source_language]);
    return $languages;
  }

  /**
   * {@inheritdoc}
   */
  public function rejectDataItem(JobItemInterface $job_item, array $key, array $values = NULL) {
    $key = '[' . implode('][', $key) . ']';
    $job_item->addMessage('Rejected data item @key for job item @item in job @job.', array('@key' => $key, '@item' => $job_item->id(), '@job' => $job_item->getJobId()));
    return TRUE;
  }

  /**
   * {@inheritdoc}
   */
  public function rejectForm(array $form, FormStateInterface $form_state) {
    return $form;
  }

}
