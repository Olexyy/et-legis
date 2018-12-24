<?php

namespace Drupal\court\Utils;

/**
 * Class ReviewResponseData.
 *
 * @package Drupal\court\Utils
 */
class ReviewResponseData implements ReviewResponseDataInterface {

  protected $text;

  protected $number;

  protected $empty;

  public function __construct($empty) {
    $this->empty = $empty;
  }

  public static function create() {

    return new static(FALSE);
  }

  public static function createEmpty() {

    return new static(TRUE);
  }

  public function isEmpty() {

    return $this->empty;
  }

  /**
   * @return mixed
   */
  public function getText() {

    return $this->text;
  }

  /**
   * @param mixed $text
   *
   * @return $this
   */
  public function setText($text) {

    $this->text = $text;

    return $this;
  }

  /**
   * @return mixed
   */
  public function getNumber() {

    return $this->number;
  }

  /**
   * @param mixed $number
   *
   * @return $this
   */
  public function setNumber($number) {

    $this->number = $number;

    return $this;
  }
}