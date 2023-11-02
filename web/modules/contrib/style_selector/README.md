Style Selector provides user-friendly form elements for choosing CSS classes or
colors from a list of allowed values.

## Features
- A Form API element for use your own forms (e.g.
[layout with custom settings forms](https://www.drupal.org/docs/drupal-apis/layout-api/how-to-register-layouts#using-class-key)).
- Two custom field types&mdash;CSS Class and CSS Color&mdash;for use with
entities, blocks, etc.
- A CSS Class formatter that adds the chosen class to entity wrapper.
- A CSS Color formatter that sets an inline color style on the entity wrapper
(color or background-color).
- Additional classes can be configured in both the module and formatter
settings.
- Claro-inspired widget provides a visual representation of the class/color.
- Support for a wide range of possible color value formats&mdash;hexadecimal
(stored as RGB/A), RGB/A, HSL/A, named colors, system colors, color keywords
(transparent, currentColor).
- Tested with the most popular admin themes: Seven, Claro, Gin, Adminimal.

## Requirements/Limitations
- CSS classes for use with Style Selector must be defined in a separate library
(theme or module).
- CSS colors entered as hex values will be converted and stored as RGB/A.

### Thanks
Initial inspiration and approach came from
[ColorWidget](https://www.drupal.org/project/colorwidget). Style Selector
started as a patch, but quickly grew into something more.
