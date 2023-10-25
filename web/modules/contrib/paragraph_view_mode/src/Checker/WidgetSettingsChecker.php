<?php

declare(strict_types=1);

namespace Drupal\paragraph_view_mode\Checker;

use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\paragraph_view_mode\Enum\WidgetSettings;
use Drupal\paragraph_view_mode\StorageManagerInterface;
use Drupal\paragraphs\ParagraphInterface;

/**
 * Provides the ability to checks the widget specific settings.
 */
class WidgetSettingsChecker implements WidgetSettingsCheckerInterface {

  /**
   * The entity display repository.
   *
   * @var EntityDisplayRepositoryInterface
   */
  protected $displayRepository;

  /**
   * Creates the widget settings checker instance.
   *
   * @param \Drupal\Core\Entity\EntityDisplayRepositoryInterface $entity_display_repository
   *   The entity display repository.
   */
  public function __construct(EntityDisplayRepositoryInterface $entity_display_repository) {
    $this->displayRepository = $entity_display_repository;
  }

  /**
   * {@inheritdoc}
   */
  public function hasFormModeBindEnabled(string $form_mode, ParagraphInterface $paragraph): bool {
    $form_display = $this
      ->displayRepository
      ->getFormDisplay($paragraph->getEntityTypeId(), $paragraph->bundle(), $form_mode);

    $view_mode_field = $form_display
      ->getComponent(StorageManagerInterface::FIELD_TYPE);


    return (bool) ($view_mode_field['settings'][WidgetSettings::FORM_MODE_BIND] ?? NULL);
  }

}
