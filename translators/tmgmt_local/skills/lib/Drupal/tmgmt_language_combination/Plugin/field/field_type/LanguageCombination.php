<?php

/**
 * @file
 * Contains \Drupal\tmgmt_language_combination\Plugin\field\field_type\LanguageCombination.
 */

namespace Drupal\tmgmt_language_combination\Plugin\field\field_type;

use Drupal\Core\Entity\Annotation\FieldType;
use Drupal\Core\Annotation\Translation;
use Drupal\field\FieldInterface;
use Drupal\field\Plugin\Type\FieldType\ConfigFieldItemBase;

/**
 * Plugin implementation of the 'tmgmt_language_combination' field type.
 *
 * @FieldType(
 *   id = "tmgmt_language_combination",
 *   label = @Translation("Language Combination"),
 *   description = @Translation("Allows the definition of language combinations (e.g. 'From english to german')."),
 *   default_widget = "tmgmt_language_combination_default",
 *   default_formatter = "tmgmt_language_combination_default"
 * )
 */
class LanguageCombination extends ConfigFieldItemBase {
  /**
   * Definitions of the contained properties.
   *
   * @var array
   */
  static $propertyDefinitions;

  /**
   * {@inheritdoc}
   */
  public function getPropertyDefinitions() {

    if (!isset(static::$propertyDefinitions)) {
      static::$propertyDefinitions['language_from'] = array(
        'type' => 'string',
        'label' => t('From language'),
      );
      static::$propertyDefinitions['language_to'] = array(
        'type' => 'string',
        'label' => t('To language'),
      );
    }
    return static::$propertyDefinitions;
  }


  /**
   * {@inheritdoc}
   */
  public static function schema(FieldInterface $field) {
    return array(
      'columns' => array(
        'language_from' => array(
          'description' => 'The langcode of the language from which the user is able to translate.',
          'type' => 'varchar',
          'length' => 10,
        ),
        'language_to' => array(
          'description' => 'The langcode of the language to which the user is able to translate.',
          'type' => 'varchar',
          'length' => 10,
        ),
      ),
      'indexes' => array(
        'language' => array('language_from', 'language_to'),
      ),
    );
  }

  public function isEmpty() {
    if (empty($this->language_from) || empty($this->language_to) || $this->language_from == '_none' || $this->language_to == '_none') {
      return TRUE;
    }

    return FALSE;
  }


  /**
   * {@inheritdoc}
   *
   * @todo
   *
   public function getConstraints() {
    $constraint_manager = \Drupal::typedData()
      ->getValidationConstraintManager();
    $constraints = parent::getConstraints();

    if ($max_length = $this->getFieldSetting('max_length')) {
      $constraints[] = $constraint_manager->create('ComplexData', array(
        'value' => array(
          'Length' => array(
            'max' => $max_length,
            'maxMessage' => t('%name: the text may not be longer than @max characters.', array(
              '%name' => $this
                ->getFieldDefinition()->getFieldLabel(),
              '@max' => $max_length
            )),
          )
        ),
      ));
    }

    return $constraints;
  }*/

}
