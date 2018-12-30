<?php

namespace Drupal\court;

use Drupal\Core\Entity\EntityAccessControlHandler;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Access\AccessResult;

/**
 * Access controller for the Decision entity.
 *
 * @see \Drupal\court\Entity\Decision.
 */
class DecisionAccessControlHandler extends EntityAccessControlHandler {

  /**
   * {@inheritdoc}
   */
  protected function checkAccess(EntityInterface $entity, $operation, AccountInterface $account) {
    /** @var \Drupal\court\Entity\DecisionInterface $entity */
    switch ($operation) {
      case 'view':
        if (!$entity->isActive()) {
          return AccessResult::allowedIfHasPermission($account, 'view unpublished decision entities');
        }
        return AccessResult::allowedIfHasPermission($account, 'view published decision entities');

      case 'update':
        return AccessResult::allowedIfHasPermission($account, 'edit decision entities');

      case 'delete':
        return AccessResult::allowedIfHasPermission($account, 'delete decision entities');
    }

    // Unknown operation, no opinion.
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  protected function checkCreateAccess(AccountInterface $account, array $context, $entity_bundle = NULL) {
    return AccessResult::allowedIfHasPermission($account, 'add decision entities');
  }

}
