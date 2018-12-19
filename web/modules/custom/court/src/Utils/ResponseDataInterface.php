<?php

namespace Drupal\court\Utils;

/**
 * Interface ResponseDataInterface.
 *
 * @package Drupal\court\Utils
 */
interface ResponseDataInterface {

  static function create();

  function setSummary($summary);

  function getSummary();

}
