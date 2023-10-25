<?php

namespace Drupal\paragraph_view_mode;

use Drupal\Core\Entity\EntityInterface;
use Drupal\field\FieldConfigInterface;

/**
 * Provides an interface for managing the storage.
 *
 * @package Drupal\paragraph_view_mode
 */
interface StorageManagerInterface {

  /**
   * Entity type name.
   */
  const ENTITY_TYPE = 'paragraph';

  /**
   * Module configuration name.
   */
  const CONFIG_NAME = 'paragraph_view_mode';

  /**
   * View mode field name.
   */
  const FIELD_NAME = self::CONFIG_NAME;

  /**
   * View mode field type.
   */
  const FIELD_TYPE = self::FIELD_NAME;

  /**
   * View mode field label.
   */
  const FIELD_LABEL = 'Paragraph view mode';

  /**
   * Add field to the given bundle.
   *
   * @param string $bundle
   *   Paragraph entity bundle.
   *
   * @return bool
   *   True if the field exist or was created successfully.
   */
  public function addField(string $bundle): bool;

  /**
   * Get field config.
   *
   * @param string $bundle
   *   Paragraph entity bundle.
   *
   * @return \Drupal\field\FieldConfigInterface|null
   *   Field config.
   */
  public function getField(string $bundle): ?FieldConfigInterface;

  /**
   * Delete field from the given bundle.
   *
   * @param string $bundle
   *   Paragraph entity bundle.
   *
   * @return bool
   *   True if the field does not exist or was successfully deleted.
   */
  public function deleteField(string $bundle): bool;

  /**
   * Add paragraph view mode field to paragraph entity form display.
   *
   * @param string $bundle
   *   Paragraph entity bundle.
   * @param string $form_mode
   *   Form mode machine name.
   */
  public function addToFormDisplay(string $bundle, string $form_mode = 'default'): void;

  /**
   * Create field storage configuration entity.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   Field storage configuration entity.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function createFieldStorage(): EntityInterface;

  /**
   * Get field config storage.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   Field storage configuration entity.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function getFieldStorage(): EntityInterface;

}
