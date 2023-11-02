<?php

namespace Drupal\style_selector\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'style_selector_css_class_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "style_selector_css_class_formatter",
 *   label = @Translation("Style Selector CSS Style"),
 *   field_types = {
 *     "style_selector_css_class",
 *   }
 * )
 */
class CssClassFormatter extends SelectorFormatterBase {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    // Attach shared and theme libraries configured in module settings.
    $config = \Drupal::config('style_selector.settings');
    $libraries = array_merge($config->get('shared_libraries'), $config->get('theme_libraries'));
    if ($libraries) {
      $elements['#attached']['library'] ??= [];
      $elements['#attached']['library'] = array_merge($elements['#attached']['library'], $libraries);
    }

    // We're not actually displaying anything, so that's it.
    return $elements;
  }

}
