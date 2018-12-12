<?php

namespace Drupal\court\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Decision entities.
 *
 * @ingroup court
 */
interface DecisionInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Decision name.
   *
   * @return string
   *   Name of the Decision.
   */
  public function getNumber();

  /**
   * Sets the Decision name.
   *
   * @param string $name
   *   The Decision name.
   *
   * @return \Drupal\court\Entity\DecisionInterface
   *   The called Decision entity.
   */
  public function setNumber($name);

  /**
   * Gets the Decision creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Decision.
   */
  public function getCreatedTime();

  /**
   * Sets the Decision creation timestamp.
   *
   * @param int $timestamp
   *   The Decision creation timestamp.
   *
   * @return \Drupal\court\Entity\DecisionInterface
   *   The called Decision entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Decision published status indicator.
   *
   * Unpublished Decision are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Decision is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Decision.
   *
   * @param bool $published
   *   TRUE to set this Decision to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\court\Entity\DecisionInterface
   *   The called Decision entity.
   */
  public function setPublished($published);

}
