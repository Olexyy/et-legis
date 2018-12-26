<?php

namespace Drupal\court\Utils;

/**
 * Interface ParserInterface.
 *
 * @package Drupal\court\Utils
 */
interface ParserInterface {

  const SEARCH = 'search';
  const REVIEW = 'review';

  public function getType();

  public function typeIs($type);

  /**
   * Factory method.
   *
   * @param string $type
   *   Type.
   * @param string $html
   *   Html.
   *
   * @return $this
   *   This object.
   */
  public static function create($type, $html);

  /**
   * Factory method.
   *
   * @param string $html
   *   Html.
   *
   * @return $this
   *   This object.
   */
  public static function createReview($html);

  /**
   * Factory method.
   *
   * @param string $html
   *   Html.
   *
   * @return $this
   *   This object.
   */
  public static function createSearch($html);

  /**
   * Getter for Html.
   *
   * @return string
   *   Html.
   */
  public function getHtml();

  /**
   * Crawler getter.
   *
   * @return \Symfony\Component\DomCrawler\Crawler
   *   Crawler.
   */
  public function getCrawler();

  /**
   * Getter for search results summary.
   *
   * @return string
   *   Summary string.
   */
  public function getSummary();

  /**
   * Extracts count from summary.
   *
   * @return int
   *   Count.
   */
  public function getCount();

  public function getResults();

  public function hasResults();

}
