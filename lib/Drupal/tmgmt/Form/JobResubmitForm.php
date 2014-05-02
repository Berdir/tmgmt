<?php

/**
 * @file
 * Contains \Drupal\tmgmt\Form\JobResubmitForm.
 */

namespace Drupal\tmgmt\Form;

use Drupal\Core\Entity\EntityConfirmFormBase;
use Drupal\Core\Url;
use Drupal\tmgmt\Entity\Form\TMGMTJobItem;

/**
 * Provides a form for deleting a node.
 */
class JobResubmitForm extends EntityConfirmFormBase {

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Resubmit as a new job?', array('%title' => $this->entity->label()));
  }

  public function getDescription() {
    return $this->t('This creates a new job with the same items which can then be submitted again. In case the sources meanwhile changed, the new job will reflect the update.');
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
    $new_job = $this->entity->cloneAsUnprocessed();
    $new_job->uid = $this->currentUser()->id();
    $new_job->save();
    /** @var TMGMTJobItem $item */
    foreach ($this->entity->getItems() as $item) {
      $item_to_resubmit = $item->cloneAsActive();
      $new_job->addExistingItem($item_to_resubmit);
    }

    $this->entity->addMessage('Job has been duplicated as a new job <a href="@url">#@id</a>.',
      array('@url' => url('admin/tmgmt/jobs/' . $new_job->tjid), '@id' => $new_job->tjid));
    $new_job->addMessage('This job is a duplicate of the previously aborted job <a href="@url">#@id</a>',
      array('@url' => url('admin/tmgmt/jobs/' . $this->entity->tjid), '@id' => $this->entity->tjid));

    drupal_set_message(t('The aborted job has been duplicated. You can resubmit it now.'));
    $urlInfo = $new_job->urlInfo();
    $form_state['redirect_route'] = new Url($urlInfo['route_name'], $urlInfo['route_parameters']);
  }

}
