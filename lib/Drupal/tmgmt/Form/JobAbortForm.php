<?php

/**
 * @file
 * Contains \Drupal\tmgmt\Form\JobAbortForm.
 */

namespace Drupal\tmgmt\Form;

use Drupal\Core\Entity\EntityConfirmFormBase;
use Drupal\Core\Url;

/**
 * Provides a form for deleting a node.
 */
class JobAbortForm extends EntityConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Abort this job?', array('%title' => $this->entity->label()));
  }

  public function getDescription() {
    return $this->t('This will send a request to the translator to abort the job. After the action the job translation process will be aborted and only remaining action will be resubmitting it.');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelRoute() {
    return $this->entity->urlInfo();

  }

  /**
   * {@inheritdoc}
   */
  public function submit(array $form, array &$form_state) {
    if (!$this->entity->abortTranslation()) {
      // This is the case when a translator does not support the abort operation.
      // It would make more sense to not display the button for the action,
      // however we do not know if the translator is able to abort a job until
      // we trigger the action.
      foreach ($this->entity->getMessagesSince() as $message) {
        if ($message->type == 'debug') {
          continue;
        }
        if ($text = $message->getMessage()) {
          // We want to persist also the type therefore we will set the
          // messages directly and not return them.
          drupal_set_message(filter_xss($text), $message->type);
        }
      }
    }
    $form_state['redirect_route'] = $this->entity->urlInfo();
  }

}
