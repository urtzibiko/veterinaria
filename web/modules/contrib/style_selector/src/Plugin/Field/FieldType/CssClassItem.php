<?php

namespace Drupal\style_selector\Plugin\Field\FieldType;

use Drupal\Component\Utility\Html;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\options\Plugin\Field\FieldType\ListStringItem;

/**
 * Plugin implementation of the 'style_selector_css_class' field type.
 *
 * @FieldType(
 *   id = "style_selector_css_class",
 *   label = @Translation("Style list"),
 *   description = @Translation("This field stores CSS Class values from a list of allowed 'value => label' pairs, i.e. 'Styles': bgstyle--red => Red, bgstyle--white => White, bgstyle--blue => Blue."),
 *   category = @Translation("Style Selector"),
 *   default_widget = "style_selector_compact_widget",
 *   default_formatter = "style_selector_css_class_formatter",
 * )
 */
class CssClassItem extends ListStringItem {
  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  protected function allowedValuesDescription() {
    $description = $this->t('<p>The possible values this field can contain. Enter one value per line, in the format <b>class_list|label</b>.</p>');
    $description .= $this->t('<p>The <b>class_list</b> is the stored value, and can be one or more valid CSS class identifiers. Separate multiple classes with a space. <strong>Do not include dot (.) characters.</strong></p>');
    $description .= $this->t('<p>The <b>label</b> will be used in displayed values and edit forms. Both key and label are required.<p>');
    $description .= '<p>' . $this->t('<b>Example:</b>');
    $description .= '<br/>' . $this->t('<code>myclass|My Class</code>');
    $description .= '<br/>' . $this->t('<code>foo foo--bar|My FooBar</code>');
    $description .= '</p>';

    return $description;
  }

  /**
   * {@inheritdoc}
   */
  protected static function validateAllowedValue($option) {
    parent::validateAllowedValue($option);
    $classes = explode(' ', $option);
    foreach ($classes as $class) {
      if ($class !== HTML::getClass($class)) {
        return t('Value key @class is not a valid CSS class identifier.', [
          '@class' => $class,
        ]);
      }
    }
  }

}
