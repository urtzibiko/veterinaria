<?php

declare(strict_types=1);

namespace Drupal\paragraph_view_mode\Matcher;


use Drupal\Core\Entity\EntityInterface;

/**
 * Defines the interface for display mode matchers.
 */
interface DisplayModeMatcherInterface {

  /**
   * Handles the form mode matching.
   *
   * @param string $mode
   *   The form mode to match.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity related to the form display.
   *
   * @return string|null
   *   The matched mode or null.
   */
  public function matchViewForModeAndEntity(
    string $mode,
    EntityInterface $entity
  ): ?string;

  /**
   * Handles the view mode matching.
   *
   * @param string $mode
   *   The view mode to match.
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity related to the view display.
   *
   * @return string|null
   *   The matched mode or null.
   */
  public function matchFormForModeAndEntity(
    string $mode,
    EntityInterface $entity
  ): ?string;

}
