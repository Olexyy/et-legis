<?php

namespace Drupal\court\Utils;

/**
 * Class ReviewRequestDataInterface.
 *
 * @package Drupal\court\Utils
 */
interface ReviewRequestDataInterface {

  /**
   * Getter for request api params.
   *
   * @return array
   *   Params array.
   */
  public function toApiArray();

  /**
   * Returns method of request.
   *
   * @return string
   */
  function getMethod();

  /**
   * Returns url for request.
   *
   * @return string
   */
  function getUrl();

  /**
   * @return static
   */
  public static function create();

  /**
   * @return mixed
   */
  public function getRegNumber();

}