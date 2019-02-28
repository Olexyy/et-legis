<?php

namespace Drupal\court\Entity;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityChangedInterface;
use Drupal\user\EntityOwnerInterface;

/**
 * Provides an interface for defining Legal position entities.
 *
 * @ingroup court
 */
interface LegalPositionInterface extends ContentEntityInterface, EntityChangedInterface, EntityOwnerInterface {

  // Add get/set methods for your configuration properties here.

  /**
   * Gets the Legal position name.
   *
   * @return string
   *   Name of the Legal position.
   */
  public function getName();

  /**
   * Sets the Legal position name.
   *
   * @param string $name
   *   The Legal position name.
   *
   * @return \Drupal\court\Entity\LegalPositionInterface
   *   The called Legal position entity.
   */
  public function setName($name);

  /**
   * Gets the Legal position creation timestamp.
   *
   * @return int
   *   Creation timestamp of the Legal position.
   */
  public function getCreatedTime();

  /**
   * Sets the Legal position creation timestamp.
   *
   * @param int $timestamp
   *   The Legal position creation timestamp.
   *
   * @return \Drupal\court\Entity\LegalPositionInterface
   *   The called Legal position entity.
   */
  public function setCreatedTime($timestamp);

  /**
   * Returns the Legal position published status indicator.
   *
   * Unpublished Legal position are only visible to restricted users.
   *
   * @return bool
   *   TRUE if the Legal position is published.
   */
  public function isPublished();

  /**
   * Sets the published status of a Legal position.
   *
   * @param bool $published
   *   TRUE to set this Legal position to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\court\Entity\LegalPositionInterface
   *   The called Legal position entity.
   */
  public function setPublished($published);

}
