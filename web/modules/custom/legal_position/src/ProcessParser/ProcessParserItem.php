<?php

namespace Drupal\legal_position\ProcessParser;

/**
 * Class ProcessParserItemItem.
 *
 * @package Drupal\legal_position\ProcessParser
 */
class ProcessParserItem {

  /**
   * Number.
   *
   * @var int
   */
  protected $number = 0;

  /**
   * Types.
   *
   * @var string[]|array
   */
  protected $types = [];

  /**
   * Text.
   *
   * @var string
   */
  protected $text = '';

  /**
   * @var string[]|array
   */
  protected $info = [];

  /**
   * Links.
   *
   * @var string
   */
  protected $links = [];

  /**
   * Getter.
   *
   * @return int
   *   Value.
   */
  public function getNumber() {

    return $this->number;
  }

  /**
   * @param mixed $number
   *
   * @return ProcessParserItemItem
   */
  public function setNumber($number) {
    $this->number = $number;
    return $this;
  }

  /**
   * @return array|string[]
   */
  public function getTypes() {
    return $this->types;
  }

  /**
   * Setter.
   *
   * @param array|string[] $types
   *   Types.
   *
   * @return $this
   *   Chaining.
   */
  public function setTypes(array $types) {

    $this->types = $types;

    return $this;
  }

  /**
   * @param string $type
   *
   * @return $this
   */
  public function addType($type) {
    $this->types[] = $type;
    return $this;
  }

  /**
   * Getter.
   *
   * @return string
   *   Value.
   */
  public function getText() {
    return $this->text;
  }

  /**
   * @param string $text
   *
   * @return ProcessParserItemItem
   */
  public function setText($text) {
    $this->text = $text;
    return $this;
  }

  /**
   * @return mixed
   */
  public function getInfo() {
    return $this->info;
  }

  /**
   * @param mixed $info
   *
   * @return ProcessParserItemItem
   */
  public function setInfo($info) {
    $this->info = $info;
    return $this;
  }



  /**
   * @return mixed
   */
  public function getLinks() {

    return $this->links;
  }

  public function getLinkCount() {

    return count($this->links);
  }

  /**
   * @param array $links
   *
   * @return ProcessParserItemItem
   */
  public function setLinks(array $links) {

    $this->links = $links;

    return $this;
  }

  public function getLastLink() {

    if ($count = $this->getLinkCount()) {

      return $this->links[$count-1];
    }

    return NULL;
  }

  /**
   * @param mixed $links
   *
   * @return ProcessParserItemItem
   */
  public function addLink($link) {

    $this->links[] = $link;

    return $this;
  }

  /**
   * Predicate.
   *
   * @param string $type
   *   Type.
   *
   * @return bool
   *   Test result.
   */
  public function hasType($type) {

    foreach ($this->types as $typeName) {
      if ($typeName == $type) {

        return TRUE;
      }
    }

    return FALSE;
  }

}