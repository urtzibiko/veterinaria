<?php

namespace Drupal\style_selector\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'style_selector_tile_widget' widget.
 *
 * @FieldWidget(
 *   id = "style_selector_tile_widget",
 *   label = @Translation("Style Selector - Tile"),
 *   field_types = {
 *     "style_selector_css_class",
 *     "style_selector_css_color",
 *   },
 *   multiple_values = TRUE
 * )
 */
class TileWidget extends SelectorWidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);

    $element += [
      '#ui_variant' => [
        'tile' => [
          'size' => $this->getSetting('size'),
        ],
      ],
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form['size'] = [
      '#type' => 'select',
      '#title' => $this->t('Size'),
      '#options' => [
        'default' => $this->t('Default'),
        'large' => $this->t('Large'),
      ],
      '#default_value' => $this->getSetting('size'),
      '#required' => TRUE,
    ];

    return $form + parent::settingsForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $summary[] = $this->t('Size: @size', ['@size' => $this->getSetting('size')]);

    return array_merge($summary, parent::settingsSummary());
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'size' => 'default',
    ] + parent::defaultSettings();
  }

}
