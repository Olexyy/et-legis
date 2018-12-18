<?php

namespace Drupal\court\Entity;

use Drupal\court\Categorization\CaseCategory1;
use Drupal\court\Categorization\CourtName;

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
   * Decision types options.
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

  /**
   * Decision categories.
   *
   * @return array
   *   Array of values.
   */
  public function categories() {

    return CaseCategory1::getList();
  }

  /**
   * Decision categories.
   *
   * @return array
   *   Array of values.
   */
  public function courts() {

    $courts = [];
    foreach (CourtName::getList() as $courtRegion) {
      foreach ($courtRegion as $courtCode => $courtName) {
        $courts[$courtCode] = $courtName;
      }
    }

    return $courts;
  }

}
