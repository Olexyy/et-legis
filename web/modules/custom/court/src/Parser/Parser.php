<?php

namespace Drupal\court\Parser;

use Drupal\court\Entity\DecisionInterface;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class Parser.
 *
 * @package Drupal\court\Parser
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
   * Review item.
   *
   * @var \Drupal\court\Parser\ReviewResult
   */
  protected $reviewResult;

  /**
   * Search result.
   *
   * @var \Drupal\court\Parser\SearchResult
   */
  protected $searchResult;

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
   * Syncs decision with parsed data.
   *
   * @param \Drupal\court\Entity\DecisionInterface $decision
   *   Decision to fill up.
   */
  public function sync(DecisionInterface $decision) {

    if ($this->typeIs(static::SEARCH)) {
      // Search type. Fill out only if not empty.
      if ($result = $this->getSearchResult()->getFirstResult()) {
        if ($result->getNumber() && !$decision->getNumber()) {
          $decision->setNumber($result->getNumber());
        }
        if ($result->getTypeId() && !$decision->getType()) {
          $decision->setType($result->getTypeId());
        }
        if ($result->getCaseNumber() && !$decision->getCaseNumber()) {
          $decision->setCaseNumber($result->getCaseNumber());
        }
        if ($result->getJurisdictionId() && !$decision->getJurisdiction()) {
          $decision->setJurisdiction($result->getJurisdictionId());
        }
        if ($result->getJudge() && !$decision->getJudge()) {
          $decision->setJudge($result->getJudge());
        }
        if ($result->getCourtId() && !$decision->getCourt()) {
          $decision->setCourt($result->getCourtId());
        }
      }
    }
    // Review type. Fill out only if not empty.
    elseif ($this->typeIs(static::REVIEW)) {
      if ($this->getReviewResult()->getText() && !$decision->getText()) {
        $decision->setText($this->getReviewResult()->getText());
      }
      if ($this->getReviewResult()->getCategoryId() && !$decision->getCategory()) {
        $decision->setCategory($this->getReviewResult()->getCategoryId());
      }
      if ($this->getReviewResult()->getRegistered() && !$decision->getRegistered()) {
        $decision->setRegistered($this->getReviewResult()->getRegistered());
      }
      if ($this->getReviewResult()->getPublished() && !$decision->getPublished()) {
        $decision->setPublished($this->getReviewResult()->getPublished());
      }
    }
  }

  /**
   * Getter for review result.
   *
   * @return \Drupal\court\Parser\ReviewResult
   *   Review result.
   */
  public function getReviewResult() {

    if (!$this->reviewResult) {
      $this->reviewResult = ReviewResult::create();
      if ($text = $this->getText()) {
        $this->reviewResult->setText($text);
      }
      if ($category = $this->getCategory()) {
        $this->reviewResult->setCategory($category);
      }
      if ($registered = $this->getRegistered()) {
        $this->reviewResult->setRegistered($registered);
      }
      if ($published = $this->getPublished()) {
        $this->reviewResult->setPublished($published);
      }
    }

    return $this->reviewResult;
  }

  public function getSearchResult() {

    if (!$this->searchResult) {
      $this->searchResult = SearchResult::create();
      $this->searchResult->setResults($this->getResults());
      $this->searchResult->setCount($this->getCount());
      $this->searchResult->setSummary($this->getSummary());
    }

    return $this->searchResult;
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
   * Getter for search results summary.
   *
   * @return string
   *   Summary string.
   */
  protected function getSummary() {

    if ($this->crawler->filter('#divFooterSearch .td1')->count()) {

      return $this->crawler->filter('#divFooterSearch .td1')->first()->html();
    }

    return '';
  }

  protected function getText() {

    if ($this->crawler->filter('#txtdepository')->count()) {

      return $this->crawler->filter('#txtdepository')->first()->html();
    }

    return NULL;
  }

  protected function getResults() {

    $results = [];
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
      $results[] = $searchItem;
    }

    return $results;
  }

  /**
   * Extracts count from summary.
   *
   * @return int
   *   Count.
   */
  protected function getCount() {

    $matches = [];
    if (preg_match('@За заданими параметрами пошуку знайдено документів:\s*(\d+)@', $this->getSummary(), $matches)) {
      if (isset($matches[1]) && is_numeric($matches[1])) {

        return (int) $matches[1];
      }
    }

    return 0;
  }

  protected function getCategory() {

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

  protected function getRegistered() {

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

  protected function getPublished() {

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
        // Little hack, we do not have categories lower than 1-st level for now.
        $category = current($categories);

        return $this->processString($category);
      }
    }

    return '';
  }

}
