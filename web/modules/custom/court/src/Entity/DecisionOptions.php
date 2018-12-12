<?php

namespace Drupal\court\Entity;

/**
 * Class DecisionOptions.
 *
 * Options here aim to be search form friendly.
 *
 * @package Drupal\court\Entity
 */
class DecisionOptions {

  /**
   * Singleton.
   *
   * @var $this
   */
  protected static $instance;

  /**
   * Singleton.
   *
   * @return $this
   *   This object.
   */
  public static function instance() {

    if (!static::$instance) {

      static::$instance = new static();
    }
    return static::$instance;
  }

  /**
   * Decision forms options.
   *
   * @return array
   *   Array of values.
   */
  public function forms() {

    return [
      1 => 'Вирок',
      2 => 'Постанова',
      3 => 'Рішення',
      4 => 'Судовий наказ',
      5 => 'Ухвала',
      6 => 'Окрема ухвала',
      10 => 'Окрема думка',
    ];
  }

  /**
   * Decision forms options.
   *
   * @return array
   *   Array of values.
   */
  public function jurisdictions() {

    return [
      1 => 'Цивільне',
      2 => 'Кримінальне',
      3 => 'Господарське',
      4 => 'Адміністративне',
      5 => 'Справи про адміністративні правопорушення',
    ];
  }
}
