<?php

declare(strict_types=1);

namespace Drupal\paragraph_view_mode\Checker;

use Drupal\paragraphs\ParagraphInterface;

/**
 * The widget settings checker interface.
 */
interface WidgetSettingsCheckerInterface {

  /**
   * Checks if the form mode bind feature is enabled for the given paragraph.
   *
   * @param string $form_mode
   *   The form mode.
   * @param \Drupal\paragraphs\ParagraphInterface $paragraph
   *   The paragraph entity.
   *
   * @return bool
   */
  public function hasFormModeBindEnabled(
    string $form_mode,
    ParagraphInterface $paragraph
  ): bool;

}
