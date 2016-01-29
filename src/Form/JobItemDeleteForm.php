<?php

/**
 * @file
 * Contains \Drupal\tmgmt\Form\JobItemDeleteForm.
 */

namespace Drupal\tmgmt\Form;

use Drupal\Core\Entity\ContentEntityDeleteForm;

/**
 * Provides a confirmation delete form for 'tmgmt_job_item' entity.
 */
class JobItemDeleteForm extends ContentEntityDeleteForm {

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return $this->getEntity()->urlInfo();
  }

  /**
   * {@inheritdoc}
   */
  protected function getRedirectUrl() {
    return $this->getEntity()->getJob()->urlInfo();
  }

}
