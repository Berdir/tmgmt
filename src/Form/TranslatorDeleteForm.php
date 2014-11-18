<?php

/**
 * @file
 * Contains \Drupal\tmgmt\Form\TranslatorDeleteForm.
 */

namespace Drupal\tmgmt\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\Core\Entity\EntityConfirmFormBase;

/**
 * Provides a form for deleting a content_entity_example entity.
 *
 * @ingroup content_entity_example
 */
class TranslatorDeleteForm extends EntityConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return t('Are you sure you want to delete Translator %name?', array('%name' => $this->entity->label()));
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelURL() {
    return new Url('tmgmt.translator_list');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return t('Delete');
  }

  /**
   * {@inheritdoc}
   *
   * Delete the entity and log the event. log() replaces the watchdog.
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    $this->entity->delete();

    \Drupal::logger('tmgmt')->info('@type: deleted %title.',
      array(
        '@type' => $this->entity->bundle(),
        '%title' => $this->entity->label(),
      ));

    $form_state->setRedirect('tmgmt.translator_list');
  }

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
