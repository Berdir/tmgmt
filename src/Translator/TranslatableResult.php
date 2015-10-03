<?php
/**
 * @file
 * Contains \Drupal\tmgmt\TranslatableResult.
 */

namespace Drupal\tmgmt\Translator;

use Drupal\tmgmt\TranslatorInterface;
use Drupal\tmgmt\JobInterface;

/**
 * Class AvailableResult.
 *
 * @package Drupal\tmgmt\Translator
 *
 * @param TranslatorInterface $translator
 *   The Translator entity that should handle the translation.
 * @param \Drupal\tmgmt\JobInterface $job
 *   The Job entity that should be translated.
 */
class TranslatableResult extends TranslatorResult {
}
