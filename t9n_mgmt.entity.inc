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

  /**
   * Overrides Entity::__construct().
   */
  public function __construct(array $values = array()) {
    parent::__construct($values, 't9n_mgmt_map');
  }

}
