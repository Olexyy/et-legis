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
   * @var \Drupal\Core\Entity\ContentEntityInterface|\Drupal\Core\Entity\EntityInterface|\Drupal\node\NodeInterface
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
   *
   * Fill category map.
   */
  public function preSave() {

    // Set default title.
    $this->entity->setTitle('');
    if ($category = $this->getCategory()) {
      $this->setCategoryMap([]);
      foreach (static::getTermStorage()->loadAllParents($category->id()) as $parent) {
        $this->addCategoryMap($parent);
      }
    }
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

  public function getCategoryMap() {

    $entity = $this->entity;
    if (isset($entity->field_category_map)) {

      return $entity->field_category_map->referencedEntities();
    }

    return [];
  }

  public function getCategoryMapIds() {

    $entity = $this->entity;
    if (isset($entity->field_category_map)) {
      $values = $entity->field_category_map->getValue();

      return array_column($values, 'target_id');
    }

    return [];
  }

  public function setCategoryMap(array $categories) {

    $entity = $this->entity;
    if (isset($entity->field_category_map)) {
      $entity->field_category_map = $categories;
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
  public function hasCategoryMap(TermInterface $category) {

    return in_array($category->id(), $this->getCategoryMapIds());
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
  public function addCategoryMap(TermInterface $category) {

    $entity = $this->entity;
    if (isset($entity->field_category_map)) {
      $entity->field_category_map[] = $category;
    }

    return $this;
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
  public function setCategory(TermInterface $category) {

    $entity = $this->entity;
    if (isset($entity->field_category)) {
      $entity->field_category = $category;
    }

    return $this;
  }

  /**
   * Getter.
   *
   * @return \Drupal\taxonomy\TermInterface|null
   *   Value if any.
   */
  public function getCategory() {

    $entity = $this->entity;
    if (isset($entity->field_category)) {
      return $entity->field_category->entity;
    }

    return NULL;
  }

}
