<?php

namespace Drupal\style_selector\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'style_selector_compact_widget' widget.
 *
 * @FieldWidget(
 *   id = "style_selector_compact_widget",
 *   label = @Translation("Style Selector - Compact"),
 *   field_types = {
 *     "style_selector_css_class",
 *     "style_selector_css_color",
 *   },
 *   multiple_values = TRUE
 * )
 */
class CompactWidget extends SelectorWidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);

    $element += [
      '#ui_variant' => [
        'compact' => [
          'type' => $this->getSetting('type'),
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
    $form['type'] = [
      '#type' => 'select',
      '#title' => $this->t('Type'),
      '#options' => [
        'round' => $this->t('Round'),
        'square' => $this->t('Square'),
      ],
      '#default_value' => $this->getSetting('type'),
      '#required' => TRUE,
    ];
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
    $summary[] = $this->t('Type: @type', ['@type' => $this->getSetting('type')]);
    $summary[] = $this->t('Size: @size', ['@size' => $this->getSetting('size')]);

    return array_merge($summary, parent::settingsSummary());
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'type' => 'round',
      'size' => 'default',
    ] + parent::defaultSettings();
  }

}
