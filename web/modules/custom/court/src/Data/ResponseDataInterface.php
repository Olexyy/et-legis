<?php

namespace Drupal\court\Data;

use Drupal\court\Parser\ParserInterface;

/**
 * Interface ResponseDataInterface.
 *
 * @package Drupal\court\Data
 */
interface ResponseDataInterface {

  /**
   * Factory method.
   *
   * @return $this
   *   This instance.
   */
  public static function create();

  /**
   * Factory method.
   *
   * @return $this
   *   This instance.
   */
  public static function createEmpty();

  /**
   * Parser setter.
   *
   * @param \Drupal\court\Parser\ParserInterface $parser
   *   Parser.
   *
   * @return $this
   *   Chaining.
   */
  public function addParser(ParserInterface $parser);

  /**
   * Getter for Html parser.
   *
   * @return \Drupal\court\Parser\Parser
   *   Html parser.
   */
  public function getParser();

  /**
   * Getter for Html parsers.
   *
   * @return \Drupal\court\Parser\Parser[]|array
   *   Html parsers.
   */
  public function getParsers();

  public function isEmpty();

}
