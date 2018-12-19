<?php

namespace Drupal\court\Service;

use Drupal\court\Utils\RequestDataInterface;

/**
 * Interface CourtApiServiceInterface.
 *
 * @package Drupal\court\Service
 */
interface CourtApiServiceInterface {

  /**
   * Executes api request.
   *
   * @param \Drupal\court\Utils\RequestDataInterface $requestData
   *   Request data.
   *
   * @return \Drupal\court\Utils\ResponseDataInterface
   *   Response data.
   */
  public function request(RequestDataInterface $requestData);

  /**
   * Number of cases in court register.
   *
   * @return int
   *   Cases count.
   */
  public function getCasesCount();

}
