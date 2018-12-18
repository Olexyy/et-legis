<?php

namespace Drupal\court\Categorization;

/**
 * Class SideStatus
 *
 * @package Drupal\reyestr_court\Categorization
 */
class SideStatus implements CategorizationInterface {

  /**
   * {@inheritdoc}
   */
  public static function getList() {
    return [
      1 => 'Фізична особа',
      2 => 'Державний орган, підприємство, установа, організація',
      3 => 'Юридична особа',
    ];
  }
}