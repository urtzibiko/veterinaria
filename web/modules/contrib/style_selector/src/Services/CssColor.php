<?php

namespace Drupal\style_selector\Services;

use Drupal\Component\Render\FormattableMarkup;

/**
 * Class CssColor.
 *
 * A collection of methods for working with CSS color values.
 */
class CssColor {

  /**
   * Color format regular expressions.
   *
   * Formats derived from Symfony CssColor Constraint
   * https://symfony.com/doc/current/reference/constraints/CssColor.html.
   *
   * @var array
   */
  protected const FORMATS = [
    'hex_long' => '/^#[0-9a-f]{6}$/i',
    'hex_long_alpha' => '/^#[0-9a-f]{8}$/i',
    'hex_short' => '/^#[0-9a-f]{3}$/i',
    'hex_short_alpha' => '/^#[0-9a-f]{4}$/i',
    'basic_named' => '/^(black|silver|gray|white|maroon|red|purple|fuchsia|green|lime|olive|yellow|navy|blue|teal|aqua)$/i',
    'extended_named' => '/^(aliceblue|antiquewhite|aqua|aquamarine|azure|beige|bisque|black|blanchedalmond|blue|blueviolet|brown|burlywood|cadetblue|chartreuse|chocolate|coral|cornflowerblue|cornsilk|crimson|cyan|darkblue|darkcyan|darkgoldenrod|darkgray|darkgreen|darkgrey|darkkhaki|darkmagenta|darkolivegreen|darkorange|darkorchid|darkred|darksalmon|darkseagreen|darkslateblue|darkslategray|darkslategrey|darkturquoise|darkviolet|deeppink|deepskyblue|dimgray|dimgrey|dodgerblue|firebrick|floralwhite|forestgreen|fuchsia|gainsboro|ghostwhite|gold|goldenrod|gray|green|greenyellow|grey|honeydew|hotpink|indianred|indigo|ivory|khaki|lavender|lavenderblush|lawngreen|lemonchiffon|lightblue|lightcoral|lightcyan|lightgoldenrodyellow|lightgray|lightgreen|lightgrey|lightpink|lightsalmon|lightseagreen|lightskyblue|lightslategray|lightslategrey|lightsteelblue|lightyellow|lime|limegreen|linen|magenta|maroon|mediumaquamarine|mediumblue|mediumorchid|mediumpurple|mediumseagreen|mediumslateblue|mediumspringgreen|mediumturquoise|mediumvioletred|midnightblue|mintcream|mistyrose|moccasin|navajowhite|navy|oldlace|olive|olivedrab|orange|orangered|orchid|palegoldenrod|palegreen|paleturquoise|palevioletred|papayawhip|peachpuff|peru|pink|plum|powderblue|purple|red|rosybrown|royalblue|saddlebrown|salmon|sandybrown|seagreen|seashell|sienna|silver|skyblue|slateblue|slategray|slategrey|snow|springgreen|steelblue|tan|teal|thistle|tomato|turquoise|violet|wheat|white|whitesmoke|yellow|yellowgreen)$/i',
    'system_colors' => '/^(Canvas|CanvasText|LinkText|VisitedText|ActiveText|ButtonFace|ButtonText|ButtonBorder|Field|FieldText|Highlight|HighlightText|SelectedItem|SelectedItemText|Mark|MarkText|GrayText)$/i',
    'keywords' => '/^(transparent|currentColor)$/i',
    'rgb' => '/^rgb\(\s*(0|255|25[0-4]|2[0-4]\d|1\d\d|0?\d?\d),\s*(0|255|25[0-4]|2[0-4]\d|1\d\d|0?\d?\d),\s*(0|255|25[0-4]|2[0-4]\d|1\d\d|0?\d?\d)\s*\)$/i',
    'rgba' => '/^rgba\(\s*(0|255|25[0-4]|2[0-4]\d|1\d\d|0?\d?\d),\s*(0|255|25[0-4]|2[0-4]\d|1\d\d|0?\d?\d),\s*(0|255|25[0-4]|2[0-4]\d|1\d\d|0?\d?\d),\s*(0|0?\.\d+|1(\.0)?)\s*\)$/i',
    'hsl' => '/^hsl\(\s*(0|360|35\d|3[0-4]\d|[12]\d\d|0?\d?\d),\s*(0|100|\d{1,2})%,\s*(0|100|\d{1,2})%\s*\)$/i',
    'hsla' => '/^hsla\(\s*(0|360|35\d|3[0-4]\d|[12]\d\d|0?\d?\d),\s*(0|100|\d{1,2})%,\s*(0|100|\d{1,2})%,\s*(0|0?\.\d+|1(\.0)?)\s*\)$/i',
  ];

  /**
   * Validate a CSS color value.
   *
   * @param string $value
   *   The value to validate.
   * @param array $formats
   *   Optional. An array of CSS Color formats to validate agains. All are
   *   checked by default.
   *
   * @return bool
   *   Returns TRUE if the value validates as one of the CSS color formats.
   */
  public function validateCssColor($value, array $formats = []) {
    $patterns = [];
    if (!$formats) {
      $patterns = array_values(self::FORMATS);
    }
    else {
      $patterns = array_intersect_key(self::FORMATS, array_flip($formats));
    }

    foreach ($patterns as $regex) {
      if (preg_match($regex, (string) $value)) {
        return TRUE;
      }
    }
    return FALSE;
  }

  /**
   * Validate hexidecimal CSS color value.
   *
   * @param string $value
   *   The value to validate.
   *
   * @return bool
   *   Returns TRUE if the value validates as one of the four hex formats.
   */
  public function validateHex($value) {
    return $this->validateCssColor($value, [
      'hex_long',
      'hex_long_alpha',
      'hex_short',
      'hex_short_alpha',
    ]);
  }

  /**
   * Get a color value that is suitable for use as a Form API option value.
   *
   * Since values containing '#' represent properties in the Form API, valid
   * hexidecimal values are converted to rgb(a).
   * Any other valid, supported CSS color value is returned as-is.
   * An empty string indicates the input was not a valid CSS color value.
   *
   * @param string $value
   *   A CSS color value to make safe for use in the Form API.
   *
   * @return string
   *   Sanitized string suitable for use as CSS color value.
   */
  public function getFormSafeColorValue(string $value) {
    $color = '';

    // If it's hex, return formatted rgb color value.
    if ($this->validateHex($value)) {
      $color = $this->hexToRgbCss($value);
    }
    elseif ($this->validateCssColor($value)) {
      $color = $value;
    }

    return $color;
  }

  /**
   * Convert hex color to formatted rgb(a) string suitable or use in CSS.
   *
   * @param string $hex
   *   The hex string to convert.
   *
   * @return string
   *   A valid rgb css color value -- e.g. rgb(0,20,155).
   */
  public function hexToRgbCss(string $hex) {
    $rgb = $this->hexToRgb($hex);
    if (isset($rgb['alpha'])) {
      $template = 'rgba(@red, @green, @blue, @alpha)';
    }
    else {
      $template = 'rgb(@red, @green, @blue)';
    }
    $rgb_css = new FormattableMarkup($template, [
      '@red' => $rgb['red'],
      '@green' => $rgb['green'],
      '@blue' => $rgb['blue'],
      '@alpha' => $rgb['alpha'] ?? '',
    ]);

    return $rgb_css->__toString();
  }

  /**
   * Convert hexidecimal CSS color to RGB/A format.
   *
   * Converts a hexadecimal color string like '#abc' or '#aabbcc' to the
   * corresponding RGB/A value.
   *
   * @param string $hex
   *   The hexadecimal color string to parse.
   * @param bool $include_alpha
   *   Include alpha in converted value if present in $hex?
   *   Defaults to TRUE.
   *
   * @return array
   *   An array containing the values for 'red', 'green', 'blue' and,
   *   optionally, 'alpha'.
   *
   * @throws \InvalidArgumentException
   */
  public function hexToRgb(string $hex, bool $include_alpha = TRUE) {
    if (!$this->validateHex($hex)) {
      throw new \InvalidArgumentException("'$hex' is not a valid hex value.");
    }

    $hex = $this->normalizeHex($hex, $include_alpha);

    // Remove '#' prefixes.
    $hex = ltrim($hex, '#');

    // Convert hex pairs to decimal values.
    $hex_vals = str_split($hex, 2);
    $rgb = array_map('hexdec', $hex_vals);

    $result = [
      'red' => $rgb[0],
      'green' => $rgb[1],
      'blue' => $rgb[2],
    ];

    if (isset($rgb[3]) && $include_alpha) {
      $result['alpha'] = round($rgb[3] / 255, 2);
    }

    return $result;
  }

  /**
   * Transform hexideicmal to a 'standard' size.
   *
   * Normalizes a hexadecimal color string like '#abc' or '#aabbccdd' to
   * #aabbcc. Optionally return alpha information if alpha is included in
   * the input hex value.
   *
   * @param string $hex
   *   The hexadecimal color string to normalize.
   * @param bool $include_alpha
   *   If alpha exists in the input, include it in the normalized value?
   *   Defaults to FALSE.
   *
   * @return string
   *   The normalized hex value.
   *
   * @throws \InvalidArgumentException
   */
  public function normalizeHex(string $hex, bool $include_alpha = FALSE) {
    if (!$this->validateHex($hex)) {
      throw new \InvalidArgumentException("'$hex' is not a valid hex value.");
    }

    // Standard hex, return as-is.
    if ($this->validateCssColor($hex, ['hex_long'])) {
      return $hex;
    }

    // Long hex with alpha.
    if ($this->validateCssColor($hex, ['hex_long_alpha'])) {
      if ($include_alpha) {
        return $hex;
      }
      else {
        $hex = substr($hex, 0, 7);
      }
    }

    // Convert hex short to hex long -- e.g. '#abc' to '#aabbcc'.
    if ($this->validateCssColor($hex, ['hex_short'])) {
      $hex = $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2] . $hex[3] . $hex[3];
    }

    // Convert hex short with alpha to hex long, or hex long with alpha.
    if ($this->validateCssColor($hex, ['hex_short_alpha'])) {
      if ($include_alpha) {
        $hex = $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2] . $hex[3] . $hex[3] . $hex[4] . $hex[4];
      }
      else {
        $hex = $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2] . $hex[3] . $hex[3];
      }
    }

    return $hex;
  }

}
