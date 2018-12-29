<?php

namespace Drupal\court\Data;

/**
 * Class RequestData.
 *
 * @package Drupal\court\Data
 */
class RequestData implements RequestDataInterface {

  // Пошук за контекстом
  protected $searchExpression = '';
  // Регіон суду
  protected $courtRegion = [];
  // Найменування суду
  protected $courtName = [];
  // Код суду
  protected $userCourtCode = '';
  // Інстанція
  protected $instType = [];
  // ПІБ судді
  protected $chairmenName = '';
  // Реєстраційний № рішення
  protected $regNumber = '';
  // Період ухвалення (постановлення)
  protected $regDateBegin = '';
  // Період ухвалення (постановлення)
  protected $regDateEnd = '';
  // Період надходження
  protected $importDateBegin = '';
  // Період надходження
  protected $importDateEnd = '';
  // Форма судового рішення
  protected $vrType = [];
  // Форма судочинства
  protected $csType = [];
  // Категорія справи1
  protected $caseCategory1 = [];
  // Категорія справи2
  protected $caseCategory2 = [];
  // Справа №
  protected $caseNumber = '';
  // Статуси сторін судового процесу
  protected $sideStatus = [];
  // Liga server
  protected $liga = FALSE;
  // Paging
  protected $paging = 25;
  // Sorting
  protected $sort = 0;

  protected static $apiKeys = [
    'searchExpression' => 'SearchExpression',
    'courtRegion' => 'CourtRegion',
    'courtName' => 'CourtName',
    'userCourtCode' => 'userCourtCode',
    'instType' => 'INSType',
    'chairmenName' => 'ChairmenName',
    'regNumber' => 'RegNumber',
    'regDateBegin' => 'RegDateBegin',
    'regDateEnd' => 'RegDateEnd',
    'importDateBegin' => 'ImportDateBegin',
    'importDateEnd' => 'ImportDateEnd',
    'vrType' => 'VRType',
    'csType' => 'CSType',
    'caseCategory1' => 'CaseCat1',
    'caseCategory2' => 'CaseCat2',
    'caseNumber' => 'CaseNumber',
    'sideStatus' => 'SideStatus',
    'liga' => 'Liga',
    'paging' => 'PagingInfo.ItemsPerPage',
    'sort' => 'Sort',
  ];

  /**
   * {@inheritdoc}
   */
  public static function create() {
    return new static();
  }

  /**
   * @return string
   */
  public function getCaseNumber() {
    return $this->caseNumber;
  }

  /**
   * @param string $caseNumber
   *
   * @return $this
   */
  public function setCaseNumber($caseNumber) {
    $this->caseNumber = $caseNumber;
    return $this;
  }

  /**
   * @return mixed
   */
  public function getChairmenName()
  {
    return $this->chairmenName;
  }

  /**
   * @param mixed $chairmenName
   */
  public function setChairmenName($chairmenName)
  {
    $this->chairmenName = $chairmenName;
    return $this;
  }

  /**
   * @return mixed
   */
  public function getImportDateBegin()
  {
    return $this->importDateBegin;
  }

  /**
   * @param mixed $importDateBegin
   */
  public function setImportDateBegin($importDateBegin)
  {
    $this->importDateBegin = $importDateBegin;
    return $this;
  }

  /**
   * @return mixed
   */
  public function getImportDateEnd()
  {
    return $this->importDateEnd;
  }

  /**
   * @param mixed $importDateEnd
   */
  public function setImportDateEnd($importDateEnd)
  {
    $this->importDateEnd = $importDateEnd;
    return $this;
  }

  /**
   * @return mixed
   */
  public function getLiga()
  {
    return $this->liga;
  }

  /**
   * @param mixed $liga
   */
  public function setLiga($liga)
  {
    $this->liga = $liga;
    return $this;
  }

  /**
   * @return mixed
   */
  public function getPaging()
  {
    return $this->paging;
  }

  /**
   * @param mixed $paging
   */
  public function setPaging($paging)
  {
    $this->paging = $paging;
    return $this;
  }

  /**
   * @return mixed
   */
  public function getRegDateBegin()
  {
    return $this->regDateBegin;
  }

  /**
   * @param mixed $regDateBegin
   */
  public function setRegDateBegin($regDateBegin)
  {
    $this->regDateBegin = $regDateBegin;
    return $this;
  }

  /**
   * @return mixed
   */
  public function getRegDateEnd()
  {
    return $this->regDateEnd;
  }

  /**
   * @param mixed $regDateEnd
   */
  public function setRegDateEnd($regDateEnd)
  {
    $this->regDateEnd = $regDateEnd;
    return $this;
  }

  /**
   * @return mixed
   */
  public function getRegNumber()
  {
    return $this->regNumber;
  }

  /**
   * @param mixed $regNumber
   */
  public function setRegNumber($regNumber)
  {
    $this->regNumber = $regNumber;
    return $this;
  }

  /**
   * @return mixed
   */
  public function getSearchExpression()
  {
    return $this->searchExpression;
  }

  /**
   * Setter for property.
   */
  public function setSearchExpression($searchExpression) {
    $this->searchExpression = $searchExpression;
    return $this;
  }

  /**
   * @return mixed
   */
  public function getSort()
  {
    return $this->sort;
  }

  /**
   * Setter for property.
   */
  public function setSort($sort) {
    $this->sort = $sort;
    return $this;
  }

  /**
   * @return mixed
   */
  public function getUserCourtCode() {
    return $this->userCourtCode;
  }

  /**
   * Setter for property.
   */
  public function setUserCourtCode($userCourtCode) {
    $this->userCourtCode = $userCourtCode;
    return $this;
  }

  /**
   * Getter for api keys.
   *
   * @return array
   */
  public static function getApiKeys() {
    return static::$apiKeys;
  }



  public function getMethod() {
    return 'POST';
  }


  public function getUrl() {
    return 'http://www.reyestr.court.gov.ua/';
  }

  public function set($key, $value) {
    if (property_exists($this, $key)) {
      if ($value) {
        $value = is_array($value) ? array_keys($value) : $value;
        $this->{$key} = $value;
      }
    }
    return $this;
  }


  public function toApiArray() {
    $params = [];
    foreach (static::getApiKeys() as $property => $apiKey) {
      $params[$apiKey] = $this->{$property};
    }
    return $params;
  }

  /**
   * @return array
   */
  public function getCourtRegion() {
    return $this->courtRegion;
  }

  /**
   * @param array $courtRegion
   *
   * @return $this
   */
  public function setCourtRegion(array $courtRegion) {
    $this->courtRegion = $courtRegion;
    return $this;
  }

  /**
   * @param int|string $courtRegion
   *
   * @return $this
   */
  public function addCourtRegion($courtRegion) {
    $this->courtRegion = $courtRegion;
    return $this;
  }

  /**
   * @return array
   */
  public function getCourtName() {
    return $this->courtName;
  }

  /**
   * @param array $courtName
   *
   * @return $this
   */
  public function setCourtName(array $courtName){
    $this->courtName = $courtName;
    return $this;
  }

  /**
   * @param int|string $courtName
   *
   * @return $this
   */
  public function addCourtName($courtName) {
    $this->courtName = $courtName;
    return $this;
  }

  /**
   * @return array
   */
  public function getInstType() {
    return $this->instType;
  }

  /**
   * @param array $instType
   *
   * @return $this
   */
  public function setInstType(array $instType) {
    $this->instType = $instType;
    return $this;
  }

  /**
   * @param string|int $instType
   *
   * @return $this
   */
  public function addInstType($instType) {
    $this->instType[] = $instType;
    return $this;
  }

  /**
   * @return array
   */
  public function getVrType() {
    return $this->vrType;
  }

  /**
   * @param array $vrType
   *
   * @return $this
   */
  public function setVrType(array $vrType) {
    $this->vrType = $vrType;
    return $this;
  }

  /**
   * @param string|int $vrType
   *
   * @return $this
   */
  public function addVrType($vrType) {
    $this->vrType[]= $vrType;
    return $this;
  }

  /**
   * @return array
   */
  public function getCsType() {
    return $this->csType;
  }

  /**
   * @param array $csType
   *
   * @return $this
   */
  public function setCsType(array $csType) {
    $this->csType = $csType;
    return $this;
  }

  /**
   * @param int|string $csType
   *
   * @return $this
   */
  public function addCsType($csType) {
    $this->csType = $csType;
    return $this;
  }

  /**
   * @return array
   */
  public function getCaseCategory1() {
    return $this->caseCategory1;
  }

  /**
   * @param array $caseCategory1
   *
   * @return $this
   */
  public function setCaseCategory1(array $caseCategory1) {
    $this->caseCategory1 = $caseCategory1;
    return $this;
  }

  /**
   * @param string|int $caseCategory1
   *
   * @return $this
   */
  public function addCaseCategory1($caseCategory1) {
    $this->caseCategory1[] = $caseCategory1;
    return $this;
  }

  /**
   * @return array
   */
  public function getCaseCategory2() {
    return $this->caseCategory2;
  }

  /**
   * @param array $caseCategory2
   *
   * @return $this
   */
  public function setCaseCategory2(array $caseCategory2) {
    $this->caseCategory2 = $caseCategory2;
    return $this;
  }

  /**
   * @param string|int $caseCategory2
   *
   * @return $this
   */
  public function addCaseCategory2($caseCategory2) {
    $this->caseCategory2[] = $caseCategory2;
    return $this;
  }

  /**
   * @return array
   */
  public function getSideStatus() {
    return $this->sideStatus;
  }

  /**
   * @param array $sideStatus
   *
   * @return $this
   */
  public function setSideStatus(array $sideStatus) {
    $this->sideStatus = $sideStatus;
    return $this;
  }

  /**
   * @param string|int $sideStatus
   *
   * @return $this
   */
  public function addSideStatus($sideStatus) {
    $this->sideStatus[] = $sideStatus;
    return $this;
  }

}