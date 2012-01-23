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
  public function isAvailable(TMGMTTranslator $translator) {
    if ($translator->getSetting('api')) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::requestTranslation().
   */
  public function requestTranslation(TMGMTJob $job) {
    // @todo Who is handling cron / batch here, we or some other controller?
    // @todo If length is more then 10000 chars we need to split the request.
    // Pull the source data array from through the job and flatten it.
    $data = tmgmt_flatten_data($job->getSourceData());
    foreach ($data as $key => $value) {
      if (isset($value['#translate']) && $value['#translate'] === FALSE) {
        continue;
      }
      $query = array(
        'from' => $job->source_language,
        'to' => $job->target_language,
        'contentType' => 'text/plain',
        'text' => $value['#text'],
      );
      $options = array(
        // @todo For some reason post is returning 202 status and service is not
        // translating things, maybe check this later.
        'headers' => array('Content-Type' => 'text/plain'),
      );
      $result = $this->doRequest($job->getTranslator(), 'Translate', $query, $options);
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
          $job->rejected('Rejected by Microsoft Translator: @error', array('@error' => $result->data));
          return;
        default:
          $job->addMessage('Unknown error from Microsoft Translator: @error', array('@error' => $result->data), 'error');
          return;
      }
    }
    // Save the translated data through the job.
    $job->addTranslatedData(tmgmt_unflatten_data($data));
    // Mark this string as translatable.
    $job->needsReview('Microsoft Translator translation completed.');
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::getSupportedTargetLanguages().
   */
  public function getSupportedTargetLanguages(TMGMTTranslator $translator, $source_language) {
    $languages = $this->getSupportedLanguages($translator);
    unset($languages[$source_language]);
    return $languages;
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::getSupportedLanguages().
   */
  public function getSupportedLanguages(TMGMTTranslator $translator) {
    // @todo Maybe add database caching?
    $languages = &drupal_static(__CLASS__ . '::' . __METHOD__);
    if (!isset($languages)) {
      $languages = array();
      $request = $this->doRequest($translator, 'GetLanguagesForTranslate');
      if ($request->code == 200) {
        $dom = new DOMDocument;
        $dom->loadXML($request->data);
        foreach ($dom->getElementsByTagName('string') as $item) {
          $languages[$item->nodeValue] = $item->nodeValue;
        }
      }
    }
    return $languages;
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::defaultTranslatorSettings().
   */
  public function defaultTranslatorSettings() {
    return array(
      // @todo Remove default API Key once development is finished.
      'api' => 'DA903AE0DF7668FD33F53192B5382E4D1D2CF369',
      'url' => $this->translatorUrl,
    );
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::settingsForm().
   */
  public function translatorSettingsForm($form, &$form_state, TMGMTTranslator $translator) {
    $form['api'] = array(
      '#type' => 'textfield',
      '#title' => t('Microsoft API Key'),
      '#default_value' => $translator->getSetting('api'),
      '#description' => t('Please enter your Microsoft API ID, or follow this <a hef="!link">link</a> to generate one.', array('!link' => 'http://www.bing.com/developers/appids.aspx.')),
    );
    return $form;
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::jobSettingsForm().
   */
  public function jobSettingsForm($form, &$form_state, TMGMTJob $job) {
    $settings['group_translations'] = array(
      '#type' => 'checkbox',
      '#title' => t('Group translation items (@TODO)'),
      '#description' => t('If checked all text items in this translation job will be send to server in one request.')
    );
    return $settings;
  }

  /**
   * Execute a request against the Microsoft API.
   *
   * @param TMGMTTranslator $translator
   *   The translator entity to get the settings from.
   * @param $path
   *   The path that should be appended to the base uri, e.g. Translate or
   *   GetLanguagesForTranslate.
   * @param $query
   *   (Optional) Array of GET query arguments.
   * @param $options
   *   (Optional) Array of additional options passed to drupal_http_request().
   *
   * @return
   *   The response object returned by drupal_http_request().
   */
  protected function doRequest(TMGMTTranslator $translator, $path, array $query = array(), array $options = array()) {
    // Allow to override the url, e.g. for the tests.
    $url = $translator->getSetting('url') . '/' . $path;

    // Add the appId to the query arguments and build the query string.
    $query = array('appId' => $translator->getSetting('api')) + $query;
    $url = url($url, array('query' => $query));
    return drupal_http_request($url, $options);
  }

}
