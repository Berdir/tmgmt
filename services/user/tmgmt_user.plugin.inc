<?php

/**
 * @file
 * Provides the user translator plugin controller.
 */

/**
 * @ingroup translator
 */
class TMGMTUserTranslatorPluginController extends TMGMTDefaultTranslatorPluginController {

}

/**
 * Default controller class response.
 *
 * @translator
 */
class TMGMTUserTranslatorResponse implements TMGMTranslatorResponseInterface {

  /**
   * @var $response
   */
  protected $response;

  /**
   * Constructor.
   *
   * @param $response
   */
  public function __construct($response) {
    $this->response = $response;
  }

  /**
   * Implements TMGMTranslatorResponseInterface::getResponse().
   */
  public function getResponse() {
    return $this->response;
  }

  /**
   * Implements TMGMTranslatorResponseInterface::toStatus().
   */
  public function toStatus() {
    // @todo Return a status under the right circumstances.
  }

  /**
   * Implements TMGMTranslatorResponseInterface::toString().
   */
  public function toString() {
    // @todo Return a message.
  }
}
