<?php

namespace Drupal\style_selector\Form;

use Drupal\Component\Utility\Html;
use Drupal\Component\Utility\Xss;
use Drupal\Core\Asset\LibraryDiscovery;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class ModuleConfigForm.
 *
 * Configuration settings for Style Selector module.
 */
class ModuleConfigForm extends ConfigFormBase {

  /**
   * The injected library discovery service.
   *
   * @var Drupal\Core\Asset\LibraryDiscovery
   */
  protected $libraryDiscovery;

  /**
   * Config settings.
   *
   * @var string
   */
  const SETTINGS = 'style_selector.settings';

  /**
   * Class constructor.
   */
  public function __construct(ConfigFactoryInterface $config_factory, LibraryDiscovery $library_discovery) {
    parent::__construct($config_factory);
    $this->libraryDiscovery = $library_discovery;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('library.discovery')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      static::SETTINGS,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'style_selector_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config(static::SETTINGS);

    $form['libraries'] = [
      '#type' => 'details',
      '#title' => 'Libraries',
      '#open' => TRUE,
    ];

    $form['libraries']['shared_libraries'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Shared Libraries'),
      '#rows' => 3,
      '#default_value' => $this->getStringFromConfigData($config->get('shared_libraries'), "\n"),
      '#description' => $this->t(
        'One or more libraries (one per line) that will be loaded <strong>BOTH</strong> when a Style Selector CSS Class field is displayed with the CSS Class widget (e.g. in the admin UI) <strong>AND</strong> when a Style Selector field is rendered with the CSS Class formatter.<br/><strong>Example</strong>: <code>my_theme/background_styles</code>'
      ),
    ];

    $form['libraries']['theme_libraries'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Theme-only Libraries'),
      '#rows' => 3,
      '#default_value' => $this->getStringFromConfigData($config->get('theme_libraries'), "\n"),
      '#description' => $this->t(
        'One or more libraries (one per line) that will be loaded <strong>ONLY</strong> when a Style Selector field is rendered with the CSS Class formatter (i.e. in the <strong>user-facing theme</strong>)<br/><strong>Example</strong>: <code>my_theme/background_support_styles</code><br/>'
      ),
    ];

    $form['libraries']['admin_libraries'] = [
      '#type' => 'textarea',
      '#title' => $this->t('Admin-only Libraries'),
      '#rows' => 3,
      '#default_value' => $this->getStringFromConfigData($config->get('admin_libraries'), "\n"),
      '#description' => $this->t(
        'One or more libraries (one per line) that will be loaded <strong>ONLY</strong> when a Style Selector CSS Class field is displayed with the CSS Class widget (i.e. in the <strong>admin UI</strong>).<br/><strong>Example</strong>: <code>my_theme/style_selector_admin_styles</code>'
      ),
    ];

    $form['extra'] = [
      '#type' => 'details',
      '#title' => 'Extra class names',
      '#open' => TRUE,
    ];

    $form['extra']['css_classes'] = [
      '#type' => 'textfield',
      '#title' => $this->t('CSS Class Field Classes'),
      '#default_value' => $this->getStringFromConfigData($config->get('extra_css_classes')),
      '#description' => $this->t(
        'A space-separated list of classes to include when a CSS Class field is rendered with the CSS Class Formatter.',
      ),
    ];

    $form['extra']['color_classes'] = [
      '#type' => 'textfield',
      '#title' => $this->t('CSS Color Field Classes'),
      '#default_value' => $this->getStringFromConfigData($config->get('extra_color_classes')),
      '#description' => $this->t(
        'A space-separated list of classes to include when a CSS Color field is rendered with the CSS Color Formatter.',
      ),
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Retrieve the configuration.
    $config = $this->configFactory()->getEditable(static::SETTINGS);
    $config->set('shared_libraries', $this->textToConfigData($form_state->getValue(['shared_libraries'])));
    $config->set('theme_libraries', $this->textToConfigData($form_state->getValue(['theme_libraries'])));
    $config->set('admin_libraries', $this->textToConfigData($form_state->getValue(['admin_libraries'])));
    $config->set('extra_css_classes', $this->classSettingToConfigData($form_state->getValue(['css_classes'])));
    $config->set('extra_color_classes', $this->classSettingToConfigData($form_state->getValue(['color_classes'])));
    $config->save();

    parent::submitForm($form, $form_state);
  }

  /**
   * Convert a multiline text setting to an array of sanitized values.
   *
   * @param string $text
   *   Text string possibly containing multiple lines.
   *
   * @return array
   *   An array of sanitized values.
   */
  private function textToConfigData(string $text) {
    $values = [];
    // Normalize newlines.
    $text = str_replace(["\r\n", "\r"], "\n", $text);
    foreach (array_filter(explode("\n", $text)) as $value) {
      $values[] = Xss::filter($value);
    }
    return $values;
  }

  /**
   * Convert a list of classes to an array suitable for storage as config.
   *
   * @param mixed $setting
   *   A space-separated list of CSS classes.
   *
   * @return array
   *   Array of valid, sanitized CSS class identifiers.
   */
  private function classSettingToConfigData($setting) {
    $values = [];
    if ($setting) {
      foreach (array_filter(explode(' ', $setting)) as $value) {
        $value = Html::getClass(trim($value));
        if ($value) {
          $values[] = $value;
        }
      }
    }
    return $values;
  }

  /**
   * Convert a config array to a string.
   *
   * @param array $config
   *   The configuration item.
   * @param string $separator
   *   Separator to use when imploding the array.
   *
   * @return string
   *   A value suitable for editing or use in a text field value.
   */
  private function getStringFromConfigData(array $config, string $separator = ' ') {
    $setting = '';
    if ($config && is_array($config)) {
      $setting = implode($separator, $config);
    }
    return $setting;
  }

}
