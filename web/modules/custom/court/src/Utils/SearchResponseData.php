<?php

namespace Drupal\court\Utils;

/**
 * Class SearchResponseData.
 *
 * @package Drupal\court\Utils
 */
class SearchResponseData implements SearchResponseDataInterface {

  protected $empty;

  protected $summary;

  public function __construct($empty) {
    $this->empty = $empty;
  }

  public static function create() {

    return new static(FALSE);
  }

  public static function createEmpty() {

    return new static(TRUE);
  }

  /**
   * @return mixed
   */
  public function getSummary() {
    return $this->summary;
  }

  /**
   * @param mixed $summary
   *
   * @return SearchResponseData
   */
  public function setSummary($summary) {
    $this->summary = $summary;
    return $this;
  }
}