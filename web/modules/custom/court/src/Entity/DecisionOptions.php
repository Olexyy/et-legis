<?php

namespace Drupal\court\Entity;

use Drupal\court\Categorization\CaseCategory1;
use Drupal\court\Categorization\CaseCategory2;
use Drupal\court\Categorization\CaseCategory3;
use Drupal\court\Categorization\CourtName;
use Drupal\court\Categorization\CsType;
use Drupal\court\Categorization\InstType;
use Drupal\court\Categorization\VrType;

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
  public function types() {

    return VrType::getList();
  }

  /**
   * Search type.
   *
   * @param string $type
   *   Name.
   *
   * @return int|null
   *   Index if any.
   */
  public function searchTypeId($type) {

    if ($index = array_search($type, $this->types())) {

      return $index;
    }

    return NULL;
  }

  /**
   * Decision types options.
   *
   * @return array
   *   Array of values.
   */
  public function jurisdictions() {

    return CsType::getList();
  }

  /**
   * Search jurisdiction.
   *
   * @param string $jurisdiction
   *   Name.
   *
   * @return int|null
   *   Index if any.
   */
  public function searchJurisdictionId($jurisdiction) {

    if ($index = array_search($jurisdiction, $this->jurisdictions())) {

      return $index;
    }

    return NULL;
  }

  /**
   * Decision categories.
   *
   * @return array
   *   Array of values.
   */
  public function categories1() {

    return CaseCategory1::getList();
  }

  /**
   * Return all flat categories array.
   *
   * @return array
   *   Categories in flat order.
   */
  public function categories() {

    $categories = [];
    $categories1 = CaseCategory1::getList();
    $categories2 = CaseCategory2::getList();
    $categories3 = CaseCategory3::getList();
    foreach ($categories1 as $key => $category) {
      $categories[$key] = $category;
      if (array_key_exists($key, $categories2)) {
        foreach ($categories2[$key] as $subKey => $subCategory) {
          $categories[$subKey] = $subCategory;
          if (array_key_exists($key, $categories3) &&
            array_key_exists($subKey, $categories3[$key])) {
            foreach ($categories3[$key][$subKey] as $subSubKey => $subSubCategory) {
              $categories[$subSubKey] = $subSubCategory;
            }
          }
        }
      }
    }

    return $categories;
  }

  /**
   * Searches category index across all categories.
   *
   * @param string $category
   *   Category name.
   *
   * @return int|null
   *   Index or null.
   */
  public function searchCategoryId($category) {

    if ($index = array_search($category, $this->categories())) {

      return $index;
    }

    return NULL;
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

  /**
   * Search court.
   *
   * @param string $court
   *   Court name.
   *
   * @return int|null
   *   Id if any.
   */
  public function searchCourtId($court) {

    if ($index = array_search($court, $this->courts())) {

      return $index;
    }

    return NULL;
  }

  /**
   * Court instances.
   *
   * @return array
   *   Array of values.
   */
  public function instances() {

    return InstType::getList();
  }

  /**
   * Search instances.
   *
   * @param string $instance
   *   Instance name.
   *
   * @return array
   *   Array of values.
   */
  public function searchInstanceId($instance) {

    if ($index = array_search($instance, $this->instances())) {

      return $index;
    }

    return NULL;
  }

}
