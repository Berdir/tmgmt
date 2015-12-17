<?php

/**
 * @file
 * Contains \Drupal\tmgmt_local\Form\LocalTaskItemForm.
 */

namespace Drupal\tmgmt_local\Form;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\tmgmt_local\Entity\LocalTaskItem;

/**
 * Form controller for the localTaskItem edit forms.
 *
 * @ingroup tmgmt_local_task_item
 */
class LocalTaskItemForm extends ContentEntityForm {

  /**
   * The task item.
   *
   * @var \Drupal\tmgmt_local\Entity\LocalTaskItem
   */
  protected $entity;

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $task_item = $this->entity;

    $form_state->set('task', $task_item->getTask());
    $form_state->set('task_item', $task_item);
    $form_state->set('job_item', $job_item = $task_item->getJobItem());

    $job = $job_item->getJob();

    if ($job->getSetting('job_comment')) {
      $form['job_comment'] = array(
        '#type' => 'item',
        '#title' => t('Job comment'),
        '#markup' => Xss::filter($job->getSetting('job_comment')),
      );
    }

    $form['translation'] = array(
      '#type' => 'container',
    );

    // Build the translation form.
    $data = $job_item->getData();

    // Need to keep the first hierarchy. So flatten must take place inside
    // of the foreach loop.
    $zebra = 'even';
    // Reverse the order to get the correct order.
    foreach (array_reverse(Element::children($data)) as $key) {
      $flattened = \Drupal::service('tmgmt.data')->flatten($data[$key], $key);
      $form['translation'][$key] = $this->formElement($flattened, $task_item, $zebra);
    }

    // Add the form actions as well.
    $form['actions']['#type'] = 'actions';
    $form['actions']['save_as_completed'] = array(
      '#type' => 'submit',
      '#validate' => ['::validateSaveAsComplete'],
      '#submit' => ['::save', '::saveAsComplete'],
      '#value' => t('Save as completed'),
    );
    $form['actions']['save'] = array(
      '#type' => 'submit',
      '#submit' => ['::save'],
      '#value' => t('Save'),
    );

    return $form;
  }

  /**
   * Builds a translation form element.
   *
   * @param array $data
   *   Data of the translation.
   * @param LocalTaskItem $item
   *   The LocalTaskItem.
   * @param string $zebra
   *   Tell is translation is odd or even.
   *
   * @return array
   *   Render array with the translation element.
   */
  private function formElement(array $data, LocalTaskItem $item, &$zebra) {
    static $flip = array(
      'even' => 'odd',
      'odd' => 'even',
    );

    $form = [];

    $job = $item->getJobItem()->getJob();
    $language_list = \Drupal::languageManager()->getLanguages();

    foreach (Element::children($data) as $key) {
      $form = [];
      if (isset($data[$key]['#text']) && \Drupal::service('tmgmt.data')->filterData($data[$key])) {
        // The char sequence '][' confuses the form API so we need to replace
        // it.
        $target_key = str_replace('][', '|', $key);
        $zebra = $flip[$zebra];
        $form[$target_key] = array(
          '#tree' => TRUE,
          '#theme' => 'tmgmt_local_translation_form_element',
          '#ajaxid' => Html::getUniqueId('tmgmt-local-element-' . $key),
          '#parent_label' => $data[$key]['#parent_label'],
          '#zebra' => $zebra,
        );

        $source_language = $language_list[$job->getSourceLangcode()];
        $target_language = $language_list[$job->getTargetLangcode()];

        $form[$target_key]['source'] = array(
          '#type' => 'textarea',
          '#title' => $source_language->getName(),
          '#value' => $data[$key]['#text'],
          '#disabled' => TRUE,
          '#allow_focus' => TRUE,
        );

        $form[$target_key]['translation'] = array(
          '#type' => 'textarea',
          '#title' => $target_language->getName(),
          '#default_value' => $item->getData(\Drupal::service('tmgmt.data')->ensureArrayKey($key), '#text'),
        );

        $form[$target_key]['actions'] = array(
          '#type' => 'container',
        );
        $status = $item->getData(\Drupal::service('tmgmt.data')->ensureArrayKey($key), '#status');
        $completed = $status == TMGMT_DATA_ITEM_STATE_TRANSLATED;
        if ($completed) {
          $form[$target_key]['actions']['reject-' . $target_key] = array(
            '#type' => 'submit',
            // Unicode character &#x2717 BALLOT X.
            '#value' => '✗',
            '#attributes' => array('title' => t('Reject')),
            '#name' => 'reject-' . $target_key,
            '#submit' => array('tmgmt_local_translation_form_update_state_submit'),
            '#ajax' => array(
              'callback' => 'tmgmt_local_translation_form_update_state_ajax',
              'wrapper' => $form[$target_key]['#ajaxid'],
            ),
            '#tmgmt_local_action' => 'reject',
            '#tmgmt_local_key' => str_replace('][', '|', $key),
          );
        }
        else {
          $form[$target_key]['actions']['finish-' . $target_key] = array(
            '#type' => 'submit',
            // Unicode character &#x2713 CHECK MARK.
            '#value' => '✓',
            '#attributes' => array('title' => t('Finish')),
            '#name' => 'finish-' . $target_key,
            '#submit' => array('tmgmt_local_translation_form_update_state_submit'),
            '#ajax' => array(
              'callback' => 'tmgmt_local_translation_form_update_state_ajax',
              'wrapper' => $form[$target_key]['#ajaxid'],
            ),
            '#tmgmt_local_action' => 'finish',
            '#tmgmt_local_key' => str_replace('][', '|', $key),
          );
        }
      }
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    parent::save($form, $form_state);

    /** @var LocalTaskItem $task_item */
    $task_item = $form_state->get('task_item');
    foreach ($form_state->getValues() as $key => $value) {
      if (is_array($value) && isset($value['translation'])) {
        $update['#text'] = $value['translation'];
        $task_item->updateData($key, $update);
      }
    }
    $task_item->save();

    $task = $task_item->getTask();
    $uri = $task->urlInfo();
    $form_state->setRedirect($uri->getRouteName(), $uri->getRouteParameters());
  }

  /**
   * Form submit callback for save as completed submit action.
   *
   * Change items to needs review state and task to completed status.
   */
  public function saveAsComplete(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\tmgmt_local\Entity\LocalTask $task */
    $task = $form_state->get('task');

    /** @var LocalTaskItem $task_item */
    $task_item = $form_state->get('task_item');
    $task_item->completed();
    $task_item->save();

    // Mark the task as completed if all assigned job items are at needs done.
    $all_done = TRUE;
    foreach ($task->getItems() as $item) {
      if ($item->isPending()) {
        $all_done = FALSE;
        break;
      }
    }
    if ($all_done) {
      $task->setStatus(TMGMT_LOCAL_TASK_STATUS_COMPLETED);
      // If the task is now completed, redirect back to the overview.
      $form_state->setRedirect('translate');
    }
    else {
      // If there are more task items, redirect back to the task.
      $uri = $task->urlInfo();
      $form_state->setRedirect($uri->getRouteName(), $uri->getRouteParameters());
    }

    /** @var \Drupal\tmgmt\Entity\JobItem $job_item */
    $job_item = $form_state['job_item'];

    // Add the translations to the job item.
    $job_item->addTranslatedData($task_item->getData());
  }

  /**
   * Form validate callback for save as completed submit action.
   *
   * Verify that all items are translated.
   */
  public function validateSaveAsComplete(array &$form, FormStateInterface $form_state) {
    // Loop over all data items and verify that there is a translation in there.
    foreach ($form_state['values'] as $key => $value) {
      if (is_array($value) && isset($value['translation'])) {
        if (empty($value['translation'])) {
          $form_state->setErrorByName($key . '[translation]', t('Missing translation.'));
        }
      }
    }
  }

}
