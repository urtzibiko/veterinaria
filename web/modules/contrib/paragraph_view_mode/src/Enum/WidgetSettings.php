<?php

declare(strict_types=1);

namespace Drupal\paragraph_view_mode\Enum;

/**
 * The pseudo enumerable providing widget settings.
 *
 * @todo Make it the real enum once we will define module compatibility to PHP ^8.1.
 */
final class WidgetSettings {

  public const DEFAULT_VIEW_MODE = 'default_view_mode';

  public const FORM_MODE_BIND = 'form_mode_bind';

  public const VIEW_MODES = 'view_modes';

}
