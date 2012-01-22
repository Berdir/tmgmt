<?php

/**
 * @file
 * Provides a Supertext translation plugin controller.
 */
class TMGMTSupertextController extends TMGMTDefaultTranslatorPluginController {

  /**
   * Translation service URL.
   *
   * @var string
   */
  protected $apiURL = 'http://www.supertext.ch/api/v1/';
  // @TODO The second one is only for testing on local network.
  protected $sandboxURL = 'http://dev.supertext.ch/api/v1/';
  //protected $sandboxURL = 'http://172.21.21.66/supertext/api/v1/';
 

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::isAvailable().
   */
  public function isAvailable() {
    if($this->getOption('api_account_id') && $this->getOption('api_password')) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::canTranslate().
   *
   * We will check are source and target language supported.
   */
  public function canTranslate(TMGMTJob $job) {
    return TRUE;
  }
  
  public function generateSupertextOrderObject(TMGMTJob $job){
  $object = new stdClass();
    $object->AccountId = variable_get('tmgmt_supertext_api_account_id', '');
    $object->Password = variable_get('tmgmt_supertext_api_password', '');
    $object->CallbackUrl = url('tmgmt_supertext_callback', array('absolute' => TRUE));
    $object->CommunicationLang = "en-US";
    $object->ContentType = "text/plain";
    $object->Currency = "chf";
	$object->Service = "translation";
    $object->SourceLang = $job->source_language;
    $object->TargetLang = $job->target_language;
    $object->ReferenceData = $job->tjid . ':' . tmgmt_supertext_hash($job->tjid);
    $object->Groups = array();

    $items = $job->getItems();
          // @todo check to use $job->getSourceData();

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
  	$order_settings = explode(":", $job->translator_context['supertext-job']);
    $object->OrderTypeId = $order_settings[0];
    $object->DeliveryId = $order_settings[1];
    $job->label = $job->translator_context['job-label'];
	$object->OrderTitle = $job->translator_context['job-label'];
    
    $options = array(
      'method' => 'POST',
      'data' => json_encode($object),
      'headers' => array('Content-Type' => 'application/json'),
    );

    $job->addMessage("Sending request to supertext");
    $response = drupal_http_request($this->sandboxURL.'/translation/order', $options);


    if ($response->code == "200") {
      $job->submitted("Job sent to Supertext. Response: !response",array('!response'=> $response->data));
      $responsedata = json_decode($response->data);
      // Save order id to the job
      $job->translator_id = $responsedata->Id;
      drupal_set_message(t("Job successfully sent to Supertext. <br />Ordertitle: @ordertitle<br />OrderID: @orderid<br />Price: @price<br />Wordcount: @wordcount<br />Deadline: @deadline",array(
        '@ordertitle' => $responsedata->OrderTitle,
        '@orderid' => $responsedata->Id,
        '@price' => $responsedata->Price,
        '@wordcount' => $responsedata->WordCount,
        '@deadline' => format_date(strtotime($responsedata->Deadline)),
      )));
    } else {
      $job->addMessage("Could not send Job to Supertext. Response: @response",array('@response'=>$response->data));
      drupal_set_message(t("Could not send Job to Supertext. Error: @error",array('@error' => $response->error)),'error');
    }
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::getSupportedTargetLanguages().
   */
  public function getSupportedTargetLanguages($source_language) {
    // @TODO Not real data, we are returning list of languages for now

    // Array like array('es' => TRUE, 'de' => TRUE);
    return array_flip(array_keys(language_list()));
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::settingsForm().
   */
  public function settingsForm($form, &$form_state, TMGMTTranslator $translator) {
    $form['api_account_id'] = array(
      '#type' => 'textfield',
      '#title' => t('Supertext API Account Id'),
      '#default_value' => $translator->getSetting('api_account_id', ''),
      '#description' => t('Please enter yout supertext API Account Id.'),
    );

    $form['api_password'] = array(
      '#type' => 'textfield',
      '#title' => t('Supertext API password'),
      '#default_value' => $translator->getSetting('api_password', ''),
      '#description' => t('Please enter your Supertext API password.'),
    );

    $form['use_sandbox'] = array(
      '#type' => 'checkbox',
      '#title' => t('Use Sandbox'),
      '#default_value' => $translator->getSetting('use_sandbox', TRUE),
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
    $object = $this->generateSupertextOrderObject($job);
        $object->TargetLang = 'de-CH';
    dpm($object);
    $options = array(
      'method' => 'POST',
      'data' => json_encode($object),
      'headers' => array('Content-Type' => 'application/json'),
    );
                
    $response = drupal_http_request($this->sandboxURL.'/translation/quote', $options);

    if ($response->code == "200") {
      //$job->submitted("Job sent to Supertext. Response: @response",array('@response'=>$repsonse->data));
      $responsedata = json_decode($response->data);
      $joboptions = array();
	  $settings['supertext-job'] = array(
	  	'#type' => 'value', 
	  );
	  $settings['job-label'] = array(
	  	'#type' => 'textfield',
	  '#title' => t('Job Title'), 
  '#default_value' => $job->label, 
	  '#size' => 60, 
  '#maxlength' => 128, 
	  );	  
      if (isset($responsedata->Options)) {
       foreach ($responsedata->Options as $option_item) {
            $settings[$option_item->OrderTypeId . "_markup"] = array(
            	'#type' => 'item',
		        '#title' => $option_item->Name,
                '#markup' => $option_item->Description,
		      );       	
          foreach ($option_item->DeliveryOptions as $delivery_key => $delivery_item) {
            $name = format_date(strtotime($delivery_item->DeliveryDate)) . ', Price ' . $delivery_item->Price . " ". $responsedata->Currency;
            $settings[$option_item->OrderTypeId .":" .$delivery_item->DeliveryId] = array(
		        '#type' => 'radio',
		        '#title' => $name,
                '#return_value' => $option_item->OrderTypeId .":" .$delivery_item->DeliveryId,
                '#default_value' => NULL, 
                '#attributes' => array('name' => array('settings[supertext-job]')),
		      ); 
          }
         
        }
      }
            
      return $settings;

    } else {
      $job->addMessage("Could not get quote from Supertext. Response: @response",array('@response'=>$response->data));
      drupal_set_message(t("Could not get quote from Supertext. Error: @error",array('@error' => $response->error)),'error');
      
      return;
    }


  }
  
}
