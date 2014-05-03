<?php

/**
 * @file
 * Contains \Drupal\tmgmt\Form\JobItemForm.
 */

namespace Drupal\tmgmt\Form;

use Drupal\Component\Utility\NestedArray;
use Drupal\tmgmt\Entity\JobItem;
use Drupal\tmgmt\TranslatorRejectDataInterface;

/**
 * Form controller for the job item edit forms.
 *
 * @ingroup tmgmt_job
 */
class JobItemForm extends TmgmtFormBase {

  /**
   * @var \Drupal\tmgmt\Entity\JobItem
   */
  protected $entity;

  /**
   * Overrides Drupal\Core\Entity\EntityForm::form().
   */
  public function form(array $form, array &$form_state) {
    $form = parent::form($form, $form_state);
    $item = $this->entity;
    $form['info'] = array(
      '#type' => 'container',
      '#attributes' => array('class' => array('tmgmt-ui-job-info', 'clearfix')),
      '#weight' => 0,
    );

    $uri = $item->getSourceUri();
    $form['info']['source'] = array(
      '#type' => 'item',
      '#title' => t('Source'),
      '#markup' => l($item->getSourceLabel(), $uri['path'], $uri['options']),
      '#prefix' => '<div class="tmgmt-ui-source tmgmt-ui-info-item">',
      '#suffix' => '</div>',
    );

    $form['info']['sourcetype'] = array(
      '#type' => 'item',
      '#title' => t('Source type'),
      '#markup' => $item->getSourceType(),
      '#prefix' => '<div class="tmgmt-ui-source-type tmgmt-ui-info-item">',
      '#suffix' => '</div>',
    );

    $form['info']['changed'] = array(
      '#type' => 'item',
      '#title' => t('Last change'),
      '#markup' => format_date($item->changed),
      '#prefix' => '<div class="tmgmt-ui-changed tmgmt-ui-info-item">',
      '#suffix' => '</div>',
    );
    $states = tmgmt_job_item_states();
    $form['info']['state'] = array(
      '#type' => 'item',
      '#title' => t('State'),
      '#markup' => $states[$item->state],
      '#prefix' => '<div class="tmgmt-ui-item-state tmgmt-ui-info-item">',
      '#suffix' => '</div>',
      '#value' => $item->state,
    );
    $job = $item->getJob();
    $uri = $job->uri();
    $form['info']['job'] = array(
      '#type' => 'item',
      '#title' => t('Job'),
      '#markup' => l($job->label(), $uri['path']),
      '#prefix' => '<div class="tmgmt-ui-job tmgmt-ui-info-item">',
      '#suffix' => '</div>',
    );

    // Display selected translator for already submitted jobs.
    if (!$item->getJob()->isSubmittable()) {
      $translators = tmgmt_translator_labels();
      $form['info']['translator'] = array(
        '#type' => 'item',
        '#title' => t('Translator'),
        '#markup' => isset($translators[$item->getJob()->translator]) ? check_plain($translators[$item->getJob()->translator]) : t('Missing translator'),
        '#prefix' => '<div class="tmgmt-ui-translator tmgmt-ui-info-item">',
        '#suffix' => '</div>',
      );
    }

    // Actually build the review form elements...
    $form['review'] = array(
      '#type' => 'container',
    );
    // Build the review form.
    $data = $item->getData();
    // Need to keep the first hierarchy. So flatten must take place inside
    // of the foreach loop.
    $zebra = 'even';
    foreach (element_children($data) as $key) {
      $form['review'][$key] = $this->reviewFormElement($form_state, tmgmt_flatten_data($data[$key], $key), $item, $zebra, $key);
    }

    if ($view =  entity_load('view', 'tmgmt_ui_job_item_messages')) {
      $form['messages'] = array(
        '#type' => 'fieldset',
        '#title' => $view->label(),
        '#collapsible' => TRUE,
        '#weight' => 50,
      );
      $form['messages']['view'] = $view->getExecutable()->preview('block', array($item->id()));
    }

    $form['#attached']['css'][] = drupal_get_path('module', 'tmgmt_ui') . '/css/tmgmt_ui.admin.css';
    // The reject functionality has to be implement by the translator plugin as
    // that process is completely unique and custom for each translation service.

    // Give the source ui controller a chance to affect the review form.
    $source = $this->sourceManager->createUIInstance($item->plugin);
    $form = $source->reviewForm($form, $form_state, $item);
    // Give the translator ui controller a chance to affect the review form.
    if ($item->getTranslator()) {
      $plugin_ui = $this->translatorManager->createUIInstance($item->getTranslator()->plugin);
      $form = $plugin_ui->reviewForm($form, $form_state, $item);
    }

    return $form;
  }

  protected function actions(array $form, array &$form_state) {
    $item = $this->entity;

    // Add the form actions as well.
    $actions['accept'] = array(
      '#type' => 'submit',
      '#value' => t('Save as completed'),
      '#access' => $item->isNeedsReview(),
      '#validate' => array(
        array($this, 'validate'),
      ),
      '#submit' => array(
        array($this, 'submit'),
        array($this, 'save'),
      ),
    );
    $actions['save'] = array(
      '#type' => 'submit',
      '#value' => t('Save'),
      '#access' => !$item->isAccepted(),
      '#validate' => array(
        array($this, 'validate'),
      ),
      '#submit' => array(
        array($this, 'submit'),
        array($this, 'save'),
      ),
    );
    $uri = $item->getJob()->uri();
    $url = isset($_GET['destination']) ? $_GET['destination'] : $uri['path'];
    $actions['cancel'] = array(
      '#type' => 'link',
      '#title' => t('Cancel'),
      '#href' => $url,
    );
    return $actions;
  }


  /**
   * {@inheritdoc}
   */
  public function validate(array $form, array &$form_state) {
    parent::validate($form, $form_state);
    $item = $this->buildEntity($form, $form_state);
    // First invoke the validation method on the source controller.
    $source_ui = $this->sourceManager->createUIInstance($item->plugin);
    $source_ui->reviewFormValidate($form, $form_state, $item);
    // Invoke the validation method on the translator controller (if available).
    if($item->getTranslator()){
      $translator_ui = $this->translatorManager->createUIInstance($item->getTranslator()->plugin);
      $translator_ui->reviewFormValidate($form, $form_state, $item);
    }
  }

  /**
   * Overrides Drupal\Core\Entity\EntityForm::save().
   */
  public function save(array $form, array &$form_state) {
    $item = $this->entity;
    // First invoke the submit method on the source controller.
    $source_ui = $this->sourceManager->createUIInstance($item->plugin);
    $source_ui->reviewFormSubmit($form, $form_state, $item);
    // Invoke the submit method on the translator controller (if available).
    if ($item->getTranslator()){
      $translator_ui = $this->translatorManager->createUIInstance($item->getTranslator()->plugin);
      $translator_ui->reviewFormSubmit($form, $form_state, $item);
    }
    // Write changes back to item.
    foreach ($form_state['values'] as $key => $value) {
      if (is_array($value) && isset($value['translation'])) {
        // Update the translation, this will only update the translation in case
        // it has changed.
        $data = array(
          '#text' => $value['translation'],
          '#origin' => 'local',
        );
        $item->addTranslatedData($data, $key);
      }
    }
    // Check if the user clicked on 'Accept', 'Submit' or 'Reject'.
    if (!empty($form['actions']['accept']) && $form_state['triggering_element']['#value'] == $form['actions']['accept']['#value']) {
      $item->acceptTranslation();
      // Check if the item could be saved and accepted successfully and redirect
      // to the job item view if that is the case.
      if ($item->isAccepted()) {
        $form_state['redirect_route'] = $item->getJob()->urlInfo();
      }
      // Print all messages that have been saved while accepting the reviewed
      // translation.
      foreach ($item->getMessagesSince() as $message) {
        // Ignore debug messages.
        if ($message->type == 'debug') {
          continue;
        }
        if ($text = $message->getMessage()) {
          drupal_set_message(filter_xss($text), $message->type);
        }
      }
    }
    $item->save();
  }

  /**
   * {@inheritdoc}
   */
  public function delete(array $form, array &$form_state) {
    $entity = $this->entity;
    $form_state['redirect'] = 'admin/tmgmt/items/' . $entity->id() . '/delete';
  }


  /**
   * Build form elements for the review form using flatened data items.
   *
   * @todo Mention in the api documentation that the char '|' is not allowed in
   * field names.
   *
   * @param array $form_state
   *   Drupal form form_state object.
   * @param $data
   *   Flatened array of translation data items.
   * @param $job_item
   *   The job item related to this data.
   * @param $zebra
   *   String containing either odd or even. This is used to style the each
   *   translation item with alternating colors.
   * @param $parent_key
   *   The key for $data.
   */
  function reviewFormElement(&$form_state, $data, JobItem $job_item, &$zebra, $parent_key) {
    $flip = array(
      'even' => 'odd',
      'odd' => 'even',
    );
    $form = array(
      '#theme' => 'tmgmt_ui_translator_review_form',
      '#ajaxid' => tmgmt_ui_review_form_element_ajaxid($parent_key),
    );

    foreach (element_children($data) as $key) {
      // The char sequence '][' confuses the form API so we need to replace it.
      $target_key = str_replace('][', '|', $key);
      if (isset($data[$key]['#text']) && _tmgmt_filter_data($data[$key])) {
        $zebra = $flip[$zebra];
        $form[$target_key] = array(
          '#tree' => TRUE,
          '#theme' => 'tmgmt_ui_translator_review_form_element',
          '#parent_label' => $data[$key]['#parent_label'],
          '#zebra' => $zebra,
        );
        $form[$target_key]['status'] = array(
          '#theme' => 'tmgmt_ui_translator_review_form_element_status',
          '#value' => $job_item->isAccepted() ? TMGMT_DATA_ITEM_STATE_ACCEPTED : $data[$key]['#status'],
        );
        $form[$target_key]['actions'] = array(
          '#type' => 'container',
        );
        if (!$job_item->isAccepted()) {
          if ($data[$key]['#status'] != TMGMT_DATA_ITEM_STATE_REVIEWED) {
            $form[$target_key]['actions']['reviewed'] = array(
              '#type' => 'submit',
              // Unicode character &#x2713 CHECK MARK
              '#value' => '✓',
              '#attributes' => array('title' => t('Reviewed')),
              '#name' => 'reviewed-' . $target_key,
              '#submit' => array('tmgmt_ui_translation_review_form_update_state'),
              '#ajax' => array(
                'callback' => array($this, 'ajaxReviewForm'),
                'wrapper' => $form['#ajaxid'],
              ),
            );
          }
          else {
            $form[$target_key]['actions']['unreviewed'] = array(
              '#type' => 'submit',
              // Unicode character &#x2713 CHECK MARK
              '#value' => '✓',
              '#attributes' => array('title' => t('Not reviewed'), 'class' => array('unreviewed')),
              '#name' => 'unreviewed-' . $target_key,
              '#submit' => array('tmgmt_ui_translation_review_form_update_state'),
              '#ajax' => array(
                'callback' => array($this, 'ajaxReviewForm'),
                'wrapper' => $form['#ajaxid'],
              ),
            );
          }
          if ($job_item->getTranslatorController() instanceof TranslatorRejectDataInterface && $data[$key]['#status'] != TMGMT_DATA_ITEM_STATE_PENDING) {
            $form[$target_key]['actions']['reject'] = array(
              '#type' => 'submit',
              // Unicode character &#x2717 BALLOT X
              '#value' => '✗',
              '#attributes' => array('title' => t('Reject')),
              '#name' => 'reject-' . $target_key,
              '#submit' => array('tmgmt_ui_translation_review_form_update_state'),
            );
          }

          if (!empty($data[$key]['#translation']['#text_revisions'])) {
            $form[$target_key]['actions']['revert'] = array(
              '#type' => 'submit',
              // Unicode character U+21B6 ANTICLOCKWISE TOP SEMICIRCLE ARROW
              '#value' => '↶',
              '#attributes' => array('title' => t('Revert to previous revision')),
              '#name' => 'revert-' . $target_key,
              '#data_item_key' => $key,
              '#submit' => array('tmgmt_ui_translation_review_form_revert'),
              '#ajax' => array(
                'callback' => array(array($this, 'ajaxReviewForm')),
                'wrapper' => $form['#ajaxid'],
              ),
            );
          }
        }
        if (!empty($data[$key]['#translation']['#text_revisions'])) {
          $revisions = array();

          foreach ($data[$key]['#translation']['#text_revisions'] as $revision) {
            $revisions[] = t('Origin: %origin, Created: %created</br>%text', array(
              '%origin' => $revision['#origin'],
              '%created' => format_date($revision['#timestamp']),
              '%text' => filter_xss($revision['#text']),
            ));
          }
          $form[$target_key]['below']['revisions_wrapper'] = array(
            '#type' => 'fieldset',
            '#title' => t('Translation revisions'),
            '#collapsed' => TRUE,
            '#collapsible' => TRUE,
          );
          $form[$target_key]['below']['revisions_wrapper']['revisions'] = array(
            '#theme' => 'item_list',
            '#items' => $revisions,
          );
        }
        $form[$target_key]['translation'] = array(
          '#type' => 'textarea',
          '#default_value' => isset($data[$key]['#translation']['#text']) ? $data[$key]['#translation']['#text'] : NULL,
          '#title' => t('Translation'),
          '#disabled' => $job_item->isAccepted(),
        );
        $form[$target_key]['source'] = array(
          '#type' => 'textarea',
          '#default_value' => $data[$key]['#text'],
          '#title' => t('Source'),
          '#disabled' => TRUE,
        );

        // Give the translator ui controller a chance to affect the data item element.
        $form[$target_key] = \Drupal::service('plugin.manager.tmgmt.translator')->createUiInstance($job_item->getTranslator()->plugin)
          ->reviewDataItemElement($form[$target_key], $form_state, $key, $parent_key, $data[$key], $job_item);
        // Give the source ui controller a chance to affect the data item element.
        $form[$target_key] = \Drupal::service('plugin.manager.tmgmt.source')->createUIInstance($job_item->plugin)
          ->reviewDataItemElement($form[$target_key], $form_state, $key, $parent_key, $data[$key], $job_item);
      }
    }
    return $form;
  }

  /**
   * Ajax callback for the job item review form.
   */
  function ajaxReviewForm($form, &$form_state) {
    $key = array_slice($form_state['triggering_element']['#array_parents'], 0, 2);
    $render_data = NestedArray::getValue($form, $key);
    tmgmt_ui_write_request_messages($form_state['controller']->getEntity()->getJob());
    return drupal_render($render_data);
  }

}
