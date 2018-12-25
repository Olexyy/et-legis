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
  public function isActive();

  /**
   * Sets the published status of a Decision.
   *
   * @param bool $published
   *   TRUE to set this Decision to published, FALSE to set it to unpublished.
   *
   * @return \Drupal\court\Entity\DecisionInterface
   *   The called Decision entity.
   */
  public function setStatus($published);

  public function setText($text);

  public function getText();

  public function getCaseNumber();

  public function setCaseNumber($value);

  public function getType();

  public function setType($value);

  public function getJurisdiction();

  public function setJurisdiction($value);

  public function getCategory();

  public function setCategory($value);

  public function getCourt();

  public function setCourt($value);

  public function getInstance();

  public function setInstance($value);

  public function getJudge();

  public function setJudge($value);

  public function getResolved();

  public function setResolved($value);

  public function getValidated();

  public function setValidated($value);

  public function getRegistered();

  public function setRegistered($value);

  public function getPublished();

  public function setPublished($value);

  public function getResume();

  public function setResume($value);

  public function getLegalPosition();

  public function setLegalPosition($value);

}
