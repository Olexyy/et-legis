<?php

namespace Drupal\court\Utils;

/**
 * Class SearchResponseData.
 *
 * @package Drupal\court\Utils
 */
class SearchResponseData implements ResponseDataInterface {

  protected $summary;

  public static function create() {
    return new static();
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