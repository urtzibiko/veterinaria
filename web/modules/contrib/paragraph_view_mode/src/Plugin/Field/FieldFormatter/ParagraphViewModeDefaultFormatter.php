<?php

namespace Drupal\paragraph_view_mode\Plugin\Field\FieldFormatter;

use Drupal\text\Plugin\Field\FieldFormatter\TextDefaultFormatter;

/**
 * Plugin implementation of the 'paragraph_view_mode' formatter.
 *
 * @FieldFormatter(
 *   id = "paragraph_view_mode",
 *   label = @Translation("Paragraph View Mode"),
 *   field_types = {
 *     "paragraph_view_mode",
 *   }
 * )
 */
class ParagraphViewModeDefaultFormatter extends TextDefaultFormatter {}
