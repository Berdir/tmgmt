<?php
/**
 * @file
 * Contains \Drupal\tmgmt_test\TestController.
 */

namespace Drupal\tmgmt_test;

use Drupal\Core\Controller\ControllerBase;
use Drupal\tmgmt\Entity\JobItem;

/**
 * Test controller.
 */
class TestController extends ControllerBase {

  /**
   * Callback to add given job item into the cart.
   */
  function addToCart(JobItem $tmgmt_job_item) {
    tmgmt_ui_cart_get()->addExistingJobItems(array($tmgmt_job_item));
  }
} 
