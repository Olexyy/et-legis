<?php

namespace Drupal\court\Plugin\Field;

use Drupal\Core\Field\FieldItemList;
use Drupal\Core\TypedData\ComputedItemListTrait;

/**
 * Class IncludeParentFieldItemList.
 *
 * @package Drupal\court\Plugin\Field
 */
class IncludeParentTermItemList extends FieldItemList {

  use ComputedItemListTrait;

  /**
   * Computes the values for an item list.
   */
  protected function computeValue() {
    /* @var \Drupal\court\Entity\LegalPosition $entity*/
    $entity = $this->getEntity();
    $defaultValue = $this->getFieldDefinition()->getDefaultValue($entity);
    $this->list[] = $this->createItem(0, $defaultValue[0]['value']);
  }

}
