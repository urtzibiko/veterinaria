<?php

declare(strict_types=1);

namespace Drupal\paragraph_view_mode\Matcher;

use Drupal\Core\Entity\EntityInterface;
use Drupal\paragraph_view_mode\Checker\WidgetSettingsCheckerInterface;
use Drupal\paragraph_view_mode\Enum\ViewModes;
use Drupal\paragraph_view_mode\StorageManagerInterface;
use Drupal\paragraphs\ParagraphInterface;

/**
 * Matches the given display mode with the target one.
 */
class DisplayModeMatcher implements DisplayModeMatcherInterface {

  /**
   * Widget settings checker.
   *
   * @var \Drupal\paragraph_view_mode\Checker\WidgetSettingsCheckerInterface
   */
  protected $settingsChecker;

  /**
   * Creates the matcher instance.
   *
   * @param \Drupal\paragraph_view_mode\Checker\WidgetSettingsCheckerInterface $settings_checker
   */
  public function __construct(WidgetSettingsCheckerInterface $settings_checker) {
    $this->settingsChecker = $settings_checker;
  }

  /**
   * {@inheritdoc}
   */
  public function matchFormForModeAndEntity(string $mode, EntityInterface $entity): ?string {
    $matched_mode = $this->matchViewForModeAndEntity($mode, $entity);

    if (NULL === $matched_mode) {
      return $matched_mode;
    }

    if (FALSE === $this->settingsChecker->hasFormModeBindEnabled($mode, $entity)) {
      return NULL;
    }

    return $matched_mode;
  }

  /**
   * {@inheritdoc}
   */
  public function matchViewForModeAndEntity(string $mode, EntityInterface $entity): ?string {
    if (
      FALSE === $this->isAllowedMode($mode)
      || FALSE === $this->isSupportedEntity($entity)
    ) {
      return NULL;
    }

    /** @var ParagraphInterface $paragraph */
    $paragraph = $entity;
    if (FALSE === $this->hasViewModeField($paragraph)) {
      return NULL;
    }

    return $paragraph->get(StorageManagerInterface::FIELD_NAME)->value ?: $mode;
  }

  /**
   * Checks if the matched entity is the paragraph type.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The matched entity.
   *
   * @return bool
   *   True if is paragraph, false otherwise.
   */
  private function isSupportedEntity(EntityInterface $entity): bool {
    return $entity instanceof ParagraphInterface;
  }

  /**
   * Checks if the paragraph has view mode field.
   *
   * @param \Drupal\paragraphs\ParagraphInterface $paragraph
   *   The paragraph.
   *
   * @return bool
   */
  private function hasViewModeField(ParagraphInterface $paragraph): bool {
    return $paragraph->hasField(StorageManagerInterface::FIELD_NAME);
  }

  /**
   * Checks if the display mode is allowed.
   *
   * @param string $mode
   *   The mode to be checked.
   *
   * @return bool
   *   True if the mode is allowed, false otherwise.
   */
  private function isAllowedMode(string $mode): bool {
    return ViewModes::PREVIEW !== $mode;
  }

}
