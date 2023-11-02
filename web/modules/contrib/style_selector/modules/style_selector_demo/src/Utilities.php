<?php

namespace Drupal\style_selector_demo;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\TypedConfigManager;

/**
 * Class Utilities.
 *
 * Helpers for Style Selector Demo.
 */
class Utilities {

  /**
   * The config factory object.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The typed config object.
   *
   * @var \Drupal\Core\Config\TypedConfigManager
   */
  protected $configTyped;

  /**
   * Demo config name.
   *
   * @var string
   */
  protected const DEMO_CONFIG = 'style_selector_demo.settings';

  /**
   * Main module config name.
   *
   * @var string
   */
  protected const MODULE_CONFIG = 'style_selector.settings';

  /**
   * Field type.
   *
   * @var string
   */
  protected const FIELD_TYPES = [
    'style_selector_css_class' => 'class',
    'style_selector_css_color' => 'color',
  ];

  /**
   * The constructor.
   */
  public function __construct(ConfigFactoryInterface $config_factory, TypedConfigManager $config_typed) {
    $this->configFactory = $config_factory;
    $this->configTyped = $config_typed;
  }

  /**
   * Get the full Demo config array.
   *
   * @return array
   *   The config array, minus the '_core' value if set.
   */
  public function getDemoConfigArray() {
    $demo_config = $this->configFactory->get(self::DEMO_CONFIG)->get();
    // Remove unneeded _core value.
    unset($demo_config['_core']);
    return $demo_config;
  }

  /**
   * Convenience method to get the Demo config object.
   *
   * @return \Drupal\Core\Config\ConfigFactoryInterface
   *   The style_selector_demo config object.
   */
  public function getDemoConfig() {
    return $this->configFactory->get(self::DEMO_CONFIG);
  }

  /**
   * Convenience method to get the main module (Style Selector) config object.
   *
   * @return \Drupal\Core\Config\ConfigFactoryInterface
   *   The style_selector config object.
   */
  public function getMainConfig() {
    return $this->configFactory->get(self::MODULE_CONFIG);
  }

  /**
   * Check for module config for required libraries.
   *
   * Ensure that all the demo libraries are present in the current Style
   * Selector configuration.
   *
   * @return bool
   *   TRUE if they are all there. FALSE if any are missing.
   */
  public function getMissingLibs() {
    $definition = $this->configTyped->getDefinition(self::DEMO_CONFIG);
    $demo_config = $this->getDemoConfigArray();
    $main_config = $this->getMainConfig();

    $missing = [];

    foreach ($demo_config as $shared_key => $demo_values) {
      $current_values = $main_config->get($shared_key) ?? [];
      // Ensure each value is also present in the key's values.
      foreach ($demo_values as $required_value) {
        if ((array_search($required_value, $current_values)) === FALSE) {
          if (isset($missing[$shared_key])) {
            $missing[$shared_key]['libs'][] = $required_value;
          }
          else {
            $missing[$shared_key] = [
              'label' => $definition['mapping'][$shared_key]['label'],
              'libs' => [$required_value],
            ];
          }
        }
      }
    }

    return $missing;
  }

}
