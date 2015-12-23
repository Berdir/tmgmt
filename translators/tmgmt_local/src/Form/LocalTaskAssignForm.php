<?php

/**
 * @file
 * Contains \Drupal\tmgmt_local\Form\LocalTaskAssignForm.
 */

namespace Drupal\tmgmt_local\Form;

use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\user\Entity\User;
use Drupal\views\Views;

/**
 * Assign task confirmation form.
 */
class LocalTaskAssignForm extends ContentEntityForm {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    parent::buildForm($form, $form_state);

    $roles = tmgmt_local_translator_roles();
    if (empty($roles)) {
      drupal_set_message(t('No user role has the "provide translation services" permission. <a href="@url">Configure permissions</a> for the Local translator module.',
        array('@url' => URL::fromRoute('user.admin_permissions'))), 'warning');
    }

    $form['tuid'] = array(
      '#title' => t('Assign to'),
      '#type' => 'select',
      '#empty_option' => t('Select user'),
      '#options' => tmgmt_local_get_translators_for_tasks([$this->getEntity()->id()]),
      '#required' => TRUE,
    );

    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Assign tasks'),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);

    /** @var \Drupal\user\Entity\User $translator */
    $translator = User::load($form_state->getValue('tuid'));

    /** @var \Drupal\tmgmt_local\LocalTaskInterface $task */
    $task = $this->getEntity();
    $task->assign($translator);
    $task->save();

    drupal_set_message(t('Assigned @label to translator @translator_name.', array('@label' => $task->label(), '@translator_name' => $translator->getAccountName())));

    $form_state->setRedirect(Views::getView('tmgmt_local_task_overview')->getUrl()->getRouteName());
  }

}
