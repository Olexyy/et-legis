<?php

namespace Drupal\court\Utils;

use Drupal\court\Entity\DecisionInterface;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class Parser.
 *
 * @package Drupal\court\Utils
 */
class Parser implements ParserInterface {

  /**
   * Parser type.
   *
   * @var string
   */
  protected $type;

  /**
   * Crawler.
   *
   * @var \Symfony\Component\DomCrawler\Crawler
   */
  protected $crawler;

  /**
   * Html.
   *
   * @var string
   */
  protected $html;

  /**
   * Search results instances.
   *
   * @var array|\Drupal\court\Utils\SearchItem[]
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
   * Review item.
   *
   * @var \Drupal\court\Utils\ReviewItem
   */
  protected $review;

  /**
   * Parser constructor.
   *
   * @param string $type
   *   Type.
   * @param string $html
   *   Html.
   */
  public function __construct($type, $html) {

    $this->crawler = new Crawler();
    $this->html = $this->processHtml($html);
    $this->crawler->addHtmlContent($this->html);
    $this->type = $type;
  }

  /**
   * Getter.
   *
   * @return string
   *   Value.
   */
  public function getType() {

    return $this->type;
  }

  /**
   * Predicate.
   *
   * @param string $type
   *   Decision type.
   *
   * @return bool
   *   Result.
   */
  public function typeIs($type) {

    return $this->type == $type;
  }

  /**
   * Processes Html before it is crawled.
   *
   * @param string $html
   *   Html.
   *
   * @return string
   *   Processed Html.
   */
  protected function processHtml($html) {

    $html = str_replace('&nbsp;', ' ', $html);

    return $html;
  }

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
  public static function create($type, $html) {

    return new static($type, $html);
  }

  /**
   * Factory method.
   *
   * @param string $html
   *   Html.
   *
   * @return $this
   *   This object.
   */
  public static function createReview($html) {

    return static::create(static::REVIEW, $html);
  }

  /**
   * Factory method.
   *
   * @param string $html
   *   Html.
   *
   * @return $this
   *   This object.
   */
  public static function createSearch($html) {

    return static::create(static::SEARCH, $html);
  }

  /**
   * Getter for Html.
   *
   * @return string
   *   Html.
   */
  public function getHtml() {

    return $this->html;
  }

  /**
   * Crawler getter.
   *
   * @return \Symfony\Component\DomCrawler\Crawler
   *   Crawler.
   */
  public function getCrawler() {

    return $this->crawler;
  }

  /**
   * Getter for search results summary.
   *
   * @return string
   *   Summary string.
   */
  public function getSummary() {

    if ($this->crawler->filter('#divFooterSearch .td1')->count()) {

      return $this->crawler->filter('#divFooterSearch .td1')->first()->html();
    }

    return '';
  }

  /**
   * Getter for review item.
   *
   * @return \Drupal\court\Utils\ReviewItem
   *   Review item.
   */
  public function getReview() {

    if (!$this->review) {
      $this->review = ReviewItem::create();
      if ($text = $this->getText()) {
        $this->review->setText($text);
      }
      if ($category = $this->getCategory()) {
        $this->review->setCategory($category);
      }
      if ($registered = $this->getRegistered()) {
        $this->review->setRegistered($registered);
      }
      if ($published = $this->getPublished()) {
        $this->review->setPublished($published);
      }
    }

    return $this->review;
  }

  public function getText() {

    if ($this->crawler->filter('#txtdepository')->count()) {

      return $this->crawler->filter('#txtdepository')->first()->html();
    }

    return NULL;
  }

  /**
   * Syncs decision with parsed data.
   *
   * @param \Drupal\court\Entity\DecisionInterface $decision
   *   Decision to fill up.
   */
  public function sync(DecisionInterface $decision) {

    if ($this->typeIs(static::SEARCH)) {
      // Search type.
      if ($this->hasResults()) {
        $result = $this->getResults()[0];
        $decision->setNumber($result->getNumber());
        $decision->setType($result->getTypeId());
        $decision->setCaseNumber($result->getCaseNumber());
        $decision->setJurisdiction($result->getJurisdictionId());
        $decision->setJudge($result->getJudge());
        $decision->setCourt($result->getCourtId());
      }
    }
    // Review type.
    elseif ($this->typeIs(static::REVIEW)) {
      $decision->setText($this->getReview()->getText());
      $decision->setCategory($this->getReview()->getCategoryId());
      $decision->setRegistered($this->getReview()->getRegistered());
      $decision->setPublished($this->getReview()->getPublished());
    }
  }

  public function getResults() {

    if (!is_array($this->results)) {
      $this->results = [];
      if ($this->crawler->filter('#tableresult tr')->count()) {
        $row = $this->crawler->filter('#tableresult tr')->eq(1)->filter('td');
        $number = trim($row->eq(0)->filter('a')->text());
        $type = trim($row->eq(1)->text());
        $resolved = strtotime(trim($row->eq(2)->text()));
        $validated = strtotime(trim($row->eq(3)->text()));
        $jurisdiction = trim($row->eq(4)->text());
        $caseNumber = trim($row->eq(5)->text());
        $court = trim($row->eq(6)->text());
        $judge = trim($row->eq(7)->text());
        $searchItem = new SearchItem();
        $searchItem
          ->setNumber($number)
          ->setType($type)
          ->setResolved($resolved)
          ->setValidated($validated)
          ->setJurisdiction($jurisdiction)
          ->setCaseNumber($caseNumber)
          ->setCourt($court)
          ->setJudge($judge);
        $this->results[] = $searchItem;
      }
    }

    return $this->results;
  }

  public function hasResults() {

    return (bool) $this->getResults();
  }

  /**
   * Extracts count from summary.
   *
   * @return int
   *   Count.
   */
  public function getCount() {

    $matches = [];
    if (preg_match('@За заданими параметрами пошуку знайдено документів:\s*(\d+)@', $this->getSummary(), $matches)) {
      if (isset($matches[1]) && is_numeric($matches[1])) {

        return (int) $matches[1];
      }
    }

    return 0;
  }

  public function getCategory() {

    if ($this->crawler->filter('#divcasecat')->count()) {
      if ($this->crawler->filter('#divcasecat tr')->count()) {
        $rows = $this->crawler->filter('#divcasecat tr');
        // '2/0417/124/12: Цивільні справи; Позовне провадження; Спори, що виникають із сімейних правовідносин.'
        $row = $rows->eq(0)->filter('td b')->text();

        return $this->processCategory($row);
      }
    }

    return '';
  }

  public function getRegistered() {

    if ($this->crawler->filter('#divcasecat')->count()) {
      if ($this->crawler->filter('#divcasecat tr')->count()) {
        $rows = $this->crawler->filter('#divcasecat tr');
        if ($rows->eq(1)->filter('td b')->count()) {
          // '09.06.2015.'
          $registered = $rows->eq(1)->filter('td b')->eq(1)->text();

          return $this->processTimestamp($registered);
        }
      }
    }

    return NULL;
  }

  public function getPublished() {

    if ($this->crawler->filter('#divcasecat')->count()) {
      if ($this->crawler->filter('#divcasecat tr')->count()) {
        $rows = $this->crawler->filter('#divcasecat tr');
        if ($rows->eq(1)->filter('td b')->count()) {
          // '09.06.2015.'
          $published = $rows->eq(1)->filter('td b')->eq(2)->text();

          return $this->processTimestamp($published);
        }
      }
    }

    return NULL;
  }

  /**
   * Processes string.
   *
   * @param string $string
   *   String.
   *
   * @return string
   *   Processed string.
   */
  protected function processString($string) {

    return trim($string, ". \t\n\r\0\x0B");
  }

  /**
   * Parses timestamp.
   *
   * @param string $row
   *   Unparsed row.
   *
   * @return int|null
   *   Time if any.
   */
  protected function processTimestamp($row) {

    $row = $this->processString($row);
    if ($time = strtotime($row)) {
      return $time;
    }

    return NULL;
  }

  /**
   * Category negotiator.
   *
   * @param string $row
   *   Unparsed row.
   *
   * @return string
   *   Category if any.
   */
  protected function processCategory($row) {

    $categories = explode(':', $row);
    if (count($categories) == 2) {
      $categories = $categories[1];
      $categories = $this->processString($categories);
      if (stripos($categories, 'не визначено') === FALSE) {
        $categories = array_filter(explode(';', $categories));
        $count = count($categories);
        // Little hack, we do not have categories lower than 3-rd level for now.
        if ($count > 3) {
          $category = $count[2];
        }
        else {
          $category = end($categories);
        }

        return $this->processString($category);
      }
    }

    return '';
  }

}
