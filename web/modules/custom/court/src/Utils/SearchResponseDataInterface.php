<?php

namespace Drupal\court\Utils;

/**
 * Interface ResponseDataInterface.
 *
 * @package Drupal\court\Utils
 */
interface SearchResponseDataInterface {

  public static function create();

  public function setSummary($summary);

  public function getSummary();

}
