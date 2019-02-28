<?php

namespace Drupal\court;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Legal position entity.
 *
 * @see \Drupal\court\Entity\LegalPosition.
 */
class LegalPositionAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\court\Entity\LegalPositionInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isPublished()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished legal position entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published legal position entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit legal position entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete legal position entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add legal position entities');
  }

}
