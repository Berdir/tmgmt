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

  // @todo: Add more states.
  const STATE_NEW = 0;

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
