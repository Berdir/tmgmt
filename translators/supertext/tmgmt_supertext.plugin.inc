<?php

/**
 * @file
 * Provides the Supertext translation plugin controller.
 */

/**
 * Supertext translator plugin controller.
 */
class TMGMTSupertextPluginController extends TMGMTDefaultTranslatorPluginController {

  /**
   * Translation service URL.
   *
   * @var string
   */
  protected $apiURL = 'http://www.supertext.ch/api/v1/';
  protected $sandboxURL = 'http://dev.supertext.ch/api/v1/';
  // @TODO The second one is only for testing on local network.
  //protected $sandboxURL = 'http://192.168.74.1/supertext/api/v1/';


  /**
   * Implements TMGMTTranslatorPluginControllerInterface::isAvailable().
   */
  public function isAvailable(TMGMTTranslator $translator) {
    if($translator->getSetting('api_username')) {
      return TRUE;
    }
    return FALSE;
  }

  
  /**
   * Returns a mapping for a generic language or null if there is no mapping necessary
   */
  public function getMappedLanguages($language){
    $options = array(
      'method' => 'GET',
      'headers' => array('Content-Type' => 'application/json'),
    );
    $response = drupal_http_request($this->sandboxURL.'translation/languagemapping/'.$language, $options);
    if ($response->code == "200") {
      $responsedata = json_decode($response->data);
      //if language is supported, we don't need a mapping
      if (isset($responsedata->Supported)) {
        if($responsedata->Supported == true)
        {
          return NULL; 
        }
        else
        {
          if (isset($responsedata->Languages)) {
            $lang_settings = array();
            foreach ($responsedata->Languages as $lang_item) {
              $lang_settings[$lang_item->Iso] = $lang_item->Name;
            }      
            return $lang_settings;
          }   
        }
      }       
    }          
    else {
      drupal_set_message(t("Could not get language mapping  from Supertext. Error: @error",array('@error' => $response->error)),'error');
    }    
  }  
  
  public function supertextHttpRequest($method, $object, TMGMTTranslator $translator){    
    // Allow to use the internal mocking URL.
    if ($custom_url = $translator->getSetting('url')) {
      $url = $custom_url;
    }
    else {
      $url = $translator->getSetting('use_sandbox') ? $this->sandboxURL : $this->apiURL;
    }
    $url .= $method;
  	
  	$options = array(
      'method' => 'POST',
      'data' => json_encode($object),
      'headers' => array('Content-Type' => 'application/json', 
        'Accept' => 'application/json',
  	    'User-Agent' => 'Drupal Supertext Translation Interface v0.1',
  	    'Authorization' => 'Basic '.base64_encode($translator->getSetting('api_username').':'.$translator->getSetting('api_token'))),	
    );
    return drupal_http_request($url, $options);  	
  }
  
  
  public function generateSupertextOrderObject(TMGMTJob $job){
  	$translator = $job->getTranslator();    
    //check if there is mapping
  	if($language = $translator->getSetting('language_mapping_'.$job->source_language)){ 
      $sourceLanguage = $language;
    } else {
      $sourceLanguage = $job->source_language;
    }
    if($language = $translator->getSetting('language_mapping_'.$job->target_language)){ 
      $targetLanguage = $language;
    } else {
      $sourceLanguage = $job->target_language;
    }
    $object = new stdClass();
    $object->CallbackUrl = url('tmgmt_supertext_callback', array('absolute' => TRUE));
    $object->ContentType = "text/html";
    $object->Currency = "EUR";
    $object->SourceLang = $sourceLanguage;
    $object->TargetLang = $targetLanguage;
    $object->ReferenceData = $job->tjid . ':' . tmgmt_supertext_hash($job->tjid);
    $object->Groups = array();
    
    $items = $job->getItems();
    //watchdog('supertext', 'getItems  <br /><pre>!post</pre>', array('!post' => print_r($items, TRUE)));
    // @todo check to use $job->getSourceData();
    //$items = $job->getSourceData();
    //watchdog('supertext', 'getSourceData  <br /><pre>!post</pre>', array('!post' => print_r($job->getSourceData(), TRUE)));
    foreach ($items as $tjiid => $item) {
      $itemSourceData = $item->getSourceData();
      $fields = tmgmt_flatten_data($itemSourceData);
      $group = new stdClass();
      $group->GroupId = $tjiid;
      $group->Context = $itemSourceData['#label'];
      $group->Items = array();
      foreach ($fields as $fieldarray_id => $field_array) {
        $field = new stdClass();
        $field->Content = $field_array['#text'];
        $field->Context = $field_array['#label'];
        $field->Id = $fieldarray_id;
        $group->Items[] = $field;
      }
      $object->Groups[] = $group;
    }
    return $object;
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::requestTranslation().
   *
   * Here we will acutally query source and get translations.
   */
  public function requestTranslation(TMGMTJob $job) {
  	$object = $this->generateSupertextOrderObject($job);
  	$order_settings = explode(":", $job->settings['supertext-job']);
    $object->OrderTypeId = $order_settings[0];
    $object->DeliveryId = $order_settings[1];
	  $object->OrderName = $job->settings['job-label'];
    
	  $job->label = $job->settings['job-label'];
	  
    //dpm($object);	  
	  $response = $this->supertextHttpRequest('/translation/order', $object, $job->getTranslator());	  
    if ($response->code == "200") {
      //$job->submitted("Job sent to Supertext. Response: !response",array('!response'=> $response->data));
      $job->submitted("Job sent to Supertext.", null);
    	$responsedata = json_decode($response->data);
      // Save order id to the job
      $job->reference = $responsedata->Id;
      $job->addMessage(t("Thank you very much for your order."));
      $job->addMessage(t("Order Id: @orderid",array(
        '@orderid' => $responsedata->Id)
      ));
      $job->addMessage(t("Deadline: @deadline",array(
        '@deadline' => format_date(strtotime($responsedata->Deadline)),
      )));
    } else if($response->code == "401") {
    	//wrong or missing authentication
      $job->rejected('Rejected by Supertext Translator: Wrong or missing authentication. Please check your Supertext Translator Settings.', null, 'error');
      return;
    } else {
      $job->rejected('Unknown error from Microsoft Translator: !error', array('!error' => $response->data), 'error');
      return;
    }
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::getSupportedTargetLanguages().
   */
  public function getSupportedTargetLanguages(TMGMTTranslator $translator, $source_language) {
    // @todo: Implement this properly.
    return parent::getSupportedTargetLanguages($translator, $source_language);
  }

}
