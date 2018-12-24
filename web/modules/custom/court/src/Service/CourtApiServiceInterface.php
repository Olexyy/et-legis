<?php

namespace Drupal\court\Service;

use Drupal\court\Entity\Decision;
use Drupal\court\Utils\ReviewRequestDataInterface;
use Drupal\court\Utils\ReviewResponseDataInterface;
use Drupal\court\Utils\SearchRequestDataInterface;

/**
 * Interface CourtApiServiceInterface.
 *
 * @package Drupal\court\Service
 */
interface CourtApiServiceInterface {

  const ID = 'court.api';
  const BASE_URL = 'http://www.reyestr.court.gov.ua/';
  const REVIEW_PREFIX = 'Review';

  /**
   * Executes api request.
   *
   * @param \Drupal\court\Utils\SearchRequestDataInterface $requestData
   *   Request data.
   *
   * @return \Drupal\court\Utils\SearchResponseDataInterface
   *   Response data.
   */
  public function search(SearchRequestDataInterface $requestData);

  /**
   * Executes api request.
   *
   * @param \Drupal\court\Utils\ReviewRequestDataInterface $requestData
   *   Request data.
   *
   * @return \Drupal\court\Utils\ReviewResponseDataInterface
   *   Response data.
   */
  public function review(ReviewRequestDataInterface $requestData);

  /**
   * Number of cases in court register.
   *
   * @return int
   *   Cases count.
   */
  public function getCasesCount();

  /**
   * Getter for review url by given case number.
   *
   * @param string $number
   *   Case number.
   *
   * @return string
   *   Url string.
   */
  public function getReviewUrl($number);

  public function sync(Decision $decision, ReviewResponseDataInterface $searchResponseData);

}
