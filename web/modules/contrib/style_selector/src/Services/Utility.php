<?php

namespace Drupal\style_selector\Services;

use Drupal\Component\Utility\Html;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\layout_builder\Entity\LayoutEntityDisplayInterface;
use Drupal\node\NodeInterface;

/**
 * Class Utilities.
 *
 * Helper methods.
 */
class Utility {

  /**
   * The config factory object.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The current module config object.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $config;

  /**
   * The css color service object.
   *
   * @var \Drupal\style_selector\Services\CssColor
   */
  protected $cssColor;

  /**
   * Config name.
   *
   * @var string
   */
  protected const CONFIG_NAME = 'style_selector.settings';

  /**
   * Default Style Selector element properties.
   *
   * @todo Move these to configurable settings?
   *
   * @var array
   */
  protected const ELEMENT_DEFAULTS = [
    'color_prop' => 'background-color',
    'extra_classes' => '',
    'empty_option' => 'None',
    'ui_settings' => [
      'alpha_grid' => TRUE,
      'check_icon' => TRUE,
      'empty_icon' => TRUE,
      'text_icon' => FALSE,
    ],
    'ui_variant' => [
      'compact' => [
        'type' => 'round',
        'size' => 'default',
      ],
    ],
  ];

  /**
   * Style Selector field types.
   *
   * @var array
   */
  protected const FIELD_TYPES = [
    'style_selector_css_class' => 'class',
    'style_selector_css_color' => 'color',
  ];

  /**
   * Style Selector formatter IDs.
   *
   * @var array
   */
  protected const FORMATTERS = [
    'style_selector_css_class_formatter',
    'style_selector_css_color_formatter',
  ];

  /**
   * The constructor.
   */
  public function __construct(ConfigFactoryInterface $config_factory, CssColor $css_color) {
    $this->configFactory = $config_factory;
    $this->cssColor = $css_color;
    $this->config = $this->configFactory->get(self::CONFIG_NAME);
  }

  /**
   * Get the current node from a RouteMatch.
   *
   * @param CurentRouteMatch $route_match
   *   The route match object.
   *
   * @return mixed
   *   A node object if successful, else NULL.
   */
  public function getNodeFromRoute(CurrentRouteMatch $route_match) {
    $node = NULL;

    switch ($route_match->getRouteName()) {
      case 'entity.node.canonical':
      case 'layout_builder.overrides.node.view':
        $node = $route_match->getParameter('node');
        break;

      case 'entity.node.revision':
        $node = $route_match->getParameter('node_revision');
        break;

      case 'entity.node.preview':
        $node = $route_match->getParameter('node_preview');
        break;

      default:
        $node = NULL;
    }

    if ($node instanceof NodeInterface) {
      return $node;
    }
    else {
      return NULL;
    }
  }

  /**
   * Get a value from the default properties array.
   *
   * @param string $name
   *   The property name.
   *
   * @return mixed
   *   The default value, or NULL if not found.
   */
  public function getDefault(string $name) {
    return self::ELEMENT_DEFAULTS[$name] ?? NULL;
  }

  /**
   * Get all default properties.
   *
   * @return array
   *   The full default properties array.
   */
  public function getDefaults() {
    return self::ELEMENT_DEFAULTS;
  }

  /**
   * Get an array of valid CSS class identifiers.
   *
   * @param mixed $classes
   *   An array or space-separated list of class identifiers.
   * @param string $prefix
   *   Optional. String value to prepend to each class identifier.
   *
   * @return array
   *   An array of valid, sanitized CSS class identifiers.
   */
  public function getClasses(mixed $classes, string $prefix = '') {
    $validated = [];
    if (!empty($classes)) {
      if (is_array($classes)) {
        $validated = $this->sanitizeClasses($classes, $prefix);
      }
      elseif (is_string($classes)) {
        $validated = $this->sanitizeClasses(array_filter(explode(' ', $classes)), $prefix);
      }
    }
    return $validated;
  }

  /**
   * Get an array of valid CSS color values.
   *
   * @param array $colors
   *   An array of color values to be validated.
   *
   * @return array
   *   The array of validated CSS color values.
   */
  public function getColors(array $colors) {
    $validated = [];
    foreach ($colors as $color) {
      if ($valid_color = $this->cssColor->getFormSafeColorValue($color)) {
        $validated[] = $valid_color;
      }
    }
    return $validated;
  }

  /**
   * Get a space-separated list of valid class identifiers.
   *
   * @param mixed $classes
   *   List of class identifiers to be sanitized.
   * @param string $prefix
   *   Optional. String value to prepend to each class identifier.
   *
   * @return string
   *   A space-separated list of sanitized CSS class identifiers.
   */
  public function getClassList(mixed $classes, string $prefix = '') {
    return implode(' ', $this->getClasses($classes, $prefix));
  }

  /**
   * Validate/sanitize and array of CSS class identifiers.
   *
   * @param array $classes
   *   Array of class identifieres to be sanitized.
   * @param string $prefix
   *   Options. Stirng value to prepend to each class identifier.
   *
   * @return array
   *   An array of valid CSS class identiriers.
   */
  public function sanitizeClasses(array $classes, string $prefix = '') {
    $sanitized = [];
    foreach ($classes as $class) {
      $sanitized[] = Html::getClass($prefix . $class);
    }
    return $sanitized;
  }

  /**
   * Discover entity fields using the specified formatter.
   *
   * @param \Drupal\Core\Entity\Display\EntityViewDisplayInterface $display
   *   An entity view display object.
   * @param \Drupal\Core\Entity\FieldableEntityInterface $entity
   *   Entity object.
   * @param array $formatter_types
   *   An array of formatter IDs to searchr for. Defaults to all current
   *   Style Selctor formatters.
   *
   * @return array
   *   An associative array of field names with additional field info:
   *   - settings: Array of settings configured in the formatter.
   *   - type: The field type ('class' or 'color').
   *   - classes: Array of module-defined classes for the field type.
   */
  public function getStyleSelectorFields(EntityViewDisplayInterface $display, FieldableEntityInterface $entity, array $formatter_types = self::FORMATTERS) {
    // Get extra classes from module config.
    $config = $this->config;
    $extra_classes = [
      'class' => $config->get('extra_css_classes'),
      'color' => $config->get('extra_color_classes'),
    ];

    $fields = [];

    // Get fields configured by Layout Builder.
    if ($display instanceof LayoutEntityDisplayInterface && $display->isLayoutBuilderEnabled()) {
      // Get entity-specific sections when the layout is customized.
      if ($display->isOverridable()
        && $entity->hasField('layout_builder__layout')
      ) {
        $sections = $entity->get('layout_builder__layout')->getSections();
      }
      // Get default sections otherwise.
      if (empty($sections)) {
        $sections = $display->getSections();
      }

      foreach ($sections as $section) {
        foreach ($section->getComponents() as $component) {
          $config = $component->get('configuration');

          // Only fields that use the specified formatter.
          if (isset($config['id']) && isset($config['formatter']['type'])) {
            $formatter = $config['formatter'];
            if (in_array($formatter['type'], $formatter_types)) {
              // Extract field name from the component ID.
              [, , , $name] = explode(':', $config['id']);
              if (!empty($name)) {
                // Only include Style Selector CSS Class field type.
                if ($type = $this->getStyleSelectorFieldType($name, $entity)) {
                  // $fields[$name]['settings'] ??= [];
                  $fields[$name] = [
                    'settings' => array_merge($fields[$name]['settings'] ?? [], $formatter['settings'] ?? []),
                    'type' => $type,
                    'classes' => $extra_classes[$type],
                  ];
                }
              }
            }
          }
        }
      }
    }

    // Get fields from standard manage display form.
    else {
      foreach ($display->getComponents() as $name => $component) {
        if (isset($component['type']) && in_array($component['type'], $formatter_types)) {
          // Only include Style Selector CSS Class field type.
          if ($type = $this->getStyleSelectorFieldType($name, $entity)) {
            // $fields[$name]['settings'] ??= [];
            $fields[$name] = [
              'settings' => array_merge($fields[$name]['settings'] ?? [], $component['settings'] ?? []),
              'type' => $type,
              'classes' => $extra_classes[$type],
            ];
          }
        }
      }
    }

    return $fields;
  }

  /**
   * Get the validated CSS class or color values for a field.
   *
   * @param \Drupal\Core\Entity\FieldableEntityInterface $entity
   *   The parent entity for the field.
   * @param string $field_name
   *   The field to get values for.
   *
   * @return array
   *   An array of validated CSS class identifiers or color values.
   */
  public function getStyleSelectorFieldValues(FieldableEntityInterface $entity, string $field_name) {
    $values = [];
    if ($field_type = $this->getStyleSelectorFieldType($field_name, $entity)) {
      $field_values = $this->getFieldValues($entity->get($field_name)->getValue());
      if ($field_values) {
        if ($field_type == 'class') {
          $values = $this->getClasses($field_values);
        }
        elseif ($field_type == 'color') {
          $values = $this->getColors($field_values);
        }
      }
    }
    return $values;
  }

  /**
   * Get an array of values from a Drupal field value.
   *
   * EntityField::getValue() returns an indexed array of key, value pairs, e.g.:
   * [
   *  0: ['value': 'an actual value'],
   *  1: ['value': 'another actual value'],
   * ]
   * This method returns an array of those values. Empty if none.
   *
   * @param mixed $field_value
   *   Result of a call to EntityField::getValue().
   *
   * @return array
   *   An array of the actual RAW values.
   */
  public function getFieldValues(mixed $field_value) {
    $values = [];
    if ($field_value) {
      foreach ($field_value as $value) {
        $values[] = $value['value'];
      }
    }
    return $values;
  }

  /**
   * Determine if a field is one of the Style Selector custom types.
   *
   * @param string $field_name
   *   The name of the field to check for.
   * @param Drupal\Core\Entity\FieldableEntityInterface $entity
   *   The entity to check.
   *
   * @return mixed
   *   Returns a short name for the field type if the field is a Style
   *   Selector field type. FALSE if it is not.
   */
  private function getStyleSelectorFieldType(string $field_name, FieldableEntityInterface $entity) {
    $field = ($entity->hasField($field_name)) ? $entity->get($field_name) : NULL;
    if ($field) {
      return self::FIELD_TYPES[$field->getFieldDefinition()->getType()] ?? FALSE;
    }
    return FALSE;
  }

}
