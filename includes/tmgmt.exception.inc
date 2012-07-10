<?php

/**
 * TMGMT Exception class
 */
class TMGMTException extends Exception {

  /**
   * @param string $message
   * @param array $data
   *   Associative array of dynamic data that will be inserted into $message.
   * @param int $code
   * @param Exception|null $previous
   */
  function __construct($message = "", $data = array(), $code = 0, Exception $previous = NULL) {
    parent::__construct(strtr($message, $data), $code, $previous);
  }
}
