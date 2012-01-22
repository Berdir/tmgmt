<?php
/**
 * @file
 * Provides myGengo translation plugin controller.
 */

class TMGMTMyGengoController extends TMGMTDefaultTranslatorPluginController {

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
  public function requestFromGengo($endpoint, $method, $data = array()) {
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
          'api_key' => $this->getOption('api_public_key'),
          'api_sig' => hash_hmac('sha1', $timestamp, $this->getOption('api_private_key')),
          'ts' => $timestamp
          ), $data));

      $response = drupal_http_request($url . '?' . $query_data, $options);
    }
    else {
      $options['headers']['Content-Type'] = 'application/x-www-form-urlencoded';
      $options['data'] = drupal_http_build_query(array(
        'api_key' => $this->getOption('api_public_key'),
        'api_sig' => hash_hmac('sha1', $timestamp, $this->getOption('api_private_key')),
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
  public function isAvailable() {
    if ($this->getOption('api_public_key') && $this->getOption('api_private_key')) {
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
    if ($this->isAvailable()) {
      $targets = $this->getSupportedTargetLanguages($job->source_language);
      if (in_array($job->target_language, $targets))
        return TRUE;
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
	$translations = $this->generateMyGengoTranslationJobArray($job);


    $response = $this->requestFromGengo('translate/jobs', 'POST', array(
      'jobs' => $translations,
      'process' => 1,
      'as_group' => 1
    ));

    // Save the translated data through the job.
    $job->addTranslatedData(tmgmt_unflatten_data($response));
    //return new TMGMTTranslatorResponse(TMGMT_JOB_STATE_REVIEW, t('Microsoft Translator translation completed.'));
  }

  public function generateMyGengoTranslationJobArray(TMGMTJob $job, $tier = "machine"){

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
  public function getSupportedTargetLanguages($source_language) {
    $results = array();
    $targets = $this->requestFromGengo('translate/service/language_pairs', 'GET', array(
      'lc_src' => $source_language
    ));

    foreach($targets as $target) {
      array_push($results, $target->lc_tgt);
    }
    // Resulting array must have language codes as keys
    $results = array_flip(array_unique($results));
    return $results;
  }

  /**
   * Returns array of service supported languages.
   *
   * @return array
   */
  public function getSupportedLanguages() {
    $languages = &drupal_static(__FUNCTION__);
    if (!isset($languages)) {
      $supported_languages = $this->requestFromGengo('translate/service/languages', 'GET');
      $languages = array();

      foreach ($supported_languages as $language) {
        array_push($languages, $language->lc);
      }
    }
    return $languages;
  }

  /**
   * Defines plugin settings form.
   *
   * @param $form array
   * @param $form_state array
   */
  public function settingsForm($form, &$form_state, TMGMTTranslator $translator) {

    $form['api_public_key'] = array(
      '#type' => 'textfield',
      '#title' => t('myGengo API Public key'),
      '#default_value' => $translator->getSetting('api_public_key', ''),
      '#description' => t('Please enter yout myGengo API Public key.'),
    );
    $form['api_private_key'] = array(
      '#type' => 'textfield',
      '#title' => t('myGengo API Private key'),
      '#default_value' => $translator->getSetting('api_private_key', ''),
      '#description' => t('Please enter your myGengo API Private key.'),
    );
    return $form;
  }

  /**
   * Defines plugin job settings form.
   *
   * @param $form array
   * @param $form_state array
   * @return array
   *   Settings form.
   */
  public function jobSettingsForm($form, &$form_state, TMGMTJob $job) {
  	$quality = !empty($form_state['values']['settings']['quality']) ? $form_state['values']['settings']['quality'] : 'machine';
  	$translation_array = $this->generateMyGengoTranslationJobArray($job, $quality);
  	$response = $this->requestFromGengo('/translate/service/quote', 'POST', array(
      'jobs' => $translation_array,
      'as_group' => 1
    ));
    $sum_credits = 0;
    $highest_eta = "";
    $sum_unit_count = 0;
    foreach ($response->jobs as $mygengo_job) {
    	$sum_unit_count = $sum_unit_count + $mygengo_job->unit_count;
    	$sum_credits = $sum_credits + $mygengo_job->credits;
    	$highest_eta = ($mygengo_job->eta > $highest_eta) ? $mygengo_job->eta : $highest_eta;
    }



    $settings['word_count'] = array(
      '#type' => 'item',
      '#title' => t('Word count'),
      '#markup' => $sum_unit_count,
    );

        $settings['quality'] = array(
      '#type' => 'select',
      '#title' => t('Quality'),
	  '#options' => array(
	          'machine' => t('Machine'),
	         'standard' => t('Standard'),
    		'pro' => t('Pro'),
	         'ultra' => t('Ultra'),
	       ),
	          '#description' => t('With which Quality you want to translate?'),
	       '#ajax' => array(

      'callback' => 'tmgmt_mygengo_quote',
	       'wrapper' => 'mygengo-quote',

    ),
    );

      $settings['quote'] = array(
        '#type' => 'fieldset',
        '#title' => t('Quote'),
        '#prefix' => '<div id="mygengo-quote">',
        '#suffix' => '</div>',
      );

    $settings['quote']['needed_credits'] = array(
      '#type' => 'item',
      '#title' => t('Needed Credits'),
      '#markup' => $sum_credits,
    );

    $settings['quote']['eta'] = array(
      '#type' => 'item',
      '#title' => t('ETA'),
      '#markup' => format_date(mktime() + $highest_eta, "long"),
    );


    return $settings;
  }

}
