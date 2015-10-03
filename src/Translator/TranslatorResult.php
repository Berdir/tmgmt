<?php

/**
 * @file
 * Contains Drupal\tmgmt\TranslatorResult.
 */

namespace Drupal\tmgmt\Translator;

/**
 * TMGMT Translator Result abstract class.
 */
abstract class TranslatorResult {

  /**
   * TRUE or FALSE for response.
   *
   * @var boolean
   */
  protected $success;

  /**
   * Message in case success is FALSE.
   *
   * @var string
   */
  protected $message;

  /**
   * Returns the object message.
   */
  public function getMessage() {
    $argumented_message = t($this->message);
    return $argumented_message;
  }

  /**
   * Returns the object state on success.
   */
  public function getSuccess() {
    return $this->success;
  }

  /**
   * Sets the value success to FALSE and sets the $message accordingly.
   *
   * @param string $message
   *   This is the value to be saved as message for object.
   */
  protected function setNo($message) {
    $this->success = FALSE;
    $this->message = $message;
  }

  /**
   * Sets the value success to TRUE.
   */
  protected function setYes() {
    $this->success = TRUE;
  }

  /**
   * Returns the object with TRUE.
   *
   * @return \Drupal\tmgmt\Translator\TranslatorResult
   *   This returns the instance of the object with desired values.
   */
  public static function yes() {
    $result = new static();
    $result->setYes();
    return $result;
  }

  /**
   * Returns the object with FALSE and a message.
   *
   * @return \Drupal\tmgmt\Translator\TranslatorResult
   *   This returns the instance of the object with desired values.
   */
  public static function no($message) {
    $result = new static();
    $result->setNo($message);
    return $result;
  }
}
