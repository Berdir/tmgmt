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
    public function requestFromGengo($endpoint, $data = array()) {
        $options = array(
            'headers' => array(
                'User-Agent' => 'Drupal myGengo Translation Interface v0.1',
                'Accept' => 'application/json'
            )
        );

        $timestamp = gmdate('U');

        /**
         *  If it's a GET or DELETE request, just sign it and send it appropriately. PUT/POST
         *  get a little more annoying...
         */
        if($options->method == 'GET' || $options->method == 'DELETE') { 
            $request_data = drupal_http_build_query(array_merge(array(
                'api_key' => $this->getOption('api_public_key'),
                'api_sig' => hash_hmac('sha1', $timestamp, $this->getOption('api_private_key')),
                'ts' => $timestamp
            ), $data));

            $url = ($this->useSandbox ? $this->sandboxURL : $this->apiURL) . $endpoint . '?';
            $response = drupal_http_request($url.$query_data, $options);
        } else {
            // POST/PUT
        }

        $results = json_decode($response->data);
        if($results->opstat == 'ok' && isset($results->response)) {
            return $results->response;
        } else {
            // TODO: Raise an actual error. >_>
            return 'x';
        }
    }

    /**
     * Implements TMGMTTranslatorPluginControllerInterface::isAvailable().
     */
    public function isAvailable() {
        if($this->getOption('api_public_key') && $this->getOption('api_private_key')) {
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
        if($this->isAvailable()) {
            $targets = $this->getSupportedTargetLanguages($job->source_language);
            if(in_array($job->target_language, $targets)) return TRUE;
        }
        
        return FALSE;
    }

    /**
     * Implements TMGMTTranslatorPluginControllerInterface::requestTranslation().
     *
     * Here we will acutally query source and get translations.
     */
    public function requestTranslation(TMGMTJob $job) {
        // Pull the source data array from through the job and flatten it..
        $data = tmgmt_flatten_data($job->getSourceData());
        foreach($data as $key => $value) {
            if (isset($value['#translate']) && $value['#translate'] === FALSE) {
                continue;
            }

      $options = array(
        // @todo - for some reason post is returning 202 status and service
        // is not translating things, maybe check this later.
        //'method' => 'POST',
        //'data' => $query_data,
        'headers' => array('Content-Type' => 'text/plain')
      );
      $result = drupal_http_request($translate_url. '?' . $query_data, $options);
      switch ($result->code) {
        case 200:
          // Lets leave DOMDocument for now because this service enable us
          // to send array of translation sources, and we will probably use
          // this soon.
          $dom = new DOMDocument;
          $dom->loadXML($result->data);
          $string_items = $dom->getElementsByTagName('string');
          $data[$key]['#text'] = $string_items->item(0)->nodeValue;
          break;
        case 400:
          return new TMGMTTranslatorResponse(TMGMT_JOB_STATE_REJECTED, $result->data);
          break;
        default:
          // @todo - we need to think here more what to do if service is not
          // responding for some items and is responding for some others.
          // Ideally we should save sources that we manage to translate and
          // not just return.
          return new TMGMTTranslatorResponse(NULL, $result->data);
          break;
      }
    }
    // Save the translated data through the job.
    $job->addTranslatedData(tmgmt_unflatten_data($data));
    return new TMGMTTranslatorResponse(TMGMT_JOB_STATE_REVIEW, t('Microsoft Translator translation completed.'));
  }

    /**
     * Implements TMGMTTranslatorPluginControllerInterface::getSupportedTargetLanguages().
     */
    public function getSupportedTargetLanguages($source_language) {
        return $this->requestFromGengo('translate/service/language_pairs', array(
            'lc_src' => $source_language
        ));
    }

    /**
     * Returns array of service supported languages.
     *
     * @return array
     */
    public function getSupportedLanguages() {
        $languages = &drupal_static(__FUNCTION__);
        if(!isset($languages)) {
            $supported_languages = $this->requestFromGengo('translate/service/languages');
            $languages = array();
            
            foreach($supported_languages as $language) {
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
    public function settingsForm($form, &$form_state) {
      $form['api_public_key'] = array(
        '#type' => 'textfield',
        '#title' => t('myGengo API Public key'),
        '#default_value' => $this->getOption('api_public_key', ''),
        '#description' => t('Please enter yout myGengo API Public key.'),
      );

      $form['api_private_key'] = array(
        '#type' => 'textfield',
        '#title' => t('myGengo API Private key'),
        '#default_value' => $this->getOption('api_private_key', ''),
        '#description' => t('Please enter your myGengo API Private key.'),
      );
      return $form;
    }

}
