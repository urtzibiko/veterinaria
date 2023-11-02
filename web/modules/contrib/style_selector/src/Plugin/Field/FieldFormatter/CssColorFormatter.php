<?php

namespace Drupal\style_selector\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'style_selector_css_color_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "style_selector_css_color_formatter",
 *   label = @Translation("Style Selector CSS Color"),
 *   field_types = {
 *     "style_selector_css_color",
 *   }
 * )
 */
class CssColorFormatter extends SelectorFormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'css_property' => 'background',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);
    $form['css_property'] = [
      '#type' => 'select',
      '#title' => $this->t('Which CSS property should the color be applied to?'),
      '#options' => [
        'background-color' => $this->t('background-color'),
        'color' => $this->t('color'),
      ],
      '#default_value' => $this->getSetting('css_property'),
      '#required' => TRUE,
      '#weight' => -1,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $property = $this->getSetting('css_property');
    if (!empty($property)) {
      $summary[] = $this->t('Apply field value to element @prop property', [
        '@prop' => $property,
      ]);
    }
    return array_merge($summary, parent::settingsSummary());
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    // No output here since we're setting a style parent entity element. That
    // style is set in style_selector_entity_view_alter().
    return [];
  }

}
