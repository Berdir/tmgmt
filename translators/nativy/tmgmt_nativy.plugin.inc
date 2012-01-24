<?php

/**
 * @file
 * Provides Microsoft Translator plugin controller.
 *
 * Check @link http://msdn.microsoft.com/en-us/library/dd576287.aspx Microsoft
 * Translator @endlink. Note that we are using HTTP API.
 */

class TMGMTNativyTranslatorController extends TMGMTDefaultTranslatorPluginController {
  /**
   * Translation service URL.
   *
   * @var string
   */
  //protected $translatorURL = 'http://api.microsofttranslator.com/v2/Http.svc';

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::isAvailable().
   */
  public function isAvailable(TMGMTTranslator $translator) {
    if ($translator->getSetting('api_user_name') && $translator->getSetting('api_password')) {
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
    return TRUE;
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::requestTranslation().
   *
   * Here we will acutally query source and get translations.
   */
  public function requestTranslation(TMGMTJob $job) {
    
    // @todo - who is handling cron / batch here, we or some other controler?
    $translate_url = $this->translatorURL . '/Translate';

    // Pull the source data array from through the job and flatten it..
    $data = tmgmt_flatten_data($job->getSourceData());
    foreach ($data as $key => $value) {
      if (isset($value['#translate']) && $value['#translate'] === FALSE) {
        continue;
      }

      $query_data = drupal_http_build_query(array(
        'appId'       => $this->getSetting('bing_api_id'),
        'from'        => $job->source_language,
        'to'          => $job->target_language,
        'contentType' => 'text/plain',
        // @todo - if length is more then 10000 chars we need to split request.
        'text'        => $value['#text'],
      ));

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
          return new TMGMTTranslatorResponse(TMGMTJob::STATE_REJECTED, $result->data);
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
    return new TMGMTTranslatorResponse(TMGMTJob::STATE_REVIEW, t('Microsoft Translator translation completed.'));
  }

  /**
   * Defines plugin settings form.
   *
   * @param $form array
   * @param $form_state array
   */
  public function translatorSettingsForm($form, &$form_state, TMGMTTranslator $translator) {
    $form['api_user_name'] = array(
      '#type' => 'textfield',
      '#title' => t('User name'),
      '#default_value' => $translator->getSetting('api_user_name', ''),
    );
    $form['api_password'] = array(
      '#type' => 'textfield',
      '#title' => t('Password'),
      '#default_value' => $translator->getSetting('api_password', ''),
    );
    return $form;
  }
  
  /**
   * Returns all languages that are supported by this service.
   *
   * @param TMGMTTranslator $translator
   *   The translator entity.
   *
   * @return array
   *   An array of languages in ISO format.
   */
  public function getSupportedLanguages(TMGMTTranslator $translator) {
    return parent::getSupportedLanguages($translator);
    $languages = &drupal_static(__FUNCTION__);
    if (!isset($languages)) {
      $languages = array();
      $res = drupal_http_request($this->translatorURL . '/GetLanguagesForTranslate' . '?appId=' . $this->getOption('bing_api_id'));
      if ($res->code == 200) {
        $dom = new DOMDocument;
        $dom->loadXML($res->data);
        $string_items = $dom->getElementsByTagName('string');
        foreach($string_items as $item) {
          $languages[$item->nodeValue] = $item->nodeValue;
        }
      }
    }
    return $languages;
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
   * Defines plugin job settings form.
   *
   * @param $form array
   * @param $form_state array
   * @return array
   *   Settings form.
   */
  public function jobSettingsForm($form, &$form_state, TMGMTJob $job) {
    $settings['quote'] = array(
      '#type' => 'fieldset',
      '#title' => t('Quote'),
      '#prefix' => '<div id="nativity-quote">',
      '#suffix' => '</div>',
    );
    $translator = $job->getTranslator();
    $client = $this->nativySoapClient();
    // First try authentication and if successful, go for the offer.
    $this->nativyAuthenticate(
      $translator->getSetting('api_user_name'),
      $translator->getSetting('api_password')
    );
    // Build offer parameters
    $template = file_get_contents(drupal_get_path('module', 'tmgmt_nativy') . '/nis_offerrequest_sample_good.xml');
    //$template = '<requ>' . $template . '</requ>';
    //dpm($template);
    $soapvar = new SoapVar($template, XSD_ANYXML);
      
    $response = FALSE;
    try {
      $response = $client->CreateOffer($soapvar);
      $iframe_url = $response->CreateOfferResult;
      $settings['iframe']['#markup'] = "<iframe width=\"100%\" height=\"800\" src=\"$iframe_url\"></iframe>";
    }
    catch (Exception $e) {
      dpm($e, 'Exception');
    }
    dpm($response);
    dpm($client->__getLastRequest());
    dpm($client->__getLastRequestHeaders()); 

    
/*
And this is what we get

(Object) SoapFault

    faultstring (String, 11 characters ) Bad Request
    faultcode (String, 4 characters ) HTTP
    xdebug_message (String, 861 characters ) SoapFault: Bad Request in /var/workspace/drupa...

SoapFault: Bad Request in /var/workspace/drupal7/sites/all/modules/translation_management/translators/nativy/tmgmt_nativy.plugin.inc on line 184 Call Stack: 0.0000 332892 1. {main}() /var/workspace/drupal7/index.php:0 0.0349 3037948 2. menu_execute_active_handler() /var/workspace/drupal7/index.php:20 0.0349 3038600 3. call_user_func_array() /var/workspace/drupal7/includes/menu.inc:503 0.0350 3038852 4. tmgmt_nativy_test_page() /var/workspace/drupal7/includes/menu.inc:0 0.0414 3695884 5. TMGMTNativyTranslatorController->jobSettingsForm() /var/workspace/drupal7/sites/all/modules/translation_management/translators/nativy/tmgmt_nativy.module:90 0.1751 3771464 6. SoapClient->CreateOffer() /var/workspace/drupal7/sites/all/modules/translation_management/translators/nativy/tmgmt_nativy.plugin.inc:184


 */

     
    return $settings;
  }

  /**
   * Build offer xml
   */
  protected function nativyBuildOffer($job) {
    
  }
  /**
   * Send request to nativy, get response
   */
  protected function nativySoapRequest($method, $params) {
    $client = $this->nativySoapClient();
    $response = $client->$method($params);
    dpm($response);
    dpm(array ('REQUEST' => $client->__getLastRequest()), 'Last Request');
    dpm($client->__getLastRequestHeaders(), 'Last Request Headers'); 
    return $response;
  }
  /**
   * Get SOAP client for Nativy service
   */
  protected function nativySoapClient() {
    static $client;
    if (!isset($client)) {
      $client = new TMGMTNativySoapClient(
        drupal_get_path('module', 'tmgmt_nativy') . '/tmgmt_nativy_test.wsdl',
        //drupal_get_path('module', 'tmgmt_nativy') . '/tmgmt_nativy_live.wsdl',
        array('trace' => TRUE)
      );
      // For debugging we can pull API information from the WSDL
      //dpm($client->__getFunctions(), 'Functions');
      //dpm($client->__getTypes(), 'Types');
    }
    return $client;
  }
  /**
   * Perform authentication
   * 
   * Enter description here ...
   */
   function nativyAuthenticate($user, $pass) {
     $client = $this->nativySoapClient();
     $client->setAuthHeader(array(
       'Username' => $user,
       'Password' => $pass,
     ));
     $response = $client->AuthenticateUser();
     //dpm($response, $response->AuthenticateUserResult);
     //dpm($client->__getLastRequest());
     //dpm($client->__getLastRequestHeaders()); 
     // Got token that should be valid for 20 mins.
     $token = $response->AuthenticateUserResult;
     $client->setAuthHeader(array('AuthenticatedToken' => $token));
     //dpm($response);
     //dpm($client->__getLastRequest());
     //dpm($client->__getLastRequestHeaders()); 
      
   }

}

/**
 * Nativy SOAP client
 */
class TMGMTNativySoapClient extends SoapClient {
   /**
    * Set authentication header.
    */
   public function setAuthHeader($params) {
     $rawheader = '<SecuredWebServiceHeader xmlns="https://www.nativy.com/nis">';
     foreach ($params as $name => $value) {
       $rawheader .= "<$name>$value</$name>";
     }
     $rawheader .= '</SecuredWebServiceHeader>';
     $soapvar = new SoapVar($rawheader, XSD_ANYXML);
     $headers['SecuredWebServiceHeader'] = new SoapHeader(
     		'http://www.nativy.com/nis', 
        'SecuredWebServiceHeader',
        $soapvar
     );
     //$client = $this->nativySoapClient();
     $this->__setSoapHeaders(NULL); 
     $this->__setSoapHeaders($headers);
   }  
}