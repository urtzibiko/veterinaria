<?php

declare(strict_types=1);

namespace Drupal\paragraph_view_mode\Enum;

/**
 * The pseudo enumerable providing the paragraph view modes..
 *
 * @todo Make it the real enum once we will define module compatibility to PHP ^8.1.
 */
final class ViewModes {

  /**
   * Default view mode.
   */
  public const DEFAULT = 'default';

  /**
   * Special view mode used as a preview.
   */
  public const PREVIEW = 'preview';

}
