<?php

/*
 * @file
 * Entity classes for Translation Management entities.
 */

/**
 * Entity class for the t9n_mgmt_translator entity.
 */
class TranslationManagementTranslator extends Entity {

  /**
   * The ID of the translator.
   *
   * @var integer
   */
  public $tid;

  /**
   * Machine readable name of the translator.
   *
   * @var string
   */
  public $name;

  /**
   * Label of the translator.
   *
   * @var string
   */
  public $label;

  /**
   * Plugin name of the translator.
   *
   * @type string
   */
  public $plugin;

  /**
   * Translator type specific settings.
   *
   * @var array
   */
  public $settings;

  /**
   * Overrides Entity::__construct().
   */
  public function __construct(array $values = array()) {
    parent::__construct($values, 't9n_mgmt_translator');
  }

}

/**
 * Entity class for the t9n_mgmt_job entity.
 */
class TranslationManagementJob extends Entity {

  /**
   * A new translation job, not yet submitted to a translator.
   */
  const STATE_NEW = 0;

  /**
   * A translation job that has been submitted to the translator.
   */
  const STATE_SUBMITTED = 1;

  /**
   * A translation job that has been rejected by the translator.
   *
   * The response contains information why the job was rejected.
   */
  const STATE_REJECTED = 2;

  /**
   * The translation job was translated and needs to be reviewed.
   */
  const STATE_REVIEW = 3;

  /**
   * The translation has been accepted and the job is finished.
   */
  const STATE_ACCEPTED = 4;

  /**
   * Translation job identifier.
   *
   * @var integer
   */
  public $tjid;

  /**
   * ID of the translation map this job belongs to.
   *
   * @see TranslationManagementJob::getMap().
   *
   * @var type
   */
  public $tmid;

  /**
   * Current state of the translation job
   * @var type
   */
  public $state;

  /**
   * Language to be translated from.
   *
   * @var string
   */
  public $source_language;

  /**
   * Language into which the data needs to be translated.
   *
   * @var varchar
   */
  public $target_language;

  /**
   * Array of the data to be translated.
   *
   * The structure is similar to the form API in the way that it is a possibly
   * nested array with the following properties whose presence indicate that the
   * current element is a text that might need to be translated.
   *
   * - #text: The text to be translated.
   * - #label: The label that might be shown to the translator.
   * - #comment: (Optional) A comment with additional information.
   * - #translate: Either TRUE or FALSE. Only text that has this property set to
   *   TRUE must be translated.
   *
   * The key can be an alphanumeric string.
   *
   * @see TranslationManagementJob::getFlattenedData()
   *
   * @var array
   */
  public $data;

  /**
   * The translated data in the same structure.
   *
   * Only the parts that actually are translated are present in this array.
   *
   * @var array
   */
  public $translated_data;

  /**
   * Response message from the translator.
   *
   * Can for example be used if the job has been rejected by the translator.
   *
   * @var string
   */
  public $response;

  /**
   * Reference to the used translator of this job.
   *
   * @see TranslationManagementJob::getTranslator().
   *
   * @var string
   */
  public $translator;

  /**
   * Translator specific configuration and context information for this job.
   *
   * @var array
   */
  public $translator_context;

  /**
   * Remote identification of this job.
   *
   * @var integer
   */
  public $translator_id;


  /**
   * Overrides Entity::__construct().
   */
  public function __construct(array $values = array()) {
    parent::__construct($values, 't9n_mgmt_job');
  }

}

/**
 * Entity class for the t9n_mgmt_map entity.
 */
class TranslationManagementMap extends Entity {

  /**
   * The source plugin that provides the item.
   *
   * @var varchar
   */
  public $plugin;

  /**
   * Type of this item, used by the plugin to identify it.
   *
   * @var string
   */
  public $item_type;

  /**
   * Id of the item.
   *
   * @var integer
   */
  public $item_id;

  /**
   * Overrides Entity::__construct().
   */
  public function __construct(array $values = array()) {
    parent::__construct($values, 't9n_mgmt_map');
  }

}
