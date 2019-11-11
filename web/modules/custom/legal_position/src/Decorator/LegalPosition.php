<?php

namespace Drupal\legal_position\Decorator;

use Drupal\Core\Entity\EntityInterface;
use Drupal\taxonomy\TermInterface;
use Drupal\user\UserInterface;

/**
 * Class LegalPosition.
 *
 * @package Drupal\legal_position\Decorator
 */
class LegalPosition {

  /**
   * Definitions.
   *
   * @var string
   */
  const BUNDLE = 'legal_position';
  const TYPE = 'node';

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
  public static function create(EntityInterface $entity = NULL) {

    if (!$entity) {
      $entity = static::getNodeStorage()->create([
        'type' => static::BUNDLE,
      ]);
    }
    return new static($entity);
  }

  /**
   * Pre create handler.
   */
  public function preCreate() {

    $this->entity->setTitle(' ');
  }

  /**
   * Predicate to define if given wrapper applies to entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Given entity.
   *
   * @return bool
   *   Test result.
   */
  public static function applies(EntityInterface $entity) {

    return $entity->getEntityTypeId() == static::TYPE &&
      $entity->bundle() == static::BUNDLE;
  }

  /**
   * Parses body.
   *
   * @param string $body
   *   Body.
   *
   * @return string
   *   Title.
   */
  protected function parseToBody($body) {
    // Replace copy paste symbol.
    $body = str_replace('&nbsp;', ' ', $body);
    $bodyClean = strip_tags($body, '<p><a><em><strong><sup><sub>');

    return $bodyClean;
  }

  /**
   * Parses body to title.
   *
   * @param string $body
   *   Body.
   *
   * @return string
   *   Title.
   */
  protected function parseToTitle($body) {

    $bodyClean = strip_tags($body);
    if ($bodyClean) {
      if (mb_strlen($bodyClean) > 120) {
        $bodyClean = mb_substr($bodyClean, 0, 117) . '...';
      }
    }
    else {
      // Set default title.
      $bodyClean = ' ';
    }

    return $bodyClean;
  }

  /**
   * Pre-save handler.
   */
  public function preSave() {

    // Process and save body.
    $body = (string) $this->entity->get('body')->value;
    $bodyClean = $this->parseToBody($body);
    $this->entity->get('body')->value = $bodyClean;
    // Handle title generation.
    $this->entity->setTitle($this->parseToTitle($bodyClean));
    // Handle category map.
    if ($category = $this->getCategory()) {
      $this->setCategoryMap([]);
      foreach (static::getTermStorage()->loadAllParents($category->id()) as $parent) {
        $this->addCategoryMap($parent);
      }
    }
    // Define `is latest` logic.
    if ($this->getLatestId()) {
      $this->setIsLatest(FALSE);
    }
    else {
      $this->setIsLatest(TRUE);
    }
    // Manage reviewer. Set if content is published and none set.
    if ($this->entity->isPublished() && !$this->getReviewerId()) {
      $this->setReviewerId(\Drupal::currentUser()->id());
    }
  }

  /**
   * Post-save handler.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function postSave() {

    // Handle attach latest to previous.
    if ($previous = $this->getPrevious()) {
      if ($legalPosition = static::create($previous)) {
        if ($legalPosition->getLatestId() != $this->entity->id()) {
          $legalPosition
            ->setLatestId($this->entity->id())
            ->setIsLatest(FALSE)
            ->save();
        }
      }
    }
    // Handle detach latest from previous.
    else {
      if ($original = $this->getOriginal()) {
        if ($legalPosition = static::create($original)) {
          if ($previous = $legalPosition->getPrevious()) {
            $legalPosition->setPreviousId(NULL)
              ->setIsLatest(TRUE)
              ->save();
          }
        }
      }
    }
  }

  /**
   * Extracts original entity if any.
   *
   * @return \Drupal\Core\Entity\ContentEntityInterface|\Drupal\node\NodeInterface|null
   *   Original entity.
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

  /**
   * Node storage.
   *
   * @return \Drupal\node\NodeStorageInterface
   *   Node storage.
   */
  public static function getNodeStorage() {

    return \Drupal::service('entity_type.manager')
      ->getStorage('node');
  }

  /**
   * Category map.
   *
   * @return array|\Drupal\taxonomy\TermInterface[]
   *   Entities.
   */
  public function getCategoryMap() {

    $entity = $this->entity;
    if (isset($entity->field_category_map)) {

      return $entity->field_category_map->referencedEntities();
    }

    return [];
  }

  /**
   * Getter for category map ids.
   *
   * @return array|string[]
   *   Ids.
   */
  public function getCategoryMapIds() {

    $entity = $this->entity;
    if (isset($entity->field_category_map)) {
      $values = $entity->field_category_map->getValue();

      return array_column($values, 'target_id');
    }

    return [];
  }

  /**
   * Setter for category map ids.
   *
   * @param array $categories
   *   Categories.
   *
   * @return $this
   *   Chaining.
   */
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
   * Adds given category.
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
   * Getter for category.
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
   * Getter for previous id.
   *
   * @return string|null
   *   Id if any.
   */
  public function getPreviousId() {

    return $this->entity->get('field_previous_version')->target_id;
  }

  /**
   * Getter for previous.
   *
   * @return null|\Drupal\node\NodeInterface
   *   Entity if any.
   */
  public function getPrevious() {

    return $this->entity->get('field_previous_version')->entity;
  }

  /**
   * Setter for previous.
   *
   * @param string|null $id
   *   Id.
   *
   * @return $this
   *   Chaining.
   */
  public function setPreviousId($id) {

    $this->entity->set('field_previous_version', $id);

    return $this;
  }

  /**
   * Setter for previous.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Id.
   *
   * @return $this
   *   Chaining.
   */
  public function setPrevious(EntityInterface $entity) {

    return $this->entity->set('field_previous_version', $entity->id());
  }

  /**
   * Getter for latest id.
   *
   * @return string|null
   *   Id if any.
   */
  public function getLatestId() {

    return $this->entity->get('field_latest_version')->target_id;
  }

  /**
   * Getter for latest.
   *
   * @return null|\Drupal\node\NodeInterface
   *   Entity if any.
   */
  public function getLatest() {

    return $this->entity->get('field_latest_version')->entity;
  }

  /**
   * Setter for latest.
   *
   * @param string|null $id
   *   Id.
   *
   * @return $this
   *   Chaining.
   */
  public function setLatestId($id) {

    $this->entity->set('field_latest_version', $id);

    return $this;
  }

  /**
   * Setter for latest.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Id.
   *
   * @return $this
   *   Chaining.
   */
  public function setLatest(EntityInterface $entity) {

    return $this->entity->set('field_latest_version', $entity->id());
  }

  /**
   * Save handler.
   *
   * @return $this
   *   Chaining.
   *
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function save() {

    $this->entity->save();

    return $this;
  }

  /**
   * Setter for latest.
   *
   * @param bool $value
   *   Given value.
   *
   * @return $this
   *   Chaining.
   */
  public function setIsLatest($value) {

    $this->entity->setSticky($value);

    return $this;
  }

  /**
   * Getter for latest.
   *
   * @return bool
   *   Value.
   */
  public function getIsLatest() {

    return $this->entity->isSticky();
  }

  public function getReviewerId() {

    return $this->entity->get('field_reviewer')->target_id;
  }

  public function getReviewer() {

    return $this->entity->get('field_reviewer')->entity;
  }

  public function setReviewerId($id) {

    $this->entity->get('field_reviewer')->target_id = $id;

    return $this;
  }

  public function setReviewer(UserInterface $user) {

    return $this->setReviewerId($user->id());
  }

  /**
   * Entity accessor.
   *
   * @return \Drupal\Core\Entity\ContentEntityInterface|\Drupal\Core\Entity\EntityInterface|\Drupal\node\NodeInterface
   *   Node.
   */
  public function getEntity() {

    return $this->entity;
  }

  /**
   * Getter.
   *
   * @return string|null
   *   Value.
   */
  public function getBody() {

    return $this->entity->get('body')->value;
  }

  /**
   * Setter.
   *
   * @param string|null $body
   *   Body.
   *
   * @return $this
   *   Chaining.
   */
  public function setBody($body) {

    $this->entity->get('body')->value = $body;

    return $this;
  }

  /**
   * Setter.
   *
   * @param array $link
   *   Link with 'title' and 'uri'.
   *
   * @return $this
   *   Chaining.
   */
  public function setDecisionLink(array $link) {

    $this->entity->set('field_decision_link', $link);

    return $this;
  }

  /**
   * @param array $conditions
   *
   * @return \Drupal\Core\Entity\EntityInterface|mixed|null
   */
  public static function loadSingle(array $conditions) {

    $conditions['type'] = static::BUNDLE;
    $results = static::getNodeStorage()
      ->loadByProperties($conditions);

    return $results ? current($results) : NULL;
  }

  /**
   * @param $url
   *
   * @return \Drupal\Core\Entity\EntityInterface|mixed|null
   */
  public static function loadByDecisionLink($url) {

    return static::loadSingle([
      'field_decision_link.uri' => $url,
    ]);
  }

}
