<?php

namespace Drupal\paragraph_view_mode\Plugin\Field\FieldWidget;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget\StringTextfieldWidget;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;
use Drupal\paragraph_view_mode\Enum\ViewModes;
use Drupal\paragraph_view_mode\Enum\WidgetSettings;
use Drupal\paragraph_view_mode\StorageManagerInterface;
use Drupal\paragraphs\Entity\ParagraphsType;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Plugin implementation of the 'paragraph_view_mode' widget.
 *
 * @FieldWidget(
 *   id = "paragraph_view_mode",
 *   label = @Translation("Paragraph view mode"),
 *   field_types = {
 *     "paragraph_view_mode",
 *   }
 * )
 */
class ParagraphViewModeWidget extends StringTextfieldWidget {

  /**
   * Current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected $request;

  /**
   * Entity form builder.
   *
   * @var \Drupal\Core\Entity\EntityFormBuilderInterface
   */
  protected $entityFormBuilder;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $instance->request = $container->get('request_stack')->getCurrentRequest();
    $instance->entityFormBuilder = $container->get('entity.form_builder');

    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      WidgetSettings::VIEW_MODES => self::getAvailableViewModes(),
      WidgetSettings::DEFAULT_VIEW_MODE => ViewModes::DEFAULT,
      WidgetSettings::FORM_MODE_BIND => TRUE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {

    $element[WidgetSettings::VIEW_MODES] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Available view modes'),
      '#description' => $this->getViewModesFieldDescription(),
      '#options' => $this->defaultSettings()[WidgetSettings::VIEW_MODES],
      '#default_value' => array_keys($this->getEnabledViewModes()),
      '#required' => FALSE,
      '#ajax' => [
        'callback' => [__CLASS__, 'defaultViewModes'],
        'event' => 'change',
        'wrapper' => 'paragraph-view-mode-default',
      ],
    ];

    if ($this->getSetting(WidgetSettings::VIEW_MODES)) {
      $element[WidgetSettings::DEFAULT_VIEW_MODE] = [
        '#type' => 'select',
        '#title' => $this->t('Default value'),
        '#description' => $this->t('View mode to be used as a default field value.'),
        '#options' => $element[WidgetSettings::VIEW_MODES]['#options'],
        '#default_value' => $this->getSetting(WidgetSettings::DEFAULT_VIEW_MODE),
        '#required' => FALSE,
        '#weight' => 2,
        '#prefix' => '<div id="paragraph-view-mode-default">',
        '#suffix' => '</div>',
      ];
    }

    $element[WidgetSettings::FORM_MODE_BIND] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Bind with the form mode'),
      '#description' => $this->t('It will reload the paragraph form on change event using ajax only' .
        ' if there is a form mode with exactly the same machine name as the view mode.'),
      '#default_value' => $this->getSetting(WidgetSettings::FORM_MODE_BIND),
      '#required' => FALSE,
      '#weight' => 3,
    ];

    return $element;
  }

  /**
   * Ajax callback for updating the default view mode options.
   *
   * @param array $form
   *   Form structure.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   *
   * @return array
   *   Default view mode form element.
   */
  public static function defaultViewModes(array $form, FormStateInterface $form_state) {
    $checkboxes = $form_state->getTriggeringElement();
    $element = NestedArray::getValue($form, array_slice($checkboxes['#array_parents'], 0, count($checkboxes['#array_parents']) - 2));

    $options = array_intersect_key($element[WidgetSettings::VIEW_MODES]['#options'], $element[WidgetSettings::VIEW_MODES]['#value']);

    $element[WidgetSettings::DEFAULT_VIEW_MODE]['#options'] = empty($options) ? $element[WidgetSettings::VIEW_MODES]['#options'] : $options;

    return $element[WidgetSettings::DEFAULT_VIEW_MODE];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $settings = $this->getEnabledViewModes();

    if (empty($settings)) {
      $message = $this->t('No view modes enabled, "@default" view mode will be used instead.', [
        '@default' => ViewModes::DEFAULT,
      ]);
    }
    else {
      $message = $this->t('Available view modes: @types', ['@types' => implode(', ', $settings)]);
    }

    $summary[] = $message;

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);

    $wrapper_id = 'view-mode-paragraph-' . $items->getEntity()->uuid();

    $element['value'] = [
      '#title' => $items->getFieldDefinition()->getLabel(),
      '#type' => 'select',
      '#default_value' => $items[$delta]->value ?? $this->getSetting(WidgetSettings::DEFAULT_VIEW_MODE),
      '#options' => $this->getEnabledViewModes() ?: $this->getDefaultOption(),
      '#required' => TRUE,
      '#weight' => 1,
    ] + $element['value'];

    if ($this->getSetting(WidgetSettings::FORM_MODE_BIND)) {
      $element['value'] = [
        '#paragraph' => $items->getEntity(),
        '#ajax' => [
          'callback' => [$this, 'reloadSubform'],
          'event' => 'change',
          'wrapper' => $wrapper_id,
        ],
      ] + $element['value'];

      $form['#prefix'] =  '<div id="' . $wrapper_id . '">';
      $form['#suffix'] = '</div>';
    }

    return $element;
  }

  /**
   * Ajax callback for reloading the paragraph subform.
   *
   * The reload of the subform basically triggers the
   * hook_entity_form_mode_alter under the hood.
   * We are using this fact to alter the form mode.
   *
   * @param array $form
   *   The structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The state of the form.
   *
   * @return array
   *   The paragraph subform.
   */
  public function reloadSubform(array &$form, FormStateInterface $form_state): array {
    $triggering_element = $form_state->getTriggeringElement();
    $element = NestedArray::getValue($form, $triggering_element['#array_parents']);

    $parents_reversed = array_reverse($element['#array_parents'], TRUE);
    foreach ($parents_reversed as $key => $parent) {
      if ($parent === 'subform') {
        break;
      }
      unset($parents_reversed[$key]);
    }
    $parents = array_reverse($parents_reversed, TRUE);

    return NestedArray::getValue($form, $parents);
  }


  /**
   * Getter for available view modes in paragraph entity type.
   *
   * @return array
   *   Associative array of view mode machine names and labels.
   */
  protected static function getAvailableViewModes() {
    $request = \Drupal::request();
    $entity_display_respository = \Drupal::service('entity_display.repository');
    $paragraph_type = self::getParagraphsTypeFromRequest($request);

    $entity_id = StorageManagerInterface::ENTITY_TYPE;

    if ($paragraph_type instanceof ParagraphsType) {
      return $entity_display_respository->getViewModeOptionsByBundle($entity_id, $paragraph_type->id());
    }

    return $entity_display_respository->getViewModeOptions($entity_id);
  }

  /**
   * Getter for enabled view modes.
   *
   * @return array
   *   Associative array of view mode machine names and labels.
   */
  protected function getEnabledViewModes() {
    $availableViewModes = self::getAvailableViewModes();
    $currentViewModes = array_filter($this->getSetting(WidgetSettings::VIEW_MODES));

    return array_intersect_key($availableViewModes, $currentViewModes);
  }

  /**
   * Provides default option for the form elements.
   *
   * @return array
   *   Default view mode option.
   */
  protected function getDefaultOption() {
    return [ViewModes::DEFAULT => $this->t('Default')];
  }

  /**
   * Get ParagraphsType entity object from request.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   Current request object.
   *
   * @return \Drupal\paragraphs\Entity\ParagraphsType|null
   *   ParagraphsType entity.
   */
  protected static function getParagraphsTypeFromRequest(Request $request): ?ParagraphsType {
    return $request->attributes->get('paragraphs_type', NULL);
  }

  /**
   * Getter for 'view modes' field description.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   *   Field description.
   */
  protected function getViewModesFieldDescription(): TranslatableMarkup {
    $paragraphs_type = self::getParagraphsTypeFromRequest($this->request);

    $url_route = implode('.', [
      'entity.entity_view_display',
      StorageManagerInterface::ENTITY_TYPE,
      ViewModes::DEFAULT,
    ]);
    $url_parameters = ['paragraphs_type' => $paragraphs_type->id()];
    $url_options = ['fragment' => 'edit-modes'];

    $url = Url::fromRoute($url_route, $url_parameters, $url_options);

    return $this->t('It is using only the view modes enabled in the <strong>CUSTOM DISPLAY SETTINGS</strong> section under the <a href="@url">Manage Display</a> tab.', ['@url' => $url->toString()]);
  }

}
