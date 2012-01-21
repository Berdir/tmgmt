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
    protected $sandboxURL = 'http://dev.supertext.ch/api/v1/';

    /**
     *    requestFromSupertext($endpoint, $data = array())
     *
     *    Builds, signs and fires a request to mySupertext's API. Will fire to a different url
     *    if on the sandbox.
     *
     *    Authentication rituals are documented here: http://mygengo.com/api/developer-docs/authentication/
     *
     *    It should be noted that the authentication ritual listed there is different from the one here (v1 vs v1.1,
     *    respectively). v1.1 doesn't require signing on the entire data structure, just a timestamp and keys.
     */
    public function requestFromSupertext($endpoint, $method, $data = array()) {
        $options = array(
            'headers' => array(
                'User-Agent' => 'Drupal Supertext Translation Interface v0.1',
                'Accept' => 'application/json'
            ),
            'method' => $method
        );

        $timestamp = gmdate('U');
        $url = (variable_get('tmgmt_supertext_sandbox', TRUE) ? $this->sandboxURL : $this->apiURL) . $endpoint;
        
        /**
         *    If it's a GET or DELETE request, just sign it and send it appropriately. PUT/POST
         *    get a little more annoying...
         */
        if($method == 'GET' || $method == 'DELETE') { 
            $query_data = drupal_http_build_query(array_merge(array(
                'api_key' => $this->getOption('api_public_key'),
                'api_sig' => hash_hmac('sha1', $timestamp, $this->getOption('api_private_key')),
                'ts' => $timestamp
            ), $data));

            $response = drupal_http_request($url .'?'. $query_data, $options);
        } else {
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
        if($results->opstat == 'ok' && isset($results->response)) {
            return $results->response;
        } else {
            // TODO: Raise an actual error. >_>
            return array();
        }
    }

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
        $object = new stdClass();
        $object->AccountId = variable_get('tmgmt_supertext_api_account_id', '');
	    $object->Password = variable_get('tmgmt_supertext_api_password', '');
		$object->CallbackUrl = url('tmgmt_supertext_callback', array('absolute' => TRUE));
		$object->CommunicationLang = "en-US";
		$object->ContentType = "text/plain";
		$object->Currency = "chf";
		$object->OrderTypeId = "7";
		$object->DeliveryId = "1";
		$object->SourceLang = $job->source_language;
		$object->TargetLang = "en-US";
		$object->ReferenceData = $job->tjid . ':' . tmgmt_supertext_hash($job->tjid);
		$object->OrderTitle = $job->label;
		$object->Groups = array();
		
	    $items = $job->getItems();
            // @todo check to use $job->getSourceData();

		foreach ($items as $tjiid => $item) {
			$itemSourceData = $item->getSourceData();
			$fields = tmgmt_flatten_data($itemSourceData);
			$group = new stdClass();
			$group->GroupID = $tjiid;
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
				
		$options = array(
                'method' => 'POST',
                'data' => json_encode($object),
                'headers' => array('Content-Type' => 'application/json'),
        );
		
        $job->addMessage("Sending request to supertext");
        
		$response = drupal_http_request($this->sandboxURL.'/translation/order', $options);
//		dpm($response);

		if ($response->code == "200") {
//			$job->submitted("Job sent to Supertext. Response: @response",array('@response'=>$repsonse->data));
			$responsedata = json_decode($response->data);
			drupal_set_message(t("Job successfully sent to Supertext. <br />OrderID: @orderid<br />Price: @price<br />Wordcount: @wordcount<br />Deadline: @deadline",array(
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
        $results = array();
        $targets = $this->requestFromSupertext('translate/service/language_pairs', 'GET', array(
            'lc_src' => $source_language
        ));

        foreach($targets as $target) {
            array_push($results, $target->lc_tgt);
        }

        return $results;
    }

    /**
     * Returns array of service supported languages.
     *
     * @return array
     */
    public function getSupportedLanguages() {
        $languages = &drupal_static(__FUNCTION__);
        if(!isset($languages)) {
            $supported_languages = $this->requestFromSupertext('translate/service/languages', 'GET');
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
	  $form['api_account_id'] = array(
	    '#type' => 'textfield',
	    '#title' => t('Supertext API Account Id'),
	    '#default_value' => $this->getOption('api_account_id', ''),
	    '#description' => t('Please enter yout supertext API Account Id.'),
	  );
	
	  $form['api_password'] = array(
	    '#type' => 'textfield',
	    '#title' => t('Supertext API password'),
	    '#default_value' => $this->getOption('api_password', ''),
	    '#description' => t('Please enter your Supertext API password.'),
	  );
	  
	  $form['use_sandbox'] = array(
	    '#type' => 'checkbox',
	    '#title' => t('Use Sandbox'),
	    '#default_value' => $this->getOption('use_sandbox', TRUE),
	  );
      return $form;
    }

}
