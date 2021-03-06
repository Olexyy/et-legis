<?php

namespace Drupal\court\Service;

use Drupal\court\Data\RequestDataInterface;

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
   * @param \Drupal\court\Data\RequestDataInterface $requestData
   *   Request data.
   *
   * @return \Drupal\court\Data\ResponseDataInterface
   *   Response data.
   */
  public function search(RequestDataInterface $requestData);

  /**
   * Executes api request.
   *
   * @param \Drupal\court\Data\RequestDataInterface $requestData
   *   Request data.
   *
   * @return \Drupal\court\Data\ResponseDataInterface
   *   Response data.
   */
  public function review(RequestDataInterface $requestData);

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

}
