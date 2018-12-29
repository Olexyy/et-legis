<?php

namespace Drupal\court\Parser;

use Drupal\court\Entity\DecisionOptions;

/**
 * Class ReviewItem.
 *
 * @package Drupal\court\Parser
 */
class ReviewResult {

  /**
   * Review decision text.
   *
   * @var string
   */
  protected $text;

  /**
   * Review decision category.
   *
   * @var string
   */
  protected $category;

  /**
   * Review decision registered.
   *
   * @var int
   */
  protected $registered;

  /**
   * Review decision published.
   *
   * @var int
   */
  protected $published;

  /**
   * Options.
   *
   * @var \Drupal\court\Entity\DecisionOptions
   */
  protected $options;

  /**
   * ReviewItem constructor.
   */
  public function __construct() {

    $this->options = DecisionOptions::instance();
  }

  /**
   * Inline constructor.
   *
   * @return $this
   *   Instance.
   */
  public static function create() {

    return new static();
  }

  /**
   * @return string
   */
  public function getText() {
    return $this->text;
  }

  /**
   * @param string $text
   *
   * @return $this
   */
  public function setText($text) {

    $this->text = $text;

    return $this;
  }

  /**
   * @return string
   */
  public function getCategory() {
    return $this->category;
  }

  public function getCategoryId() {

    return $this->options->searchCategoryId($this->category);
  }

  /**
   * @param string $category
   *
   * @return $this
   */
  public function setCategory($category) {
    $this->category = $category;
    return $this;
  }

  /**
   * @return int
   */
  public function getRegistered() {
    return $this->registered;
  }

  /**
   * @param int $registered
   *
   * @return $this
   */
  public function setRegistered(int $registered) {
    $this->registered = $registered;
    return $this;
  }

  /**
   * @return int
   */
  public function getPublished() {
    return $this->published;
  }

  /**
   * @param int $published
   *
   * @return $this
   */
  public function setPublished($published) {
    $this->published = $published;
    return $this;
  }

}
