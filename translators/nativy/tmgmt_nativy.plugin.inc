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
    return parent::translatorSettingsForm($form, $form_state, $translator);
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
    $this->nativyAuthenticate(
      $translator->getSetting('api_user_name'),
      $translator->getSetting('api_password')
    );
    // Build offer parameters
    /*
    $params = array();
    $params['settings'] = array(
      'emailCallbackLink' => 'https://www.yourapplication.com/nisinterface/gettranslation',
    );
    $params['customer'] = array(
      'customerEmail' => 'demo@nativy.com',
      'customerFirstName' => 'Demofirstname',
      'customerLastName' => 'Demolastname',
    );
    $params['languages'] = array(
      'primaryLangCode' => "de-DE",
      'language' => array('ms', 'jv', 'en', 'es'),
    );
    $params['textblocks'] = array (
      'textblock' => array(
        'code' => "faq_question1",
        'label' => "Wie funktioniert nativy?",
        'description langCode="de-DE"' => '
nativy hat ein System entwickelt, um den/die ideale ÜbersetzerIn für jeden
Auftrag zu finden. KundInnen können dabei ihre individuellen Kriterien in der Suche
nach dem/der idealen ÜbersetzerIn einbringen. nativy übernimmt in dem gesamten Prozess
die wichtigsten administrativen Aufgaben, um seinen NutzerInnen das Leben etwas zu
erleichtern und die Zusammenarbeit zu beschleunigen. nativy verrechnet seinen
KundInnen das von dem/der ÜbersetzerIn festgelegte Honorar inklusive eines
geringfügigen Vermittlungsaufschlags.
'
      ),
    );
    */
    //$response = $this->nativySoapRequest('CreateOffer', array('requ' => $params));
    $template = $this->getXMLTemplate('TranslationRequest');
    $template = file_get_contents(drupal_get_path('module', 'tmgmt_nativy') . '/nis_offerrequest_sample.xml');
    //dpm($template);
    $soapvar = new SoapVar($template, XSD_ANYXML);
    $response = FALSE;
    try {
    //$response = $this->nativySoapRequest('CreateOffer', array($soapvar));
    //$response = $client->CreateOffer($soapvar);
    //$params = (object)$params;
    $response = $client->CreateOffer($soapvar); //$template));
    }
    catch (Exception $e) {
      dpm($e, 'Exception');
    }
    dpm($response);
    dpm($client->__getLastRequest());
    dpm($client->__getLastRequestHeaders());
    /*
    $quality = !empty($form_state['values']['settings']['quality']) ? $form_state['values']['settings']['quality'] : 'machine';
    $translation_array = $this->generateMyGengoTranslationJobArray($job, $quality);
    $response = $this->requestFromGengo($job->getTranslator(), '/translate/service/quote', 'POST', array(
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
*/
    $settings['quote'] = array(
      '#type' => 'fieldset',
      '#title' => t('Quote'),
      '#prefix' => '<div id="nativity-quote">',
      '#suffix' => '</div>',
    );

    return $settings;
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
   * Send request to nativy, get response
   */
  protected function nativySoapRequest($method, $params) {
    $client = $this->nativySoapClient();
    $response = $client->$method($params);
    dpm($response);
    dpm($client->__getLastRequest(), 'Last Request');
    dpm($client->__getLastRequestHeaders(), 'Last Request Headers');
    return $response;
  }
  /**
   * Get SOAP client for Nativy service
   */
  protected function nativySoapClient() {
    static $client;
    if (!isset($client)) {
      $client = new SoapClient(
        drupal_get_path('module', 'tmgmt_nativy') . '/tmgmt_nativy_test.wsdl',
        //drupal_get_path('module', 'tmgmt_nativy') . '/tmgmt_nativy_live.wsdl',
        array('trace' => TRUE)
      );
      // For debugging we can pull API information from the WSDL
      dpm($client->__getFunctions(), 'Functions');
      dpm($client->__getTypes(), 'Types');
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

   // As no other thing seems to work (schema may be wrong?) we build a raw header
   $rawheader = '<SecuredWebServiceHeader xmlns="https://www.nativy.com/nis">';
   $rawheader .= "<Username>$user</Username>";
   $rawheader .= "<Password>$pass</Password>";
   $rawheader .= '</SecuredWebServiceHeader>';
   $soapvar = new SoapVar($rawheader, XSD_ANYXML);
   $headers['SecuredWebServiceHeader'] = new SoapHeader('http://www.nativy.com/nis',
                            'SecuredWebServiceHeader',
                             $soapvar);
   $client->__setSoapHeaders($headers);
   $response = $client->AuthenticateUser();
   // Got token that should be valid for 20 mins.
   $token = $response->AuthenticateUserResult;

   //dpm($response, $response->AuthenticateUserResult);
   //dpm($client->__getLastRequest());
   //dpm($client->__getLastRequestHeaders());

   // Now replace previous header with our new token and set new headers
   $rawheader = '<SecuredWebServiceHeader xmlns="https://www.nativy.com/nis">';
   $rawheader .= "<AuthenticatedToken>$token</AuthenticatedToken>";
   $rawheader .= '</SecuredWebServiceHeader>';
   $soapvar = new SoapVar($rawheader, XSD_ANYXML);
   $headers['SecuredWebServiceHeader'] = new SoapHeader('http://www.nativy.com/nis',
                            'SecuredWebServiceHeader',
                             $soapvar);
   // Note (From Nativy doc):
   // You should set Username and Password to an empty string for security reasons,
	 // because it is not used anymore after the authentication.
   $client->__setSoapHeaders(NULL);
   $client->__setSoapHeaders($headers);
 }

  public function getXMLTemplate($request_type, $params = array()) {
    switch($request_type) {
      case 'Authentication':
        $xml = '<?xml version="1.0" encoding="utf-8"?>
                <soap:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
                  <soap:Header>
                    <SecuredWebServiceHeader xmlns="https://www.nativy.com/nis">
                      <Username>' . $params['username'] . '</Username>
                      <Password>' . $params['password'] . '</Password>
                    </SecuredWebServiceHeader>
                  </soap:Header>
                  <soap:Body>
                    <AuthenticateUser xmlns="https://www.nativy.com/nis" />
                  </soap:Body>
                </soap:Envelope>';
        break;
      case 'TranslationRequest':
								return '<?xml version="1.0"?>
									<settings>
										<emailCallbackLink>
											https://www.yourapplication.com/nisinterface/gettranslation
										</emailCallbackLink>
									</settings>
								<customer customerId="mailto:demo@nativy.com">
									<customerEmail>demo@nativy.com</customerEmail>
									<customerFirstName>Demofirstname</customerFirstName>
									<customerLastName>Demolastname</customerLastName>
								</customer>
								<languages primaryLangCode="de-DE">
									<language langCode="ms" />
									<language langCode="jv" />
									<language langCode="en" />
									<language langCode="es" />
								</languages>
								<textblocks>
									<textblock code="faq_question1" label="Wie funktioniert nativy?">
										<description langCode="de-DE">
											nativy hat ein System entwickelt, um den/die ideale ÜbersetzerIn für jeden
                      Auftrag zu finden. KundInnen können dabei ihre individuellen Kriterien in der Suche
                      nach dem/der idealen ÜbersetzerIn einbringen. nativy übernimmt in dem gesamten Prozess
                      die wichtigsten administrativen Aufgaben, um seinen NutzerInnen das Leben etwas zu
                      erleichtern und die Zusammenarbeit zu beschleunigen. nativy verrechnet seinen
                      KundInnen das von dem/der ÜbersetzerIn festgelegte Honorar inklusive eines
                      geringfügigen Vermittlungsaufschlags.
										</description>
									</textblock>
								<textblock code="faq_question2" label="Wer übersetzt bei nativy?">
									<description langCode="de-DE">
										Nur professionelle ÜbersetzerInnen werden bei nativy zugelassen. Übersetzungen
										in die Muttersprache werden bevorzugt. Die Qualifikationen jedes/r ÜbersetzerIn werden
										durch nativy geprüft.
									</description>
								</textblock>
								<textblock code="faq_question3" label="Stehen AuftraggeberInnen und ÜbersetzerInnen in direktem Kontakt?">
  								<description langCode="de-DE">
  									Ja, auf der Webseite von nativy können Kurznachrichten zu den jeweiligen
  									Aufträgen ausgetauscht werden.
  								</description>
								</textblock>
							</textblocks>
						</offerrequest>';
        break;
    }
  }
}
