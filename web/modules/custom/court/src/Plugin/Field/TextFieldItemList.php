<?php

namespace Drupal\court\Plugin\Field;

use Drupal\Core\Field\FieldItemList;
use Drupal\Core\TypedData\ComputedItemListTrait;

/**
 * Class TextFieldItemList.
 *
 * @package Drupal\stamkort_permission\Plugin\Field
 */
class TextFieldItemList extends FieldItemList {

  use ComputedItemListTrait;

  /**
   * Computes the values for an item list.
   */
  protected function computeValue() {

    // By default this is empty.
  }

}
