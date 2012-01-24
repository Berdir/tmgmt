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
    dpm($job);
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
    return parent::translatorSettingsForm($form, $form_state, $translator);
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
    $translator = $job->getTranslator();
    $client = $this->nativySoapClient();
    // First try authentication and if successful, go for the offer.
    $authenticated = $client->authenticate($translator->getSetting('api_user_name'), $translator->getSetting('api_password'));
    if ($authenticated) {
      $settings['help']['#markup'] =
    		'<p>' . t('You need to complete your order through Nativy pages below') . '</p>';
      // Build offer parameters for straight text replacement
      // Customer data
      $data['!customerEmail'] = 'demo@nativy.com';
      $data['!customerFirstName'] = 'Jose';
      $data['!customerLastName'] = 'Reyero';
      // Languages
      $data['!primaryLangCode']= $job->source_language; //"de-DE";
      $data['!langCode'] = $job->target_language; //"en";
      // Add text
      $data['!textblocks'] = $this->nativyBuildText($job);
      $data['!emailCallbackLink'] = url('nativy/callback', array('absolute' => TRUE));
      //$template = file_get_contents(drupal_get_path('module', 'tmgmt_nativy') . '/nis_offerrequest_sample_good.xml');
      $template = file_get_contents(drupal_get_path('module', 'tmgmt_nativy') . '/nis_offerrequest.xml');
      $template = strtr($template, $data);    
      $response = $client->xmlRequest('CreateOffer', $template);
      if ($response) {
        // This should look like http://test.nativy.com/client/createorder.aspx?token=a19fd9f9-4bdc-4396-aebf-277fafd9c42a&amp;orderid=f1446707-ae2f-499e-a08e-47131533a88d
        $iframe_url = $response->CreateOfferResult;
        $parts = explode('orderid=', $iframe_url);
        if (count($parts) == 2 && ($orderid = $parts[1])) {
          $settings['orderid'] = array('#type' => 'value', '#value' => $orderid);
          $settings['iframe']['#markup'] = "<iframe width=\"800\" height=\"800\" src=\"$iframe_url\"></iframe>";
          return $settings;
        }
      }     
    }
    // Something went wrong, just print message and get out.
    $client->printDebug();
    $settings['error']['#markup'] =
        '<p>' . t('Cannot authenticate to Nativy server. Pleaser review your user name and password.') .'</p>';
    return $settings;
  }
  /**
   * Build text as xml
   */
  protected function nativyBuildText($job) {
    $output = '';
    $template = '<textblock diffgr:id="textblock!number" msdata:rowOrder="!order" code="!code" label="!label" msdata:hiddentextblock_Id="!order">';
    $template .= '<description langCode="!langCode">';
    $template .= '!text';
    $template .= '</description>';
    $template .= "</textblock>\n";
    $data['!langCode'] = $job->source_language;
    $text = tmgmt_flatten_data($job->getSourceData());
    $number = $order = 0;
    foreach ($text as $key => $item) {
      $data['!order'] = $order;
      $data['!number'] = $number;
      $data['!code'] = $key;
      $data['!label'] = $item['#label'];
      $data['!text'] = $item['#text'];
      $output .= strtr($template, $data);
      $number++;
      $order++;
    }
    return $output;
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
    $client->printDebug();
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

 }

/**
 * Nativy SOAP client
 */
class TMGMTNativySoapClient extends SoapClient {
  /**
   * Run request from full raw XML body
   */
  public function xmlRequest($method, $xmldata) {
    $soapvar = new SoapVar($xmldata, XSD_ANYXML);
      
    $response = FALSE;
    try {
      $response = $this->$method($soapvar);
    }
    catch (Exception $e) {
      dpm($e, 'Exception');
    }
    return $response;
  }
   /**
    * Authenticate to the service
    */
   public function authenticate($user, $pass) {
     try {
       $this->setAuthHeader(array(
         'Username' => $user,
         'Password' => $pass,
       ));
       $response = $this->AuthenticateUser();
       $token = $response->AuthenticateUserResult;
       $this->setAuthHeader(array('AuthenticatedToken' => $token));
       return TRUE;
     }
     catch (Exception $e) {
       dpm($exception);
       $this->debug();
     }
   } 
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
     $this->__setSoapHeaders(NULL); 
     $this->__setSoapHeaders($headers);
   }

   /**
    * Debug. Print out last request and response
    */
   public function printDebug() {
     dpm(array(
       'Request' => $this->__getLastRequest(),
       'RequestHeaders' => $this->__getLastRequestHeaders()
     ), 'SOAP REQUEST');
     dpm(array(
       'Response' => $this->__getLastResponse(),
       'ResponseHeaders' => $this->__getLastResponseHeaders()
     ), 'SOAP RESPONSE');
   }
}
