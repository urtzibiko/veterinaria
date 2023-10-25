<?php

namespace Drupal\paragraph_view_mode;

use Drupal\Core\Entity\Display\EntityDisplayInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Logger\LoggerChannelTrait;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\field\FieldConfigInterface;
use Drupal\field\FieldStorageConfigInterface;
use Drupal\paragraph_view_mode\Plugin\Field\FieldWidget\ParagraphViewModeWidget;

/**
 * Provides fields and forms storage operations required by the module.
 *
 * @package Drupal\paragraph_view_mode
 */
class StorageManager implements StorageManagerInterface {
  use MessengerTrait;
  use LoggerChannelTrait;
  use StringTranslationTrait;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Entity form display storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $formDisplay;

  /**
   * Logger.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * StorageManager constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   Entity type manager service.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->entityTypeManager = $entity_type_manager;
    $this->formDisplay = $this->entityTypeManager->getStorage('entity_form_display');
    $this->logger = $this->getLogger(StorageManagerInterface::CONFIG_NAME);
  }

  /**
   * {@inheritdoc}
   */
  public function addField(string $bundle): bool {
    $field = $this->getField($bundle);
    if (!$field) {
      try {
        $this->createField($bundle);
        $this->messenger()
          ->addMessage($this->t('%label field has been enabled on %type bundle %bundle', [
            '%label' => StorageManagerInterface::FIELD_LABEL,
            '%type' => StorageManagerInterface::ENTITY_TYPE,
            '%bundle' => $bundle,
          ]));
        return TRUE;
      }
      catch (EntityStorageException $exception) {
        $this->messenger()
          ->addMessage($this->t('Unable to craete %label for %type bundle %bundle', [
            '%label' => StorageManagerInterface::FIELD_LABEL,
            '%type' => StorageManagerInterface::ENTITY_TYPE,
            '%bundle' => $bundle,
          ]), MessengerInterface::TYPE_ERROR);
        return FALSE;
      }
    }
    else {
      return TRUE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function deleteField(string $bundle): bool {
    $field = $this->getField($bundle);
    if ($field) {
      try {
        $field->delete();

        $this->messenger()
          ->addMessage($this->t('%label field has been deleted on %type bundle %bundle', [
            '%label' => StorageManagerInterface::FIELD_LABEL,
            '%type' => StorageManagerInterface::ENTITY_TYPE,
            '%bundle' => $bundle,
          ]));
        return TRUE;
      }
      catch (EntityStorageException $exception) {
        $this->messenger()
          ->addMessage($this->t('Unable to delete %label for %type bundle %bundle', [
            '%label' => StorageManagerInterface::FIELD_LABEL,
            '%type' => StorageManagerInterface::ENTITY_TYPE,
            '%bundle' => $bundle,
          ]), MessengerInterface::TYPE_ERROR);
        return FALSE;
      }
    }
    else {
      return TRUE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function addToFormDisplay(string $bundle, string $form_mode = 'default'): void {
    $form_display_id = implode('.', [
      StorageManagerInterface::ENTITY_TYPE,
      $bundle,
      $form_mode,
    ]);

    try {
      $form_display = $this->formDisplay->load($form_display_id);
      if ($form_display instanceof EntityDisplayInterface) {
        $form_display
          ->setComponent(StorageManagerInterface::FIELD_NAME, [
            'type' => StorageManagerInterface::FIELD_TYPE,
            'weight' => -100,
            'settings' => ParagraphViewModeWidget::defaultSettings(),
          ])
          ->save();

        $this->logger->info('%label field has been placed on the %bundle form display %mode', [
          '%label' => StorageManagerInterface::FIELD_LABEL,
          '%bundle' => $bundle,
          '%mode' => $form_mode,
        ]);
      }
      else {
        throw new EntityStorageException();
      }
    }
    catch (EntityStorageException $exception) {
      $this->messenger()->addMessage($this->t('Unable to place %label field on the %bundle form display %mode, please place it manually.', [
        '%label' => StorageManagerInterface::FIELD_LABEL,
        '%bundle' => $bundle,
        '%mode' => $form_mode,
      ]), MessengerInterface::TYPE_WARNING);

      $this->logger->error('Unable to load form display %mode for %type of bundle %bundle', [
        '%mode' => $form_mode,
        '%type' => StorageManagerInterface::FIELD_TYPE,
        '%bundle' => $bundle,
      ]);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getField(string $bundle): ?FieldConfigInterface {
    return FieldConfig::loadByName(
      $type = StorageManagerInterface::ENTITY_TYPE,
      $bundle,
      $field_name = StorageManagerInterface::FIELD_NAME
    );
  }

  /**
   * Create new field config.
   *
   * @param string $bundle
   *   Paragraph entity bundle.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function createField(string $bundle): void {
    $field = FieldConfig::create([
      'field_storage' => $this->getFieldStorage(),
      'bundle' => $bundle,
      'label' => $this->t('@label', ['@label' => StorageManagerInterface::FIELD_LABEL]),
    ]);

    $field->save();
  }

  /**
   * {@inheritdoc}
   */
  public function getFieldStorage(): EntityInterface {
    $field_storage = $this->loadFieldStorage();

    if (!$field_storage) {
      $field_storage = $this->createFieldStorage();
    }

    return $field_storage;
  }

  /**
   * {@inheritdoc}
   */
  public function createFieldStorage(): EntityInterface {
    $field_storage = FieldStorageConfig::create([
      'entity_type' => StorageManagerInterface::ENTITY_TYPE,
      'field_name' => StorageManagerInterface::FIELD_NAME,
      'type' => StorageManagerInterface::FIELD_TYPE,
    ]);

    $field_storage->save();

    return $field_storage;
  }

  /**
   * Load existing field storage.
   *
   * @return \Drupal\field\FieldStorageConfigInterface|null
   *   Field storage configuration entity.
   */
  protected function loadFieldStorage(): ?FieldStorageConfigInterface {
    return FieldStorageConfig::loadByName(
      $type = StorageManagerInterface::ENTITY_TYPE,
      $field_name = StorageManagerInterface::FIELD_NAME
    );
  }

}
