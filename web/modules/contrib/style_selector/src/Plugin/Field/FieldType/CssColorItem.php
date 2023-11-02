<?php

namespace Drupal\style_selector\Plugin\Field\FieldType;

use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\options\Plugin\Field\FieldType\ListStringItem;

/**
 * Plugin implementation of the 'style_selector_css_color' field type.
 *
 * @FieldType(
 *   id = "style_selector_css_color",
 *   label = @Translation("Color list"),
 *   description = @Translation("This field stores CSS Color values from a list of allowed 'value => label' pairs, i.e. 'Styles': red => Red, rgb(255,255,255) => White, #0000FF => Blue."),
 *   category = @Translation("Style Selector"),
 *   default_widget = "style_selector_compact_widget",
 *   default_formatter = "style_selector_css_color_formatter",
 * )
 */
class CssColorItem extends ListStringItem {
  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  protected function allowedValuesDescription() {
    $description = $this->t('<p>The possible values this field can contain. Enter one value per line, in the format <b>key|label</b>.</p>');
    $description .= $this->t('<p>The <b>key</b> is the stored value, and must be a valid, supported CSS color format. <strong>Hex values must be prefixed with #, and will be converted and stored as RGB.</strong></p>');
    $description .= $this->t('<p>The <b>label</b> will be used in displayed values and edit forms. Both key and label are required.</p>');
    $description .= '<p>' . $this->t('<b>Example:</b>');
    $description .= '<br/>' . $this->t('<code>#fff|White</code>');
    $description .= '<br/>' . $this->t('<code>currentColor|Current Text Color</code>');
    $description .= '<br/>' . $this->t('<code>rgba(0,0,0,0.5)|Semi-transparent Black</code>');
    $description .= '</p>';
    return $description;
  }

  /**
   * {@inheritdoc}
   */
  protected static function validateAllowedValue($option) {
    parent::validateAllowedValue($option);
    if (!\Drupal::service('style_selector.css_color')->getFormSafeColorValue($option)) {
      return t('Value key @opt is not a valid CSS color.', [
        '@opt' => $option,
      ]);
    }
  }

  /**
   * {@inheritdoc}
   */
  protected static function castAllowedValue($value) {
    // Convert hex values are converted to rgb.
    return (string) \Drupal::service('style_selector.css_color')->getFormSafeColorValue($value);
  }

}
