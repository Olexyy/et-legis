<?php

namespace Drupal\court\Utils;

/**
 * Class SearchResponseData.
 *
 * @package Drupal\court\Utils
 */
class ResponseData implements ResponseDataInterface {

  /**
   * Empty status.
   *
   * @var bool
   */
  protected $empty;

  /**
   * Parser.
   *
   * @var \Drupal\court\Utils\Parser[]|array
   */
  protected $parsers = [];

  /**
   * ResponseData constructor.
   *
   * @param bool $empty
   *   Empty status.
   */
  public function __construct($empty) {
    $this->empty = $empty;
  }

  /**
   * {@inheritdoc}
   */
  public static function create() {

    return new static(FALSE);
  }

  /**
   * {@inheritdoc}
   */
  public static function createEmpty() {

    return new static(TRUE);
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {

    return $this->empty;
  }

  /**
   * Getter for Html parser.
   *
   * @return \Drupal\court\Utils\Parser|null
   *   Html parser.
   */
  public function getParser() {

    if ($this->parsers) {
      return $this->parsers[0];
    }

    return NULL;
  }

  /**
   * Getter for Html parsers.
   *
   * @return \Drupal\court\Utils\Parser[]|array
   *   Html parsers.
   */
  public function getParsers() {

    return $this->parsers;
  }

  /**
   * Parser setter.
   *
   * @param \Drupal\court\Utils\ParserInterface $parser
   *   Parser.
   *
   * @return $this
   *   Chaining.
   */
  public function addParser(ParserInterface $parser) {

    $this->parsers[] = $parser;

    return $this;
  }

}
