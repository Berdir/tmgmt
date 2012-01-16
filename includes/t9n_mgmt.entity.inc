<?php

/*
 * @file
 * Entity classes for Translation Management entities.
 */

/**
 * Entity class for the t9n_mgmt_translator entity.
 */
class TranslationManagementTranslator extends Entity {

  // @todo: Add documentation.
  public $name;
  public $label;
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

  // @todo: Add documentation.
  public $tmid;
  public $state;
  public $source_language;
  public $target_language;
  public $data;
  public $translated_data;
  public $response;

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
   * The plugin that provides the source item.
   *
   * @var varchar
   */
  public $plugin;

  // @todo: Add documentation.
  public $item_type;
  public $item_id;

  /**
   * Overrides Entity::__construct().
   */
  public function __construct(array $values = array()) {
    parent::__construct($values, 't9n_mgmt_map');
  }

}
