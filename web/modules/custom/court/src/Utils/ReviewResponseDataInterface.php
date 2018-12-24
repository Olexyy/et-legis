<?php

namespace Drupal\court\Utils;

/**
 * Interface ResponseDataInterface.
 *
 * @package Drupal\court\Utils
 */
interface ReviewResponseDataInterface {

  public static function create();

  public static function createEmpty();

  public function setText($text);

  public function getText();

  public function setNumber($number);

  public function getNumber();

  public function isEmpty();

}
