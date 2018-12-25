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
   * Search results.
   *
   * @var array|\Drupal\court\Utils\SearchItem[]
   */
  protected $results;

  protected $summary;

  protected $text;

  /**
   * Parser constructor.
   *
   * @param string $html
   *   Html.
   */
  public function __construct($html) {

    $this->crawler = new Crawler();
    $this->html = $this->processHtml($html);
    $this->crawler->addHtmlContent($this->html);
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
   * @param string $html
   *   Html.
   *
   * @return $this
   *   This object.
   */
  public static function create($html) {

    return new static($html);
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

  public function getText() {

    if (!is_string($this->text)) {
      $this->text = '';
      if ($this->crawler->filter('#txtdepository')->count()) {
        $this->text = $this->crawler->filter('#txtdepository')->first()->html();
      }
    }

    return $this->text;
  }

  public function sync(DecisionInterface $decision) {

    if ($this->hasResults()) {
      $result = $this->getResults()[0];
      $decision->setNumber($result->getNumber());
      $decision->setType($result->getType());
      $decision->setCaseNumber($result->getCaseNumber());
      $decision->setJurisdiction($result->getJurisdiction());
      $decision->setJudge($result->getJudge());
      $decision->setCourt($result->getCourt());
    }
    else {
      if ($text = $this->getText()) {
        $decision->setText($text);
      }
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

}
