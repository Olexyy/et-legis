<?php

namespace Drupal\court\Parser;

/**
 * Class SearchResult.
 *
 * @package Drupal\court\Utils
 */
class SearchResult {

  /**
   * Search results instances.
   *
   * @var array|\Drupal\court\Parser\SearchItem[]
   */
  protected $results;

  /**
   * Search summary.
   *
   * @var string
   */
  protected $summary;

  /**
   * Search results count.
   *
   * @var int
   */
  protected $count;

  /**
   * Inline constructor.
   *
   * @return $this
   *   Instance.
   */
  public static function create() {

    return new static();
  }

  public function getFirstResult() {

    if ($this->hasResults()) {

      return $this->results[0];
    }

    return NULL;
  }

  public function getResultsCount() {

    return count($this->results);
  }

  /**
   * @return bool
   */
  public function hasResults() {

    return (bool) $this->getResultsCount();
  }

  /**
   * @return array|\Drupal\court\Parser\SearchItem[]
   */
  public function getResults() {

    return $this->results;
  }

  /**
   * @param array|\Drupal\court\Parser\SearchItem[] $results
   *
   * @return SearchResult
   */
  public function setResults($results) {

    $this->results = $results;

    return $this;
  }

  /**
   * @return string
   */
  public function getSummary() {

    return $this->summary;
  }

  /**
   * @param string $summary
   *
   * @return SearchResult
   */
  public function setSummary($summary) {

    $this->summary = $summary;

    return $this;
  }

  /**
   * @return int
   */
  public function getCount() {

    return $this->count;
  }

  /**
   * @param int $count
   *
   * @return SearchResult
   */
  public function setCount($count) {

    $this->count = $count;

    return $this;
  }

}
