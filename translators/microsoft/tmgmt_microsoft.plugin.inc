<?php

/**
 * @file
 * Provides Microsoft Translator plugin controller.
 *
 * Check @link http://msdn.microsoft.com/en-us/library/dd576287.aspx Microsoft
 * Translator @endlink. Note that we are using HTTP API.
 */

class TMGMTMicrosoftTranslatorController extends TMGMTDefaultTranslatorPluginController {

  /**
   * Translation service URL.
   *
   * @var string
   */
  protected $translatorUrl = 'http://api.microsofttranslator.com/v2/Http.svc';

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::isAvailable().
   */
  public function isAvailable() {
    if ($this->getOption('bing_api_id')) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::canTranslation().
   */
  public function canTranslate(TMGMTJob $job) {
    $languages = $this->getSupportedLanguages();
    if ($this->isAvailable() && isset($languages[$job->source_language]) && isset($languages[$job->target_language])) {
      // The job is only translatable if the translator is available too.
      return TRUE;
    }
    // What to return if we can not query service and can not get supported
    // languages, for what ever reason? For now it is safer to return FALSE.
    return FALSE;
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::requestTranslation().
   */
  public function requestTranslation(TMGMTJob $job) {
    // @todo Who is handling cron / batch here, we or some other controller?
    // @todo If length is more then 10000 chars we need to split the request.
    // Pull the source data array from through the job and flatten it..
    $data = tmgmt_flatten_data($job->getSourceData());
    foreach ($data as $key => $value) {
      if (isset($value['#translate']) && $value['#translate'] === FALSE) {
        continue;
      }
      $query = drupal_http_build_query(array(
        'appId'       => $this->getOption('bing_api_id'),
        'from'        => $job->source_language,
        'to'          => $job->target_language,
        'contentType' => 'text/plain',
        'text'        => $value['#text'],
      ));
      $options = array(
        // @todo For some reason post is returning 202 status and service is not translating things, maybe check this later.
        'headers' => array('Content-Type' => 'text/plain')
      );
      $result = drupal_http_request($this->translatorUrl . '/Translate?' . $query, $options);
      switch ($result->code) {
        case 200:
          // Lets use DOMDocument for now because this service enables us to
          // send an array of translation sources, and we will probably use
          // this soon.
          $dom = new DOMDocument;
          $dom->loadXML($result->data);
          $items = $dom->getElementsByTagName('string');
          $data[$key]['#text'] = $items->item(0)->nodeValue;
          break;
        case 400:
          // @todo Change error message.
          $job->rejected($result->data);
          return;
        default:
          // @todo Change error message.
          $job->addMessage('Error from server: @error', array('@error' => $result->data));
          return;
      }
    }
    // Save the translated data through the job.
    $job->addTranslatedData(tmgmt_unflatten_data($data));
    // Mark this string as translatable.
    $job->needsReview('Microsoft Translator translation completed.');
  }

  /**
   * Implements
   * TMGMTTranslatorPluginControllerInterface::getSupportedTargetLanguages().
   */
  public function getSupportedTargetLanguages($source_language) {
    $languages = $this->getSupportedLanguages();
    unset($languages[$source_language]);
    return $languages;
  }

  /**
   * Implements
   * TMGMTTranslatorPluginControllerInterface::getSupportedLanguages().
   */
  public function getSupportedLanguages() {
    $languages = &drupal_static(__FUNCTION__);
    if (!isset($languages)) {
      $languages = array();
      $request = drupal_http_request($this->translatorUrl . '/GetLanguagesForTranslate' . '?appId=' . $this->getOption('bing_api_id'));
      if ($request->code == 200) {
        $dom = new DOMDocument;
        $dom->loadXML($request->data);
        foreach($dom->getElementsByTagName('string') as $item) {
          $languages[$item->nodeValue] = $item->nodeValue;
        }
      }
    }
    return $languages;
  }

  /**
   * The settings form.
   */
  public function settingsForm($form, &$form_state) {
    $form['bing_api_id'] = array(
      '#type' => 'textfield',
      '#title' => t('Bing API ID'),
      // @todo - remove default test bing id before production release.
      '#default_value' => $this->getOption('bing_api_id', 'DA903AE0DF7668FD33F53192B5382E4D1D2CF369'),
      '#description' => t('Please enter your Bing API ID, or follow this !link to generate one.', array('!link' => l('link', 'http://www.bing.com/developers/appids.aspx.'))),
    );
    return $form;
  }

}
