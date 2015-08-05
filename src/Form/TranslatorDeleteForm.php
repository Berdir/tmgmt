<?php

/**
 * @file
 * Contains \Drupal\tmgmt\Form\TranslatorDeleteForm.
 */

namespace Drupal\tmgmt\Form;

use Drupal\Core\Entity\EntityDeleteForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides a form for deleting a content_entity_example entity.
 *
 * @ingroup content_entity_example
 */
class TranslatorDeleteForm extends EntityDeleteForm {

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    if (tmgmt_translator_busy($this->entity->id())) {
      $form_state->setErrorByName('tmgmt', t('This translator cannot be deleted as long as there are active jobs using it.'));
    }
  }
}
