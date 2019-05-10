<?php

namespace Drupal\legal_position\Decorator;

use Drupal\Core\Entity\EntityInterface;
use Drupal\taxonomy\TermInterface;

/**
 * Class LegalPosition.
 *
 * @package Drupal\legal_position\Decorator
 */
class LegalPosition {

  const BUNDLE = 'legal_position';

  /**
   * Wrapped entity.
   *
   * @var \Drupal\Core\Entity\ContentEntityInterface|\Drupal\Core\Entity\EntityInterface
   */
  protected $entity;

  /**
   * LegalPosition constructor.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Content entity.
   */
  public function __construct(EntityInterface $entity) {

    $this->entity = $entity;
  }

  /**
   * Static constructor.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Wrapped entity.
   *
   * @return $this
   *   Instance.
   */
  public static function create(EntityInterface $entity) {

    return new static($entity);
  }

  public static function applies(EntityInterface $entity) {

    return $entity->bundle() == static::BUNDLE;
  }

  /**
   * Pre save handler.
   */
  public function preSave() {

    foreach ($this->getCategories() as $category) {
      foreach (static::getTermStorage()->loadAllParents($category->id()) as $parent) {
        if (!$this->hasCategory($parent)) {
          $this->addCategory($parent);
        }
      }
    }
  }

  public function getCategories() {

    $entity = $this->entity;
    if (isset($entity->field_category)) {

      return $entity->field_category->referencedEntities();
    }

    return [];
  }

  /**
   * Term storage.
   *
   * @return \Drupal\taxonomy\TermStorageInterface
   *   Term storage.
   */
  public static function getTermStorage() {

    return \Drupal::service('entity_type.manager')
      ->getStorage('taxonomy_term');
  }

  public function getCategoryIds() {

    $entity = $this->entity;
    if (isset($entity->field_category)) {
      $values = $entity->field_category->getValue();

      return array_column($values, 'target_id');
    }

    return [];
  }

  public function setCategory(array $categories) {

    $entity = $this->entity;
    if (isset($entity->field_category)) {
      $entity->field_category = $categories;
    }

    return $this;
  }

  /**
   * Predicate to define if entity has tag.
   *
   * @param \Drupal\taxonomy\TermInterface $category
   *   Term.
   *
   * @return bool
   *   Result.
   */
  public function hasCategory(TermInterface $category) {

    return in_array($category->id(), $this->getCategoryIds());
  }

  /**
   * Adds given tag.
   *
   * @param \Drupal\taxonomy\TermInterface $category
   *   Term to add.
   *
   * @return $this
   *   Chaining.
   */
  public function addCategory(TermInterface $category) {

    $entity = $this->entity;
    if (isset($entity->field_category)) {
      $entity->field_category[] = $category;
    }

    return $this;
  }

}
