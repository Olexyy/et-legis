<?php

namespace Drupal\court\Utils;

/**
 * Interface ResponseDataInterface.
 *
 * @package Drupal\court\Utils
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
   * @param \Drupal\court\Utils\ParserInterface $parser
   *   Parser.
   *
   * @return $this
   *   Chaining.
   */
  public function addParser(ParserInterface $parser);

  /**
   * Getter for Html parser.
   *
   * @return \Drupal\court\Utils\Parser
   *   Html parser.
   */
  public function getParser();

  /**
   * Getter for Html parsers.
   *
   * @return \Drupal\court\Utils\Parser[]|array
   *   Html parsers.
   */
  public function getParsers();

  public function isEmpty();

}
