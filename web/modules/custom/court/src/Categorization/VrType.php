<?php

namespace Drupal\court\Categorization;

/**
 * Class Decision type.
 *
 * @package Drupal\reyestr_court\Categorization
 */
class VrType implements CategorizationInterface {

  /**
   * {@inheritdoc}
   */
  public static function getList() {
    return [
      1 => 'Вирок',
      2 => 'Постанова',
      3 => 'Рішення',
      4 => 'Судовий наказ',
      5 => 'Ухвала',
      6 => 'Окрема ухвала',
      10 => 'Окрема думка',
    ];
  }
}