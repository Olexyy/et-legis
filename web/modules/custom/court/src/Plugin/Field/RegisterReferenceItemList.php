<?php

namespace Drupal\court\Plugin\Field;

use Drupal\Core\Field\FieldItemList;
use Drupal\Core\TypedData\ComputedItemListTrait;
use Drupal\court\Service\CourtApiService;

/**
 * Class TagNamesFieldItemList.
 *
 * @package Drupal\stamkort_permission\Plugin\Field
 */
class RegisterReferenceItemList extends FieldItemList {

  use ComputedItemListTrait;

  /**
   * Computes the values for an item list.
   */
  protected function computeValue() {
    /* @var \Drupal\court\Entity\DecisionInterface $entity*/
    $entity = $this->getEntity();
    $url = CourtApiService::service()->getReviewUrl($entity->getNumber());
    $this->list[] = $this->createItem(0, $url);
  }

}
