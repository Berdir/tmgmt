<?php

/**
 * @file
 * Contains \Drupal\tmgmt\Form\JobItemForm.
 */

namespace Drupal\tmgmt\Form;

use Drupal\Component\Utility\NestedArray;
use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Url;
use Drupal\tmgmt\Entity\JobItem;
use Drupal\tmgmt\JobItemInterface;
use Drupal\tmgmt\TranslatorRejectDataInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;

/**
 * Form controller for the job item edit forms.
 *
 * @ingroup tmgmt_job
 */
class JobItemForm extends TmgmtFormBase {

  /**
   * @var \Drupal\tmgmt\JobItemInterface
   */
  protected $entity;

  /**
   * Overrides Drupal\Core\Entity\EntityForm::form().
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $item = $this->entity;

    $form['#title'] = $this->t('Job item @source_label', array('@source_label' => $item->getSourceLabel()));

    $form['info'] = array(
      '#type' => 'container',
      '#attributes' => array('class' => array('tmgmt-ui-job-info', 'clearfix')),
      '#weight' => 0,
    );

    $url = $item->getSourceUrl();
    $form['info']['source'] = array(
      '#type' => 'item',
      '#title' => t('Source'),
      '#markup' => $url ? \Drupal::l($item->getSourceLabel(),$url) : $item->getSourceLabel(),
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
      '#value' => $item->getChangedTime(),
      '#markup' => format_date($item->getChangedTime()),
      '#prefix' => '<div class="tmgmt-ui-changed tmgmt-ui-info-item">',
      '#suffix' => '</div>',
    );
    $states = JobItem::getStates();
    $form['info']['state'] = array(
      '#type' => 'item',
      '#title' => t('State'),
      '#markup' => $states[$item->getState()],
      '#prefix' => '<div class="tmgmt-ui-item-state tmgmt-ui-info-item">',
      '#suffix' => '</div>',
      '#value' => $item->getState(),
    );
    $job = $item->getJob();
    $url = $job->urlInfo();
    $form['info']['job'] = array(
      '#type' => 'item',
      '#title' => t('Job'),
      '#markup' => \Drupal::l($job->label(), $url),
      '#prefix' => '<div class="tmgmt-ui-job tmgmt-ui-info-item">',
      '#suffix' => '</div>',
    );

    // Display selected translator for already submitted jobs.
    if (!$item->getJob()->isSubmittable()) {
      $translators = tmgmt_translator_labels();
      $form['info']['translator'] = array(
        '#type' => 'item',
        '#title' => t('Translator'),
        '#markup' => isset($translators[$item->getJob()->getTranslatorId()]) ? Html::escape($translators[$item->getJob()->getTranslatorId()]) : t('Missing translator'),
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
    foreach (Element::children($data) as $key) {
      $form['review'][$key] = $this->reviewFormElement($form_state, \Drupal::service('tmgmt.data')->flatten($data[$key], $key), $item, $zebra, $key);
    }

    if ($view =  entity_load('view', 'tmgmt_job_item_messages')) {
      $form['messages'] = array(
        '#type' => 'details',
        '#title' => $view->label(),
        '#open' => FALSE,
        '#weight' => 50,
      );
      $form['messages']['view'] = $view->getExecutable()->preview('block', array($item->id()));
    }

    $form['#attached']['library'][] = 'tmgmt/admin';
    // The reject functionality has to be implement by the translator plugin as
    // that process is completely unique and custom for each translation service.

    // Give the source ui controller a chance to affect the review form.
    $source = $this->sourceManager->createUIInstance($item->getPlugin());
    $form = $source->reviewForm($form, $form_state, $item);
    // Give the translator ui controller a chance to affect the review form.
    if ($item->getTranslator()) {
      $plugin_ui = $this->translatorManager->createUIInstance($item->getTranslator()->getPluginId());
      $form = $plugin_ui->reviewForm($form, $form_state, $item);
    }

    return $form;
  }

  protected function actions(array $form, FormStateInterface $form_state) {
    $item = $this->entity;

    // Add the form actions as well.
    $actions['accept'] = array(
      '#type' => 'submit',
      '#value' => t('Save as completed'),
      '#access' => $item->isNeedsReview(),
      '#submit' => array('::submitForm', '::save'),
    );
    $actions['save'] = array(
      '#type' => 'submit',
      '#value' => t('Save'),
      '#access' => !$item->isAccepted(),
      '#submit' => array('::submitForm', '::save'),
    );
    $actions['validate'] = array(
      '#type' => 'submit',
      '#value' => t('Validate HTML tags'),
      '#submit' => array('::submitForm', '::validateTags'),
    );
    $url = $item->getJob()->url();
    $url = isset($_GET['destination']) ? $_GET['destination'] : $url;
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
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    $item = $this->buildEntity($form, $form_state);
    // First invoke the validation method on the source controller.
    $source_ui = $this->sourceManager->createUIInstance($item->getPlugin());
    $source_ui->reviewFormValidate($form, $form_state, $item);
    // Invoke the validation method on the translator controller (if available).
    if($item->getTranslator()){
      $translator_ui = $this->translatorManager->createUIInstance($item->getTranslator()->getPluginId());
      $translator_ui->reviewFormValidate($form, $form_state, $item);
    }
  }

  /**
   * Overrides Drupal\Core\Entity\EntityForm::save().
   */
  public function save(array $form, FormStateInterface $form_state) {
    $item = $this->entity;
    // First invoke the submit method on the source controller.
    $source_ui = $this->sourceManager->createUIInstance($item->getPlugin());
    $source_ui->reviewFormSubmit($form, $form_state, $item);
    // Invoke the submit method on the translator controller (if available).
    if ($item->getTranslator()){
      $translator_ui = $this->translatorManager->createUIInstance($item->getTranslator()->getPluginId());
      $translator_ui->reviewFormSubmit($form, $form_state, $item);
    }
    // Write changes back to item.
    foreach ($form_state->getValues() as $key => $value) {
      if (is_array($value) && isset($value['translation'])) {
        // Update the translation, this will only update the translation in case
        // it has changed. We have two different cases, the first is for nested
        // texts.
        if (is_array($value['translation'])) {
          $data = array(
            '#text' => $value['translation']['value'],
            '#origin' => 'local',
          );
        }
        else {
          $data = array(
            '#text' => $value['translation'],
            '#origin' => 'local',
          );
        }

        $item->addTranslatedData($data, $key);
      }
    }
    // Check if the user clicked on 'Accept', 'Submit' or 'Reject'.
    if (!empty($form['actions']['accept']) && $form_state->getTriggeringElement()['#value'] == $form['actions']['accept']['#value']) {
      $item->acceptTranslation();
      // Check if the item could be saved and accepted successfully and redirect
      // to the job item view if that is the case.
      if ($item->isAccepted()) {
        $form_state->setRedirectUrl($item->getJob()->urlInfo());
      }
      // Print all messages that have been saved while accepting the reviewed
      // translation.
      foreach ($item->getMessagesSince() as $message) {
        // Ignore debug messages.
        if ($message->getType() == 'debug') {
          continue;
        }
        if ($text = $message->getMessage()) {
          drupal_set_message(Xss::filter($text), $message->getType());
        }
      }
    }
    $item->save();
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
  function reviewFormElement(FormStateInterface $form_state, $data, JobItemInterface $job_item, &$zebra, $parent_key) {
    $flip = array(
      'even' => 'odd',
      'odd' => 'even',
    );
    $form = array(
      '#theme' => 'tmgmt_translator_review_form',
      '#ajaxid' => tmgmt_review_form_element_ajaxid($parent_key),
    );

    foreach (Element::children($data) as $key) {
      // The char sequence '][' confuses the form API so we need to replace it.
      $target_key = str_replace('][', '|', $key);
      if (isset($data[$key]['#text']) && \Drupal::service('tmgmt.data')->filterData($data[$key])) {
        $zebra = $flip[$zebra];
        $form[$target_key] = array(
          '#tree' => TRUE,
          '#theme' => 'tmgmt_translator_review_form_element',
          '#parent_label' => $data[$key]['#parent_label'],
          '#zebra' => $zebra,
        );
        $form[$target_key]['status'] = array(
          '#theme' => 'tmgmt_translator_review_form_element_status',
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
              '#submit' => array('tmgmt_translation_review_form_update_state'),
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
              '#submit' => array('tmgmt_translation_review_form_update_state'),
              '#ajax' => array(
                'callback' => array($this, 'ajaxReviewForm'),
                'wrapper' => $form['#ajaxid'],
              ),
            );
          }
          if ($job_item->hasTranslator() && $job_item->getTranslatorPlugin() instanceof TranslatorRejectDataInterface && $data[$key]['#status'] != TMGMT_DATA_ITEM_STATE_PENDING) {
            $form[$target_key]['actions']['reject'] = array(
              '#type' => 'submit',
              // Unicode character &#x2717 BALLOT X
              '#value' => '✗',
              '#attributes' => array('title' => t('Reject')),
              '#name' => 'reject-' . $target_key,
              '#submit' => array('tmgmt_translation_review_form_update_state'),
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
              '#submit' => array('tmgmt_translation_review_form_revert'),
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
              '%text' => Xss::filter($revision['#text']),
            ));
          }
          $form[$target_key]['below']['revisions_wrapper'] = array(
            '#type' => 'details',
            '#title' => t('Translation revisions'),
            '#open' => TRUE,
          );
          $form[$target_key]['below']['revisions_wrapper']['revisions'] = array(
            '#theme' => 'item_list',
            '#items' => $revisions,
          );
        }

        // Manage the height of the texteareas, depending on the lenght of the
        // description. The minimum number of rows is 3 and the maximum is 15.
        $rows = ceil(strlen($data[$key]['#text']) / 100);
        if ($rows < 3) {
          $rows = 3;
        } elseif ($rows > 15) {
          $rows = 15;
        }
        if (!empty($data[$key]['#format']) && \Drupal::config('tmgmt.settings')->get('respect_text_format') == '1') {
          $form[$target_key]['translation'] = array(
            '#type' => 'text_format',
            '#default_value' => isset($data[$key]['#translation']['#text']) ? $data[$key]['#translation']['#text'] : NULL,
            '#title' => t('Translation'),
            '#disabled' => $job_item->isAccepted(),
            '#rows' => $rows,
            '#allowed_formats' => array($data[$key]['#format']),
          );
        }
        else {
          $form[$target_key]['translation'] = array(
            '#type' => 'textarea',
            '#default_value' => isset($data[$key]['#translation']['#text']) ? $data[$key]['#translation']['#text'] : NULL,
            '#title' => t('Translation'),
            '#disabled' => $job_item->isAccepted(),
            '#rows' => $rows,
          );
        }

        if (!empty($data[$key]['#format']) && \Drupal::config('tmgmt.settings')->get('respect_text_format') == '1') {
          $form[$target_key]['source'] = array(
            '#type' => 'text_format',
            '#default_value' => $data[$key]['#text'],
            '#title' => t('Source'),
            '#disabled' => TRUE,
            '#rows' => $rows,
            '#allowed_formats' => array($data[$key]['#format']),
          );
        }
        else {
          $form[$target_key]['source'] = array(
            '#type' => 'textarea',
            '#default_value' => $data[$key]['#text'],
            '#title' => t('Source'),
            '#disabled' => TRUE,
            '#rows' => $rows,
          );
        }

        if(isset($form_state->get('validation_messages')[$target_key])) {
          $form[$target_key]['validation_message'] = array(
            '#title' => t('Validation message'),
            '#type' => 'item',
            '#value' =>  htmlspecialchars($form_state->get('validation_messages')[$target_key]),
          );
        }

        // Give the translator ui controller a chance to affect the data item element.
        if ($job_item->hasTranslator()) {
          $form[$target_key] = \Drupal::service('plugin.manager.tmgmt.translator')
            ->createUIInstance($job_item->getTranslator()->getPluginId())
            ->reviewDataItemElement($form[$target_key], $form_state, $key, $parent_key, $data[$key], $job_item);
          // Give the source ui controller a chance to affect the data item element.
          $form[$target_key] = \Drupal::service('plugin.manager.tmgmt.source')
            ->createUIInstance($job_item->getPlugin())
            ->reviewDataItemElement($form[$target_key], $form_state, $key, $parent_key, $data[$key], $job_item);
        }
      }
    }
    return $form;
  }

  /**
   * Ajax callback for the job item review form.
   */
  function ajaxReviewForm(array $form, FormStateInterface $form_state) {
    $key = array_slice($form_state->getTriggeringElement()['#array_parents'], 0, 2);
    $render_data = NestedArray::getValue($form, $key);
    tmgmt_write_request_messages($form_state->getFormObject()->getEntity()->getJob());
    return $render_data;
  }

  /**
   * Submit handler for the HTML tag validation.
   */
  function validateTags(array $form, FormStateInterface $form_state) {
    $validation_messages = array();
    $field_count = 0;
    foreach ($form_state->getValues() as $field => $value) {
      if (is_array($value) && isset($value['translation'])) {
        if (!empty($value['translation'])) {
          $tags_validated = $this->compareHTMLTags($value['source'], $value['translation']);
          if ($tags_validated) {
            $validation_messages[$field] = $tags_validated;
            $field_count++;
          }
        }
      }
    }
    if($field_count > 0){
      drupal_set_message(t('HTML tag validation failed for @count field(s).', array('@count' => $field_count)), 'error');
    }
    $form_state->set('validation_messages', $validation_messages);
    $request = \Drupal::request();
    $url = $this->entity->urlInfo('canonical');
    if ($request->query->has('destination')) {
      $destination = $request->query->get('destination');
      $request->query->remove('destination');
      $url->setOption('query', array('destination' => $destination));
    }
    $form_state->setRedirectUrl($url);
    $form_state->setRebuild();
  }

  /**
   * Compare the HTML tags of source and translation.
   * @param string $source
   *  Source text.
   * @param string $translation
   *  Translated text.
   */
  function compareHTMLTags($source, $translation) {
    $pattern = "/\<(.*?)\>/";
    preg_match_all($pattern, $source, $source_tags);
    preg_match_all($pattern, $translation, $translation_tags);
    $message = '';
    if ($source_tags != $translation_tags) {
      if (count($source_tags[0]) == count($translation_tags[0])) {
        $message .= 'Order of the HTML tags are incorrect. ';
      }
      else {
        $tags = implode(',', array_diff($source_tags[0], $translation_tags[0]));
        if (!empty($tags)) {
          $message .= 'Expected tags ' . $tags . ' not found. ';
        }
        $source_tags_count = $this->htmlTagCount($source_tags[0]);
        $translation_tags_count = $this->htmlTagCount($translation_tags[0]);
        $difference = array_diff_assoc($source_tags_count, $translation_tags_count);
        foreach ($difference as $tag => $count) {
          if (!isset($translation_tags_count[$tag])) {
            $translation_tags_count[$tag] = 0;
          }
          $message .= $tag . ' expected ' . $count . ', found ' . $translation_tags_count[$tag] . '.';
        }
        $unexpected_tags = array_diff_key($translation_tags_count, $source_tags_count);
        foreach ($unexpected_tags as $tag => $count) {
          if (!isset($translation_tags_count[$tag])) {
            $translation_tags_count[$tag] = 0;
          }
          $message .= $count . ' unexpected ' . $tag . ' tag(s), found.';
        }
      }

    }
    return $message;
  }

  /**
   * Compare the HTML tags of source and translation.
   * @param array $tags
   *  array containing all the HTML tags.
   */
  function htmlTagCount($tags) {
    $counted_tags = array();
    foreach ($tags as $tag) {
      if (in_array($tag, array_keys($counted_tags))) {
        $counted_tags[$tag]++;
      }
      else {
        $counted_tags[$tag] = 1;
      }
    }
    return $counted_tags;
  }
}
