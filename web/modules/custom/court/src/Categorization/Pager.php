<?php

namespace Drupal\court\Categorization;

/**
 * Class Pager.
 *
 * @package Drupal\reyestr_court\Categorization
 */
class Pager implements CategorizationInterface {

  /**
   * {@inheritdoc}
   */
  public static function getList() {
    return [
      10 => 10,
      25 => 25,
      50 => 50,
      100 => 100,
    ];
  }
}