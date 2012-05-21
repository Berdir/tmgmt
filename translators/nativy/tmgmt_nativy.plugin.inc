<?php

/**
 * @file
 * Provides the Nativy Translator plugin controller.
 */

/**
 * Nativy translator plugin controller.
 */
class TMGMTNativyTranslatorPluginController extends TMGMTDefaultTranslatorPluginController {

  protected $live_site = 'http://nativy.com/';
  protected $test_site = 'http://test.nativy.com/';

  /**
   * Call a REST method from Nativy Connect.
   *
   * @param $translator
   *   Translator object.
   * @param $method
   *   The REST method to call.
   * @param $query
   *   Arguments for the method.
   *
   * @return
   *   The service response.
   */
  protected function get(TMGMTTranslator $translator, $method, array $query = array()) {
    $data = $this->request(url($this->getURL($translator, $method), array('query' => $query, 'absolute' => TRUE)));
    return json_decode($data);
  }

  /**
   * Executes and logs a request.
   *
   * @param $url
   *   The URL that should be requested.
   * @param $options
   *   Options to pass to drupal_http_request().
   *
   * @return
   *   The JSON
   */
  protected function request($url, $options = array()) {
    $start = microtime(TRUE);
    $request = drupal_http_request($url, $options);

    $headers = '';
    if (!empty($request->headers)) {
      foreach ($request->headers as $name => $value) {
        $headers .= "$name: $value\n";
      }
    }

    db_insert('tmgmt_nativy_log')
      ->fields(array(
        'url' => $url,
        'request' => $request->request,
        'response_code' => $request->code,
        'response_data' => isset($request->data) ? $request->data : '',
        'response_headers' => $headers,
        'time' => microtime(TRUE) - $start,
      ))
      ->execute();

    return isset($request->data) ? $request->data : '';
  }

  /**
   * Executes a post request.
   *
   * @param $translator
   *   The translator entity.
   * @param $method
   *   The method to call.
   * @param $query
   *   The POST data to submit.
   *
   * @return
   *   The returned, json decoded object.
   */
  protected function post(TMGMTTranslator $translator, $method, $query = array()) {
    $url = url($this->getURL($translator, $method), array($query));
    $options = array(
      'headers' => array(
        'Content-type' => 'application/json',
      ),
      'method' => 'POST',
      'data' => $query,
    );
    return json_decode($this->request($url, $options));
  }

  /**
   * Returns the URL for a given method.
   *
   * @param $translator
   *   The translator entity.
   * @param $method
   *   The method to call.
   *
   * @return
   *   The absolute URL to call.
   */
  protected function getURL(TMGMTTranslator $translator, $method) {
    $url = $translator->getSetting('use_test') ? $this->test_site : $this->live_site;
    $url .= 'connect/' . $method;
    return $url;
  }

  /**
   * Returns an array of the required authentication query arguments.
   *
   * @param $translator
   *   The translator entity.
   */
  protected function getAuthenticationQuery(TMGMTTranslator $translator) {
    $api_sign = hash_hmac('sha1', REQUEST_TIME, $translator->getSetting('private_key'));
    return array(
      'api_key' => $translator->getSetting('api_key'),
      'api_sign' => $api_sign,
      'timestamp' => REQUEST_TIME,
    );
  }

  /**
   * Verifies the api_sign.
   *
   * @param $translator
   *   The translator entity.
   * @param $timestamp
   *   The timestamp that has been signed.
   * @param $api_sign
   *   The sign.
   *
   * @return
   *   TRUE if the sign is correct.
   */
  public function verify(TMGMTTranslator $translator, $timestamp, $api_sign) {
    return strtolower($api_sign) == strtolower(hash_hmac('sha1', $timestamp, $translator->getSetting('private_key')));
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::isAvailable().
   */
  public function isAvailable(TMGMTTranslator $translator) {
    if ($translator->getSetting('api_key') && $translator->getSetting('private_key')) {
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
    $rank_details = $job->getSetting('rank_details');
    $redirect_path = current_path();
    // Attempt to find a better redirect path if tmgmt_ui module is available.
    if (module_exists('tmgmt_ui')) {
      $queue_destination = tmgmt_ui_redirect_queue_destination();
      $redirect_path = $queue_destination ? $queue_destination : $redirect_path;
    }
    $payment_link = $rank_details[$job->getSetting('rank')]['payment_link'] . '&returnlink=' . urlencode(url($redirect_path, array('absolute' => TRUE)));
    $job->reference = $rank_details[$job->getSetting('rank')]['orderId'];
    $job->submitted('Proceed to the <a href="!url">payment page</a> to submit this job.', array('!url' => $payment_link));
  }

  /**
   * Returns an order from a job
   */
  public function getOrder(TMGMTJob $job) {
    $order = $this->getAuthenticationQuery($job->getTranslator());
    $order['callbacklink'] = url('tmgmt/nativy/callback/' . $job->tjid, array('absolute' => TRUE));
    $order['language_from'] = $job->source_language;
    $order['language_to'] = $job->target_language;
    $order['nativy_partner_name'] = 'Drupal TMGMT';

    // Add selected options.
    foreach (array('short_description', 'long_description', 'correction_required') as $option) {
      $order[$option] = $job->getSetting($option);
    }
    $order += $job->getSetting('weighting');

    $data = array_filter(tmgmt_flatten_data($job->getData()), '_tmgmt_filter_data');
    $texts = array();
    foreach ($data as $key => $text) {
      $texts[] = array(
        'Key' => $key,
        'Value' => $text['#text'],
      );
    }
    $order['text_from'] = $texts;

    $response = $this->post($job->getTranslator(), 'order', json_encode($order));
    return $response;
  }

  /**
   * Retrieves an order object from the Nativy service.
   *
   * @param TMGMTJob $job
   *   The job for which the order should be retrieved.
   *
   * @return
   *   The nativy order object.
   */
  public function nativyRetrieveOrder(TMGMTJob $job) {
    $query = $this->getAuthenticationQuery($job->getTranslator());
    return $this->get($job->getTranslator(), 'order/' . $job->reference, $query);
  }

  /**
   * Retrieve job when ready (we got an email with a link?)
   */
  public function retrieveTranslation(TMGMTJob $job) {
    if ($document = $this->nativyRetrieveOrder($job)) {
      $translation = array();
      foreach ($document->text_to as $text) {
        $key = $text->Key;
        $text = $text->Value;
        $translation[$key] = array(
          '#text' => $text,
        );
      }
      $job->addTranslatedData(tmgmt_unflatten_data($translation));
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Implements TMGMTTranslatorPluginControllerInterface::getSupportedTargetLanguages().
   */
  public function getSupportedTargetLanguages(TMGMTTranslator $translator, $source_language) {
    global $language;
    $response = $this->get($translator, 'languages', array('language_short' => $language->language));
    $languages = array();
    foreach ($response as $language_row) {
      $languages[$language_row->language_short] = $language_row->language_short;
    }
    return $languages;
  }

}
