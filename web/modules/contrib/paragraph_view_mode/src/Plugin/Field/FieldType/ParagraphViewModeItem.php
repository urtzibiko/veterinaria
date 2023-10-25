<?php

namespace Drupal\paragraph_view_mode\Plugin\Field\FieldType;

use Drupal\Core\Field\Plugin\Field\FieldType\StringItem;

/**
 * Defines a 'paragraph_view_mode' entity field type.
 *
 * @FieldType(
 *   id = "paragraph_view_mode",
 *   label = @Translation("Paragraph view mode"),
 *   description = @Translation("A field containing paragraph view mode value"),
 *   category = @Translation("Manage display"),
 *   default_widget = "paragraph_view_mode",
 *   default_formatter = "paragraph_view_mode"
 * )
 */
class ParagraphViewModeItem extends StringItem {}
