<?php

namespace Drupal\style_selector\Element;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element\Checkboxes;
use Drupal\Core\Render\Element\FormElement;
use Drupal\Core\Render\Element\Radios;

/**
 * Renders the style_selector form element.
 *
 * @FormElement("style_selector")
 *
 * Custom form element properties:
 * - #style_type: (string) Whether the options are a list of CSS classes or
 *   CSS colors. Possible values:
 *   - css_class (default)
 *   - css_color
 * - #options: (array) Array of value => label pairs that will be transformed
 *   into available options for the element.
 * - #ui_variant: (array) The style variant of the widget/form element with
 *   additional settings as required. Example:
 *   - 'compact' => [
 *        'type' => 'round',
 *        'size' => 'large',
 *     ]
 * - #multiple: (boolean) Are multiple selections allowed. Defealts to FALSE.
 * - #ui_settings: (array) Option key, boolean value pair to set addtional
 *   display options for element/widget.
 *   Possible values (default):
 *   - 'alpha_grid' (TRUE): Show the alpha grid background image?
 *   - 'check_icon' (TRUE): Show the icon for checked selection?
 *   - 'empty-icon' (TRUE): Show the no-symbol in options with value 'none'?
 *   - 'text-icon' (FALSE): Show the 'T' icon to demo forground color?
 *   Example:
 *   - '#ui_settings' => [
 *       'alpha_grid' => FALSE,
 *       'Text-icon' => TRUE,
 *     ]
 * - #extra_classes: (string) Space-separated list of one or more classes to
 *   add to the ssui container element. The prefix 'ssui--' will be added to
 *   each supplied value.
 * - #empty_option: String to display as the label for none/empty options.
 *   Default is 'None'.
 */
class StyleSelector extends FormElement {

  /**
   * {@inheritdoc}
   */
  public function getInfo() {
    $defaults = \Drupal::service('style_selector.util')->getDefaults();
    $class = static::class;
    return [
      '#input' => TRUE,
      '#options' => [],
      '#style_type' => 'css_class',
      '#color_prop' => $defaults['color_prop'],
      '#ui_variant' => $defaults['ui_variant'],
      '#ui_settings' => [],
      '#extra_classes' => '',
      '#empty_option' => $defaults['empty_option'],
      '#multiple' => FALSE,
      '#process' => [
        [$class, 'processFormElement'],
        [$class, 'processGroup'],
      ],
      '#pre_render' => [
        [$class, 'preRenderGroup'],
      ],
    ];
  }

  /**
   * Processes a Style Selector form element.
   *
   * @param array $element
   *   The form element whose value is being processed.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param array $complete_form
   *   The complete form structure.
   *
   * @return array
   *   The element render array.
   */
  public static function processFormElement(array &$element, FormStateInterface $form_state, &$complete_form) {
    // Exit early if no styles provided.
    if (empty($element['#options'])) {
      return $element;
    }

    $helpers = \Drupal::service('style_selector.util');

    // Ensure multiple Style Selector child groups have unique names.
    $element['#tree'] = TRUE;

    // Ensure #multiple is a boolean, or defalut to FALSE.
    $element['#multiple'] = (is_bool($element['#multiple'])) ? $element['#multiple'] : FALSE;

    // Make sure we have correct default empty option label.
    if ($element['#empty_option'] === '' || !is_string($element['#empty_option'])) {
      $element['#empty_option'] = $helpers->getDefault('empty_option');
    }

    // Add the boolean fieldset for options. Checkboxes for multiple values,
    // else radios. Copy several properties from our parent element.
    $element['style_selector'] = [
      '#type' => (($element['#multiple']) ? 'checkboxes' : 'radios'),
      '#style_selector' => TRUE,
      '#required' => $element['#required'],
      '#default_value' => $element['#default_value'] ?? NULL,
      '#title' => $element['#title'],
      '#title_display' => $element['#title_display'],
      '#input' => $element['#input'],
      '#description' => $element['#description'] ?? '',
      '#description_display' => $element['#description_display'],
    ];

    // Add classes to the fieldset container.
    $element['style_selector']['#attributes']['class'][] = 'ssui';

    // Add multiple values class.
    if ($element['#multiple']) {
      $element['style_selector']['#attributes']['class'][] = 'ssui--multiple';
    }

    // Add a theme class to help with overrides/fixes.
    $active_theme = \Drupal::service('theme.manager')->getActiveTheme()->getName();
    $element['style_selector']['#attributes']['class'][] = 'ssui--' . $active_theme;

    // Add ui_variant classes.
    static::addVariant($element);

    // Add ui_options classes.
    static::addSettingsClasses($element);

    // Add any extra classes specified.
    if ($element['#extra_classes']) {
      $extra_classes = $helpers->getClasses($element['#extra_classes'], 'ssui--');
      $element['style_selector']['#attributes']['class'] = array_merge($element['style_selector']['#attributes']['class'], $extra_classes);
    }

    // Validate and copy the passed options to the style_selector element.
    $options = $element['#options'];

    // Add the empty option if the field is single value and not required.
    // The value 'none' will be added as a NULL value element.
    if (!$element['#multiple'] && !$element['#required']) {
      $empty_option = ['none' => t('@option', ['@opt' => $element['#empty_option']])];
      $options = $empty_option + $options;
    }

    foreach ($options as $style_value => $style_label) {
      // Skip if the item is missing label or value.
      if (empty($style_value) || empty($style_label)) {
        continue;
      }

      $option = '';
      if ($style_value === 'none') {
        $option = NULL;
      }
      // Validate CSS classes.
      elseif ($element['#style_type'] === 'css_class') {
        $option = $style_value = $helpers->getClassList($style_value);
      }
      // Validate CSS color.
      elseif ($element['#style_type'] === 'css_color') {
        if ($valid_color = \Drupal::service('style_selector.css_color')->getFormSafeColorValue($style_value)) {
          $option = $style_value = $valid_color;
        }
        else {
          // Invalid css color.
          continue;
        }
      }
      else {
        // Invalid #style_type.
        continue;
      }

      // Build the option element (individual radio).
      $element['style_selector']['#options'][$option] = $style_label;
      $element['style_selector'][$option]['#title'] = $style_label;
      $element['style_selector'][$option]['#attributes']['class'][] = "ssui__input";

      // These custom properties are used in the element preprocessors.
      // Mark this as a style selector element.
      $element['style_selector'][$option]['#style_selector'] = TRUE;
      // Is it a CSS class or color?
      $element['style_selector'][$option]['#style_type'] = $element['#style_type'];
      // The actual value(s) stored in the option.
      $element['style_selector'][$option]['#style_value'] = $style_value;
      // Store the processed value(s) for use in a wrapper class or style
      // attribute as appropriate.
      $element['style_selector'][$option]['#option_style'] = [];
      if ($element['#style_type'] === 'css_class') {
        $element['style_selector'][$option]['#option_style']['class'] = $helpers->getClasses($option);
      }
      elseif ($element['#style_type'] === 'css_color' && $option) {
        $element['style_selector'][$option]['#option_style']['style'] = "{$element['#color_prop']}:{$option};";
      }

    }

    // Copy the processed options back to the original element #options to
    // ensure they pass validation.
    $element['#options'] = $element['style_selector']['#options'];

    // Attach configured libraries.
    $module_config = \Drupal::config('style_selector.settings');
    $libraries = array_merge($module_config->get('admin_libraries') ?? [], $module_config->get('shared_libraries') ?? []);
    foreach ($libraries as $library) {
      $element['#attached']['library'][] = $library;
    }

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public static function valueCallback(&$element, $input, FormStateInterface $form_state) {
    // The options are in the 'style_selector' sub-element. So, if a value has
    // been selected, the input will be an array with one item keyed to
    // 'style_selector', e.g.: [ 'style_selector' => 'selected_value' ].
    // We need to extract that value so it can be passed to the appropriate
    // value callback function depending on whether the element allows multiple
    // values (checkboxes) or not (radios).
    if (is_array($input)) {
      $input = reset($input);
    }

    if ($element['#multiple']) {
      return Checkboxes::valueCallback($element, $input, $form_state);
    }
    else {
      return Radios::valueCallback($element, $input, $form_state);
    }
  }

  /**
   * Helper method to add UI Settings classes.
   *
   * @param array $element
   *   The style selector form element.
   */
  public static function addSettingsClasses(array &$element) {
    $default_settings = \Drupal::service('style_selector.util')->getDefault('ui_settings');
    // Merge provided settings with defaults.
    if (is_array($element['#ui_settings'])) {
      $ui_settings = NestedArray::mergeDeep($default_settings, $element['#ui_settings']);
    }
    else {
      $ui_settings = $default_settings;
    }

    foreach ($ui_settings as $setting => $value) {
      // Only add supported options.
      if (isset($default_settings[$setting])) {
        $element['style_selector']['#attributes']['class'][] = 'ssui--' . HTML::getClass(str_replace('_', '-', $setting)) . '-' . ((boolval($value)) ? 'on' : 'off');
      }
    }
  }

  /**
   * Helper to set up the UI variant (a.k.a 'ui style').
   *
   * Add classes and libraries for UI Variant.
   *
   * @param array $element
   *   The form element array.
   */
  public static function addVariant(array &$element) {
    $variant = static::getVariantSettings($element);
    $variant_name = array_key_first($variant);

    // Add the style variant name class, e.g. ssui-style--foo.
    $variant_class = 'ssui-style--' . HTML::getClass($variant_name);
    $element['style_selector']['#attributes']['class'][] = $variant_class;

    // Add the style variant settings class(es), e.g. ssui-style--foo-bar-baz.
    foreach ($variant[$variant_name] as $prefix => $value) {
      $element['style_selector']['#attributes']['class'][] = $variant_class . '-' . HTML::getClass($prefix . '-' . $value);
    }

    // Add library if this is a built-in variant.
    $ss_variants = [
      'compact',
      'tile',
    ];
    foreach ($ss_variants as $name) {
      if ($name == $variant_name) {
        $element['#attached']['library'][] = 'style_selector/widget_' . $name;
      }
    }
  }

  /**
   * Get a valid UI Variant array.
   *
   * Validates a user-supplied variant, or returns the default if an invalid
   * value was submitted.
   *
   * @param array $element
   *   The form element array.
   *
   * @return array
   *   The validated variant array or the fallback default.
   */
  public static function getVariantSettings(array $element) {
    $variant = $element['#ui_variant'];

    // Validate the provided variant array.
    if (is_array($variant)) {
      $variant_name = array_key_first($variant);
      if ($variant_name && !is_numeric($variant_name)) {
        return $variant;
      }
    }
    // Validation failed, so return the default.
    else {
      return \Drupal::service('style_selector.util')->getDefault('ui_variant');
    }
  }

}
