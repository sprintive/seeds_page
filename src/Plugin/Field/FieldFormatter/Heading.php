<?php

namespace Drupal\seeds_page\Plugin\Field\FieldFormatter;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;

/**
 * Plugin implementation of the 'heading' formatter.
 *
 * @FieldFormatter(
 *   id = "heading",
 *   label = @Translation("Heading"),
 *   field_types = {
 *     "string",
 *     "textfield"
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
   *
   */
  public static function defaultSettings() {
    return [
      'tag' => 'h2',
      'class' => '',
    ] + parent::defaultSettings();
  }

  /**
   *
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);
    $element['tag'] = [
      '#type' => 'select',
      '#title' => "Tag",
      '#options' => ['h1' => 'H1', 'h2' => 'H2', 'h3' => 'H3'],
      '#default_value' => $this->getSetting("tag"),
    ];

    $element['class'] = [
      '#type' => 'textfield',
      '#title' => "Class",
      '#default_value' => $this->getSetting("class"),
    ];
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];
    foreach ($items as $delta => $item) {
      $value = $item->value;
      $tag = $this->getSetting("tag");
      $class = $this->getSetting("class");
      $element[$delta] = [
        '#type' => 'html_tag',
        '#tag' => $tag,
        '#attributes' => [
          'class' => $class,
        ],
        '#value' => $value,
      ];
    }

    return $element;
  }

}
