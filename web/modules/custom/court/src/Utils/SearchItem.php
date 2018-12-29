<?php

namespace Drupal\court\Utils;

use Drupal\court\Entity\DecisionOptions;

/**
 * Class SearchItem.
 *
 * @package Drupal\court\Utils
 */
class SearchItem {

  protected $number;

  protected $type;

  protected $resolved;

  protected $validated;

  protected $jurisdiction;

  protected $caseNumber;

  protected $court;

  protected $judge;

  /**
   * Options.
   *
   * @var \Drupal\court\Entity\DecisionOptions
   */
  protected $options;

  /**
   * ReviewItem constructor.
   */
  public function __construct() {

    $this->options = DecisionOptions::instance();
  }

  /**
   * @return mixed
   */
  public function getNumber() {
    return $this->number;
  }

  /**
   * @param mixed $number
   *
   * @return SearchItem
   */
  public function setNumber($number) {
    $this->number = $number;
    return $this;
  }

  /**
   * @return mixed
   */
  public function getType() {
    return $this->type;
  }

  /**
   * @param mixed $type
   *
   * @return SearchItem
   */
  public function setType($type) {
    $this->type = $type;
    return $this;
  }

  public function getTypeId() {

    return $this->options->searchTypeId($this->type);
  }

  /**
   * @return mixed
   */
  public function getResolved() {
    return $this->resolved;
  }

  /**
   * @param mixed $resolved
   *
   * @return SearchItem
   */
  public function setResolved($resolved) {
    $this->resolved = $resolved;
    return $this;
  }

  /**
   * @return mixed
   */
  public function getValidated() {
    return $this->validated;
  }

  /**
   * @param mixed $validated
   *
   * @return SearchItem
   */
  public function setValidated($validated) {
    $this->validated = $validated;
    return $this;
  }

  /**
   * @return mixed
   */
  public function getJurisdiction() {
    return $this->jurisdiction;
  }

  /**
   * @param mixed $jurisdiction
   *
   * @return SearchItem
   */
  public function setJurisdiction($jurisdiction) {
    $this->jurisdiction = $jurisdiction;
    return $this;
  }

  /**
   * @return mixed
   */
  public function getJurisdictionId() {
    return $this->options->searchJurisdictionId($this->jurisdiction);
  }

  /**
   * @return mixed
   */
  public function getCaseNumber() {
    return $this->caseNumber;
  }

  /**
   * @param mixed $caseNumber
   *
   * @return SearchItem
   */
  public function setCaseNumber($caseNumber) {
    $this->caseNumber = $caseNumber;
    return $this;
  }

  /**
   * @return mixed
   */
  public function getCourt() {
    return $this->court;
  }

  /**
   * @param mixed $court
   *
   * @return SearchItem
   */
  public function setCourt($court) {
    $this->court = $court;
    return $this;
  }

  /**
   * @return mixed
   */
  public function getCourtId() {
    return $this->options->searchCourtId($this->court);
  }

  /**
   * @return mixed
   */
  public function getJudge() {
    return $this->judge;
  }

  /**
   * @param mixed $judge
   *
   * @return SearchItem
   */
  public function setJudge($judge) {
    $this->judge = $judge;
    return $this;
  }

}
