<?php

namespace Drupal\paragraph_view_mode;

/**
 * Provides interface for paragraph view modes.
 *
 * @package Drupal\paragraph_view_mode
 *
 * @deprecated in paragraph_view_mode:3.1.0 and is removed from
 * paragraph_view_mode:3.2.0.
 * Use Drupal\paragraph_view_mode\Enum\ViewModes instead.
 *
 */
interface ViewModeInterface {

  /**
   * Default view mode.
   */
  const DEFAULT = 'default';

  /**
   * Special view mode used as a preview.
   */
  const PREVIEW = 'preview';

}
