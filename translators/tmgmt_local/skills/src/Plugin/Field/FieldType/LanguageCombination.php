<?php

/**
 * @file
 * Contains \Drupal\tmgmt_language_combination\Plugin\field\field_type\LanguageCombination.
 */

namespace Drupal\tmgmt_language_combination\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;

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
class LanguageCombination extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field) {
    $property_definitions['language_from'] = DataDefinition::create('string')
      ->setLabel(t('From language'));
    $property_definitions['language_to'] = DataDefinition::create('string')
      ->setLabel(t('To language'));
    return $property_definitions;
  }


  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field) {
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

  /**
   * {@inheritdoc}
   */
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
