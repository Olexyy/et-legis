<?php

namespace Drupal\court\Categorization;

/**
 * Class InstType.
 *
 * @package Drupal\reyestr_court\Categorization
 */
class InstType implements CategorizationInterface {

  /**
   * {@inheritdoc}
   */
  public static function getList() {
    return [
      1 => 'Перша',
      2 => 'Апеляційна',
      3 => 'Касаційна',
    ];
  }
}