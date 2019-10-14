<?php

namespace Drupal\seeds_page\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;

/**
 * Plugin implementation of the 'heading' formatter.
 *
 * @FieldFormatter(
 *   id = "heading",
 *   label = @Translation("Heading"),
 *   field_types = {
 *     "string"
 *   }
 * )
 */
class Heading extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $summary[] = $this->t('Displays as a heading.');
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];
    foreach ($items as $delta => $item) {
      $value = $item->value;
      $element[$delta] = [
        '#markup' => "<h2>$value</h2>",
      ];
    }

    return $element;
  }

}