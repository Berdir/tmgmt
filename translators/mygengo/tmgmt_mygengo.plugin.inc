<?php

/**
 * @file
 * Provides myGengo translation plugin controller.
 */

/**
 * myGengo translation plugin controller.
 */
class TMGMTMyGengoPluginController extends TMGMTDefaultTranslatorPluginController {

  /**
   * Translation service URL.
   *
   * @var string
   */
  protected $apiURL = 'http://api.mygengo.com/v1.1/';
  protected $sandboxURL = 'http://api.sandbox.mygengo.com/v1.1/';
  protected $useSandbox = TRUE;

  /**
   *  requestFromGengo($endpoint, $data = array())
   *
   *  Builds, signs and fires a request to myGengo's API. Will fire to a different url
   *  if on the sandbox.
   *
   *  Authentication rituals are documented here: http://mygengo.com/api/developer-docs/authentication/
   *
   *  It should be noted that the authentication ritual listed there is different from the one here (v1 vs v1.1,
   *  respectively). v1.1 doesn't require signing on the entire data structure, just a timestamp and keys.
   */
  public function requestFromGengo(TMGMTTranslator $translator, $endpoint, $method, $data = array()) {
    $options = array(
      'headers' => array(
        'User-Agent' => 'Drupal myGengo Translation Interface v0.1',
        'Accept' => 'application/json'
      ),
      'method' => $method
    );

    $timestamp = gmdate('U');
    $url = ($this->useSandbox ? $this->sandboxURL : $this->apiURL) . $endpoint;

    /**
     *  If it's a GET or DELETE request, just sign it and send it appropriately. PUT/POST
     *  get a little more annoying...
     */
    if ($method == 'GET' || $method == 'DELETE') {
      $query_data = drupal_http_build_query(array_merge(array(
          'api_key' => $translator->getSEtting('api_public_key'),
          'api_sig' => hash_hmac('sha1', $timestamp, $translator->getSetting('api_private_key')),
          'ts' => $timestamp
          ), $data));

      $response = drupal_http_request($url . '?' . $query_data, $options);
    }
    else {
      $options['headers']['Content-Type'] = 'application/x-www-form-urlencoded';
      $options['data'] = drupal_http_build_query(array(
        'api_key' => $translator->getSetting('api_public_key'),
        'api_sig' => hash_hmac('sha1', $timestamp, $translator->getSetting('api_private_key')),
        'ts' => $timestamp,
        'data' => json_encode($data)
        ));

      $response = drupal_http_request($url, $options);
    }

    $results = json_decode($response->data);
    if ($results->opstat == 'ok' && isset($results->response)) {
      return $results->response;
    }
    else {
      // TODO: Raise an actual error. >_>
      return array();
    }
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::isAvailable().
   */
  public function isAvailable(TMGMTTranslator $translator) {
    if ($translator->getSetting('api_public_key') && $translator->getSetting('api_private_key')) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::canTranslation().
   *
   * We will check are source and target language supported.
   */
  public function canTranslate(TMGMTJob $job) {
    $translator = $job->getTranslator();
    if ($this->isAvailable($translator)) {
      $targets = $this->getSupportedTargetLanguages($translator, $job->source_language);
      if (isset($targets[$job->target_language])) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::requestTranslation().
   *
   * Here we will acutally query source and get translations.
   */
  public function requestTranslation(TMGMTJob $job) {
    // Pull the source data array from through the job and generate the MyGengo Translation Job
    $translations = $this->generateMyGengoTranslationJobArray($job, $job->settings['quality']);

    $response = $this->requestFromGengo($job->getTranslator(), 'translate/jobs', 'POST', array(
      'jobs' => $translations,
      'process' => 1,
      'as_group' => 1
      ));

    // Save the translated data through the job.
    $job->addTranslatedData(tmgmt_unflatten_data($response));
    //return new TMGMTTranslatorResponse(TMGMT_JOB_STATE_REVIEW, t('Microsoft Translator translation completed.'));
  }

  public function generateMyGengoTranslationJobArray(TMGMTJob $job, $tier = "machine") {
    $data = tmgmt_flatten_data($job->getSourceData());

    $translations = array();

    foreach ($data as $key => $value) {
      if (isset($value['#translate']) && $value['#translate'] === FALSE) {
        continue;
      }

      // NOTE: There's a not-so-fun-bug in the myGengo API that you'll encounter
      // if you change how this key is set. If the key in this hash starts with
      // numbers (especially a 0...), you will get an odd one-off response format
      // that can't be parsed by default.
      //
      // This is being fixed in a future iteration of the API (v2). If you encounter
      // weirdness with this issue, feel free to email us at api@mygengo.com. ;)
      //
      // - Ryan McGrath, who dislikes this error intensely
      $translations['job' . $job->tjid . $key] = array(
        'type' => 'text',
        'slug' => $key,
        'body_src' => $value['#text'],
        'lc_src' => $job->source_language,
        'lc_tgt' => $job->target_language,
        'tier' => $tier,
        'callback_url' => url('tmgmt_mygengo_callback', array('absolute' => TRUE)),
        'custom_data' => $job->tjid . '][' . $key
      );
    }
    return $translations;
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::receiveTranslation().
   */
  public function receiveTranslation(TMGMTJob $job, $keys, $data) {
    $job->addTranslatedData(array('#text' => $data->body_src), $keys);
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::getSupportedTargetLanguages().
   */
  public function getSupportedTargetLanguages(TMGMTTranslator $translator, $source_language) {
    $results = array();
    $targets = $this->requestFromGengo($translator, 'translate/service/language_pairs', 'GET', array('lc_src' => $source_language));
    foreach ($targets as $target) {
      $results[$target->lc_tgt] = $target->lc_tgt;
    }
    return $results;
  }

}
