<?php

namespace Drupal\style_selector\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget\OptionsWidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Base class for Style Selector widgets.
 */
abstract class SelectorWidgetBase extends OptionsWidgetBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    $defaults = \Drupal::service('style_selector.util')->getDefaults();
    return [
      'advanced' => [
        'color_prop' => $defaults['color_prop'],
        'extra_classes' => $defaults['extra_classes'],
        'empty_option' => $defaults['empty_option'],
        'ui_settings' => $defaults['ui_settings'],
      ],
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);
    $settings = $this->getSettings();
    $form['advanced'] = [
      '#type' => 'details',
      '#title' => $this->t('Advanced Settings'),
    ];
    if ($this->fieldDefinition->getType() == 'style_selector_css_color') {
      $form['advanced']['color_prop'] = [
        '#type' => 'select',
        '#title' => $this->t('Color property target'),
        '#description' => $this->t("Should the color for each option be applied to that element's <b><code>background-color</code></b> or <b><code>color</code></b> property?"),
        '#options' => [
          'background-color' => $this->t('Background Color'),
          'color' => $this->t('Color'),
        ],
        '#default_value' => $settings['advanced']['color_prop'],
        '#required' => TRUE,
      ];
    }
    $form['advanced']['extra_classes'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Extra classes'),
      '#description' => $this->t('A space-separated list of additional classes to apply to the form element wrapper. The prefix <code>ssui--</code> is added to each supplied value.'),
      '#default_value' => $settings['advanced']['extra_classes'],
    ];
    $form['advanced']['empty_option'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Empty option label'),
      '#description' => $this->t('Label to use for the <em>none/empty</em> option. Applies to <strong>optional fields only</strong>.'),
      '#default_value' => $settings['advanced']['empty_option'],
    ];
    $form['advanced']['ui_settings'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('UI Options'),
      '#description' => $this->t('Enable or disable widget display options below.'),
      '#description_display' => 'before',
    ];
    $form['advanced']['ui_settings']['alpha_grid'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show <strong>alpha channel grid</strong> background.'),
      '#default_value' => $settings['advanced']['ui_settings']['alpha_grid'],
    ];
    $form['advanced']['ui_settings']['check_icon'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show <strong>selected icon</strong>.'),
      '#default_value' => $settings['advanced']['ui_settings']['check_icon'],
    ];
    $form['advanced']['ui_settings']['empty_icon'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show <strong>empty option icon</strong>.'),
      '#default_value' => $settings['advanced']['ui_settings']['empty_icon'],
    ];
    $form['advanced']['ui_settings']['text_icon'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show <strong>text color icon</strong>.'),
      '#default_value' => $settings['advanced']['ui_settings']['text_icon'],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $settings = $this->getSettings()['advanced'];
    $summary = [];
    if (isset($settings['color_prop'])) {
      $summary[] = $this->t('Color property target: @prop', ['@prop' => $settings['color_prop']]);
    }
    if ($settings['extra_classes']) {
      $summary[] = $this->t('Extra classes: @classes', ['@classes' => $settings['extra_classes']]);
    }
    if ($settings['empty_option']) {
      $summary[] = $this->t('Empty option label: @label', ['@label' => $settings['empty_option']]);
    }

    // UI Options on a single line.
    $ui_settings = [
      'alpha_grid' => 'Alpha grid',
      'check_icon' => 'Selected icon',
      'empty_icon' => 'Empty icon',
      'text_icon' => 'Text icon',
    ];

    $settings_str = 'UI Options: ';
    $settings_vals = [];
    foreach ($ui_settings as $setting => $setting_label) {
      $current_value = boolval($settings['ui_settings'][$setting]);
      $settings_str .= "--{$setting_label}: @{$setting} ";
      $settings_vals["@{$setting}"] = ($current_value) ? 'On' : 'Off';
    }
    // Suppress phpcs warnings here because building a string literal would
    // be error prone and difficult to maintain if/when new settings are added.
    // phpcs:ignore
    $summary[] = $this->t($settings_str, $settings_vals);

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);

    $options = $this->getOptions($items->getEntity());
    $selected = $this->getSelectedOptions($items);

    // If required and there is one single option, preselect it.
    if ($this->required && count($options) == 1) {
      reset($options);
      $selected = [key($options)];
    }

    $settings = $this->getSettings();

    $element += [
      '#type' => 'style_selector',
      '#options' => $options,
      '#style_type' => str_replace('style_selector_', '', $this->fieldDefinition->getType()),
      '#required' => $this->fieldDefinition->isRequired(),
      '#extra_classes' => $settings['advanced']['extra_classes'],
      '#empty_option' => $settings['advanced']['empty_option'],
      '#ui_settings' => $settings['advanced']['ui_settings'],
    ];

    if ($this->multiple) {
      $element += [
        '#multiple' => TRUE,
        '#default_value' => $selected,
      ];
    }
    else {
      $element += [
        // Radio buttons need a scalar value. Take the first default value, or
        // default to NULL so that the form element is properly recognized as
        // not having a default value.
        '#default_value' => $selected ? reset($selected) : NULL,
      ];
    }

    if (isset($settings['advanced']['color_prop'])) {
      $element['#color_prop'] = $settings['advanced']['color_prop'];
    }

    return $element;
  }

}
