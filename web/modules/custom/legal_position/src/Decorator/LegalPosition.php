<?php

namespace Drupal\legal_position\Decorator;

use Drupal\Core\Entity\EntityInterface;
use Drupal\node\NodeInterface;
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

  /**
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *
   * @return bool
   */
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
    $this->entity->setTitle(' ');
    // Handle category map.
    if ($category = $this->getCategory()) {
      $this->setCategoryMap([]);
      foreach (static::getTermStorage()->loadAllParents($category->id()) as $parent) {
        $this->addCategoryMap($parent);
      }
    }

  }

  /**
   *
   */
  public function postSave() {

    // Handle attach latest to previous.
    if ($previous = $this->getPrevious()) {
      if ($legalPosition = static::create($previous)) {
        if ($legalPosition->getLatestId() != $this->entity->id()) {
          $legalPosition->setLatestId($this->entity->id())->save();
        }
      }
    }
    // Handle detach latest from previous.
    else {
      if ($original = $this->getOriginal()) {
        if ($legalPosition = static::create($original)) {
          if ($previous = $legalPosition->getPrevious()) {
            $legalPosition->setPreviousId(NULL)->save();
          }
        }
      }
    }

  }

  /**
   * @return \Drupal\Core\Entity\ContentEntityInterface|\Drupal\node\NodeInterface|null
   */
  public function getOriginal() {

    $entity = $this->entity;
    if (isset($entity->original)) {
      return $entity->original;
    }

    return NULL;
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

  /**
   * Related logic is not active due to missing field.
   * Replaced with field_previous_version.
   * And field_latest_version.
   *
   * @return array
   */
  public function getPreviousId() {

    return $this->entity->get('field_previous_version')->target_id;
  }

  public function getPrevious() {

    return $this->entity->get('field_previous_version')->entity;
  }

  public function setPreviousId($id) {

    $this->entity->set('field_previous_version', $id);

    return $this;
  }

  public function setPrevious(EntityInterface $entity) {

    return $this->entity->set('field_previous_version', $entity->id());
  }

  public function getLatestId() {

    return $this->entity->get('field_latest_version')->target_id;
  }

  public function getLatest() {

    return $this->entity->get('field_latest_version')->entity;
  }

  public function setLatestId($id) {

    $this->entity->set('field_latest_version', $id);

    return $this;
  }

  public function setLatest(EntityInterface $entity) {

    return $this->entity->set('field_latest_version', $entity->id());
  }

  public function save() {

    $this->entity->save();

    return $this;
  }

  /**
   * Related logic is not active due to missing field.
   * Replaced with field_previous_version.
   * And field_latest_version.
   *
   * @return array
   */
  public function getRelated() {

    $entity = $this->entity;
    if (isset($entity->field_related)) {

      return $entity->field_related->referencedEntities();
    }

    return [];
  }

  public function getRelatedIds() {

    $entity = $this->entity;
    if (isset($entity->field_related)) {
      if ($value = $entity->field_related->getValue()) {

        return array_column($value, 'target_id');
      }
    }

    return [];
  }

  public function hasRelatedId($id) {

    return in_array($id, $this->getRelatedIds());
  }

  public function addRelatedId($id) {

    $entity = $this->entity;
    if (isset($entity->field_related)) {
      $entity->field_related[] = $id;
    }

    return $this;
  }

  public function removeRelatedId($id) {

    $entity = $this->entity;
    if (isset($entity->field_related)) {
      $key = array_search($id, $this->getRelatedIds());
      if ($key !== FALSE) {
        $entity->field_related->removeItem($key);
      }
    }

    return $this;
  }

  public function setRelatedIds(array $ids) {

    $entity = $this->entity;
    if (isset($entity->field_related)) {
      $entity->field_related = $ids;
    }

    return $this;
  }

}
