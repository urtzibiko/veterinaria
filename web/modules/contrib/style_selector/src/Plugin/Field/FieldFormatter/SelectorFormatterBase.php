<?php

namespace Drupal\style_selector\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Base class for Style Selector formatters.
 */
abstract class SelectorFormatterBase extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'extra_classes' => '',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);
    $form['extra_classes'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Extra classes'),
      '#description' => $this->t('A space-separated list of additional classes to apply to the entity wrapper when the field value is set.'),
      '#default_value' => $this->getSetting('extra_classes'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();
    $classes = $this->getSetting('extra_classes');
    if ($classes) {
      $summary[] = $this->t('Extra classes: @classes', [
        '@classes' => $classes,
      ]);
    }

    return $summary;
  }

}
