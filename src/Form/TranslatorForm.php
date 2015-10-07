<?php

/**
 * @file
 * Contains \Drupal\tmgmt\Form\TranslatorForm.
 */

namespace Drupal\tmgmt\Form;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Entity\EntityForm;
use Drupal\tmgmt\SourceManager;
use Drupal\tmgmt\TranslatorManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;

/**
 * Form controller for the translator edit forms.
 *
 * @ingroup tmgmt_translator
 */
class TranslatorForm extends EntityForm {

  /**
   * @var \Drupal\tmgmt\TranslatorInterface
   */
  protected $entity;

  /**
   * Translator plugin manager.
   *
   * @var \Drupal\tmgmt\TranslatorManager
   */
  protected $translatorManager;

  /**
   * Source plugin manager.
   *
   * @var \Drupal\tmgmt\SourceManager
   */
  protected $sourceManager;

  /**
   * Constructs an EntityForm object.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface
   *   The module handler service.
   * @param \Drupal\tmgmt\TranslatorManager $translator_manager
   *   The translator plugin manager.
   */
  public function __construct(TranslatorManager $translator_manager, SourceManager $source_manager) {
    $this->translatorManager = $translator_manager;
    $this->sourceManager = $source_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.tmgmt.translator'),
      $container->get('plugin.manager.tmgmt.source')
    );
  }

  /**
   * Overrides Drupal\Core\Entity\EntityForm::form().
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);
    $entity = $this->entity;
    // Check if the translator is currently in use.
    if ($busy = !$entity->isNew() ? tmgmt_translator_busy($entity->id()) : FALSE) {
      drupal_set_message(t("This translator is currently in use. It cannot be deleted. The chosen Translation Plugin cannot be changed."), 'warning');
    }
    $available = $this->translatorManager->getLabels();
    // If the translator plugin is not set, pick the first available plugin as the
    // default.
    if (!($entity->hasPlugin())) {
      $entity->setPluginID(key($available));
    }
    $form['label'] = array(
      '#type' => 'textfield',
      '#title' => t('Label'),
      '#description' => t('The label of the translator.'),
      '#default_value' => $entity->label(),
      '#required' => TRUE,
      '#size' => 32,
      '#maxlength' => 64,
    );
    $form['name'] = array(
      '#type' => 'machine_name',
      '#title' => t('Machine name'),
      '#description' => t('The machine readable name of this translator. It must be unique, and it must contain only alphanumeric characters and underscores. Once created, you will not be able to change this value!'),
      '#default_value' => $entity->id(),
      '#machine_name' => array(
        'exists' => '\Drupal\tmgmt\Entity\Translator::load',
        'source' => array('label'),
      ),
      '#disabled' => !$entity->isNew(),
      '#size' => 32,
      '#maxlength' => 64,
    );
    $form['description'] = array(
      '#type' => 'textarea',
      '#title' => t('Description'),
      '#description' => t('The description of the translator.'),
      '#default_value' => $entity->getDescription(),
      '#size' => 32,
      '#maxlength' => 255,
    );
    $form['auto_accept'] = array(
      '#type' => 'checkbox',
      '#title' => t('Auto accept finished translations'),
      '#description' => t('This skips the reviewing process and automatically accepts all translations as soon as they are returned by the translation provider.'),
      '#default_value' => $entity->isAutoAccept(),
    );
    $form['plugin_wrapper'] = array(
      '#type' => 'container',
      '#prefix' => '<div id="tmgmt-plugin-wrapper">',
      '#suffix' => '</div>',
    );
    // Pull the translator plugin info if any.
    if ($entity->getPluginID()) {
      $definition = $this->translatorManager->getDefinition($entity->getPluginID());
      $form['plugin_wrapper']['plugin'] = array(
        '#type' => 'select',
        '#title' => t('Translator plugin'),
        '#description' => isset($definition['description']) ? Xss::filter($definition['description']) : '',
        '#options' => $available,
        '#default_value' => $entity->getPluginID(),
        '#required' => TRUE,
        '#disabled' => $busy,
        '#ajax' => array(
          'callback' => array($this, 'ajaxTranslatorPluginSelect'),
          'wrapper' => 'tmgmt-plugin-wrapper',
        ),
      );
      $form['plugin_wrapper']['settings'] = array(
        '#type' => 'details',
        '#title' => t('@plugin plugin settings', array('@plugin' => $definition['label'])),
        '#tree' => TRUE,
        '#open' => TRUE,
      );

      // Add the translator plugin settings form.
      $plugin_ui = $this->translatorManager->createUIInstance($entity->getPluginID());
      $form_state->set('busy', $busy);
      $form['plugin_wrapper']['settings'] += $plugin_ui->buildConfigurationForm($form['plugin_wrapper']['settings'], $form_state);
      if (!Element::children($form['plugin_wrapper']['settings'])) {
        $form['#description'] = t("The @plugin plugin doesn't provide any settings.", array('@plugin' => $plugin_ui->getPluginDefinition()['label']));
      }
    }

    $controller = $entity->getPlugin();

    // If current translator is configured to provide remote language mapping
    // provide the form to configure mappings, unless it does not exists yet.
    if (!empty($controller) && $entity->providesRemoteLanguageMappings()) {
      $form['remote_languages_mappings'] = array(
        '#tree' => TRUE,
        '#type' => 'details',
        '#title' => t('Remote languages mappings'),
        '#description' => t('Here you can specify mappings of your local language codes to the translator language codes.'),
        '#open' => TRUE,
      );

      $options = array();
      foreach ($controller->getSupportedRemoteLanguages($entity) as $language) {
        $options[$language] = $language;
      }

      foreach ($entity->getRemoteLanguagesMappings() as $local_language => $remote_language) {
        $form['remote_languages_mappings'][$local_language] = array(
          '#type' => 'textfield',
          '#title' => \Drupal::languageManager()->getLanguage($local_language)->getName() . ' (' . $local_language . ')',
          '#default_value' => $remote_language,
          '#size' => 6,
        );

        if (!empty($options)) {
          $form['remote_languages_mappings'][$local_language]['#type'] = 'select';
          $form['remote_languages_mappings'][$local_language]['#options'] = $options;
          $form['remote_languages_mappings'][$local_language]['#empty_option'] = ' - ';
          unset($form['remote_languages_mappings'][$local_language]['#size']);
        }
      }
    }

    // Add a submit button and a cancel link to the form.
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Save translator'),
      '#disabled' => empty($available),
    );
    $form['actions']['delete'] = array(
      '#type' => 'submit',
      '#value' => t('Delete'),
      '#submit' => array('tmgmt_submit_redirect'),
      '#redirect' => 'admin/tmgmt/translators/manage/' . $entity->id() . '/delete',
      '#access' => !$entity->isNew(),
    );
    $form['actions']['cancel'] = array(
      '#type' => 'link',
      '#title' => t('Cancel'),
      '#href' => 'admin/config/regional/tmgmt_translator',
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    parent::validateForm($form, $form_state);
    if (!$form_state->getValue('plugin')) {
      $form_state->setErrorByName('plugin', $this->t('You have to select a translator plugin.'));
    }
    $plugin_ui = $this->translatorManager->createUIInstance($this->entity->getPluginID());
    $plugin_ui->validateConfigurationForm($form, $form_state);
  }


  /**
   * Overrides Drupal\Core\Entity\EntityForm::save().
   */
  public function save(array $form, FormStateInterface $form_state) {
    $entity = $this->entity;
    $status = $entity->save();

    if ($status === SAVED_UPDATED) {
      drupal_set_message(format_string('%label configuration has been updated.', array('%label' => $entity->label())));
    }
    else {
      drupal_set_message(format_string('%label configuration has been created.', array('%label' => $entity->label())));
    }

    $form_state->setRedirect('entity.tmgmt_translator.collection');
  }

  /**
   * {@inheritdoc}
   */
  public function delete(array $form, FormStateInterface $form_state) {
    $form_state->setRedirectUrl($this->entity->urlInfo('delete-form'));
  }


  /**
   * Ajax callback for loading the translator plugin settings form for the
   * currently selected translator plugin.
   */
  function ajaxTranslatorPluginSelect(array $form, FormStateInterface $form_state) {
    return $form['plugin_wrapper'];
  }

}
