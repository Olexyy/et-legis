<?php

namespace Drupal\court\Categorization;

/**
 * Class Sort.
 *
 * @package Drupal\court\Categorization
 */
class Sort implements CategorizationInterface
{

  /**
   * {@inheritdoc}
   */
  public static function getList() {
    return [
      0 => 'релевантність',
      1 => 'дата ухвалення | спадання',
      2 => 'дата ухвалення | зростання',
    ];
  }
}